<?php

namespace Lox\Interpreter;

use App\Attributes\Singleton;
use App\Services\ErrorReporter;
use Lox\AST\Expressions\Assign;
use Lox\AST\Expressions\Binary;
use Lox\AST\Expressions\Call;
use Lox\AST\Expressions\Expression;
use Lox\AST\Expressions\FunctionExpression;
use Lox\AST\Expressions\Grouping;
use Lox\AST\Expressions\Literal;
use Lox\AST\Expressions\Logical;
use Lox\AST\Expressions\Ternary;
use Lox\AST\Expressions\Unary;
use Lox\AST\Expressions\Variable;
use Lox\AST\ExpressionVisitor;
use Lox\AST\Statements\BlockStatement;
use Lox\AST\Statements\CompletionStatement;
use Lox\AST\Statements\ExpressionStatement;
use Lox\AST\Statements\IfStatement;
use Lox\AST\Statements\ReturnStatement;
use Lox\AST\Statements\Statement;
use Lox\AST\Statements\VarStatement;
use Lox\AST\Statements\WhileStatement;
use Lox\AST\StatementVisitor;
use Lox\Runtime\Environment;
use Lox\Runtime\Errors\ArgumentCountError;
use Lox\Runtime\Errors\RuntimeError;
use Lox\Runtime\LoxType;
use Lox\Runtime\Values\CallableValue;
use Lox\Runtime\Values\FunctionValue;
use Lox\Runtime\Values\NilValue;
use Lox\Runtime\Values\NumberValue;
use Lox\Runtime\Values\Value;
use Lox\Scaner\Token;
use Lox\Scaner\TokenType;
use WeakMap;

#[Singleton]
class Interpreter implements ExpressionVisitor, StatementVisitor
{
    public Environment $environment;

    public function __construct(
        private readonly ErrorReporter $errorReporter,
        public readonly Environment    $global,
        private WeakMap                $locals
    )
    {
        $this->environment = $this->global;
    }

    /**
     * @param array<Statement> $statements
     * @return void
     */
    public function interpret(array $statements): Value|null
    {
        if (empty($statements)) return null;

        try {
            $last = array_pop($statements);

            foreach ($statements as $statement) {
                $this->execute($statement);
            }

            if ($last instanceof ExpressionStatement) {
                return $this->evaluate($last->expression);
            } else {
                $this->execute($last);
                return null;
            }

        } catch (RuntimeError $exception) {
            $this->errorReporter->runtimeError($exception);
        }
        return null;
    }

    private function execute(Statement $statement): void
    {
        $statement->accept($this);
    }

    /**
     * @param array<Statement> $statements
     * @param Environment $environment
     * @return void
     */
    public function executeBlock(array $statements, Environment $environment): void
    {
        $previous = $this->environment;
        try {
            $this->environment = $environment;
            foreach ($statements as $statement) {
                $this->execute($statement);
            }
        } finally {
            $this->environment = $previous;
        }
    }

    private function evaluate(Expression $expression): Value
    {
        return $expression->accept($this);
    }

    #[\Override] public function visitExpressionStmt(ExpressionStatement $statement)
    {
        $this->evaluate($statement->expression);
    }

    #[\Override] public function visitIfStmt(IfStatement $if)
    {
        if ($this->isTruthy($this->evaluate($if->condition), $if->condition)) {
            $this->execute($if->thenBranch);
        } else if ($if->elseBranch !== null) {
            $this->execute($if->elseBranch);
        }
    }

    #[\Override] public function visitReturnStmt(ReturnStatement $statement)
    {
        $value = $this->evaluate($statement->value);
        throw new ReturnSignal($statement, $value);
    }

    #[\Override] public function visitWhileStmt(WhileStatement $statement)
    {
        while ($this->isTruthy($this->evaluate($statement->condition), $statement->condition)) {
            try {
                $this->execute($statement->body);
            } catch (CompletionSignal $signal) {
                if ($signal->statement->operator->type == TokenType::BREAK) {
                    break;
                } else if ($signal->statement->operator->type == TokenType::CONTINUE) {
                    continue;
                }
            }
        }
    }

    #[\Override] public function visitCompletionStmt(CompletionStatement $completion)
    {
        throw new CompletionSignal($completion);
    }

    #[\Override] public function visitVarStmt(VarStatement $var)
    {
        if ($var->initializer != null) {
            $value = $this->evaluate($var->initializer);
        } else {
            $value = dependency(NilValue::class);
        }
        $this->environment->define($var->name, $value);
    }

    #[\Override] public function visitBlockStmt(BlockStatement $block)
    {
        $this->executeBlock($block->statements, new Environment($this->environment));
    }

    #[\Override] public function visitFunctionExpr(FunctionExpression $expression)
    {
        $function = new FunctionValue($expression, $this->environment);
        if ($expression->name != null) {
            $this->environment->define($expression->name, $function);
        }
        return $function;
    }

    #[\Override] public function visitAssignExpr(Assign $expression)
    {
        $value = $this->evaluate($expression->value);

        if ($this->hasLocale($expression)) {
            $distance = $this->locals[$expression];
            $this->environment->assignAt($distance, $expression->name, $value);
        } else {
            $this->global->assign($expression->name, $value);
        }

        return $value;
    }

    #[\Override] public function visitTernaryExpr(Ternary $expression)
    {
        return $this->isTruthy($this->evaluate($expression->condition), $expression->condition)
            ? $this->evaluate($expression->then)
            : $this->evaluate($expression->else);
    }

    #[\Override] public function visitBinaryExpr(Binary $expression)
    {

        $left  = $this->evaluate($expression->left);
        $right = $this->evaluate($expression->right);

        switch ($expression->operator->type) {
            case TokenType::BANG_EQUAL:
            case TokenType::EQUAL_EQUAL:
            case TokenType::GREATER:
            case TokenType::GREATER_EQUAL:
            case TokenType::LESS:
            case TokenType::LESS_EQUAL:
                return $left->compare($right, $expression->operator, $expression);
            case TokenType::PLUS:
            case TokenType::MINUS:
            case TokenType::SLASH:
            case TokenType::STAR:
                if (in_array(LoxType::String, [$left->getType(), $right->getType()])) {
                    $left = $left->cast(LoxType::String, $expression);
                }
                return $left->calc($right, $expression->operator, $expression);
            case TokenType::COMMA:
                return $right;
        }

        throw new RuntimeError($expression->operator, "Undefined binary operator.");
    }

    #[\Override] public function visitCallExpr(Call $call)
    {
        $callee = $this->evaluate($call->callee);

        $arguments = array_map(function (Expression $argument) {
            return $this->evaluate($argument);
        }, $call->arguments);

        /** @var CallableValue $function */
        $function = $callee->cast(LoxType::Callable, $call->callee);

        if (count($arguments) < $function->arity()) {
            throw new ArgumentCountError($call->rightParen, "Expect {$function->arity()} arguments but got ".count($arguments).".");
        }

        return $function->call($arguments, $call);
    }

    #[\Override] public function visitGroupingExpr(Grouping $expression)
    {
        return $this->evaluate($expression->expression);
    }

    #[\Override] public function visitLiteralExpr(Literal $expression)
    {
        return $expression->value;
    }

    #[\Override] public function visitLogicalExpr(Logical $expression)
    {
        $left         = $this->evaluate($expression->left);
        $leftIsTruthy = $this->isTruthy($left, $expression->left);

        if ($expression->operator->type == TokenType::OR) {
            if ($leftIsTruthy) return $left;
        } else {
            if (!$leftIsTruthy) return $left;
        }

        return $this->evaluate($expression->right);
    }

    #[\Override] public function visitUnaryExpr(Unary $expression)
    {
        $right = $this->evaluate($expression->right);

        switch ($expression->operator->type) {
            case TokenType::BANG:
                return !$this->isTruthy($right, $expression->right);
            case TokenType::MINUS:
                $this->assertNumber($expression, $right);
                return new NumberValue($right->value * -1);
        }

        return dependency(NilValue::class);
    }

    #[\Override] public function visitVariableExpr(Variable $expression): Value
    {
        return $this->lookUpVariable($expression->name, $expression);
    }

    private function assertNumber(Expression $expression, ...$values)
    {
        foreach ($values as $value) {
            if ($value instanceof NumberValue) continue;

            $operator = property_exists($expression, 'operator') ? $expression->operator : null;
            throw new RuntimeError($operator, "Operand must be number.");
        }
    }

    private function isTruthy(Value $value, Expression $cause)
    {
        return $value->cast(LoxType::Boolean, $cause)->value;
    }

    public function resolve(Expression|null $expression, int $depth)
    {
        $this->locals[$expression] = $depth;
    }

    public function lookUpVariable(Token $name, Expression $expression)
    {
        if ($this->hasLocale($expression)) {
            $distance = $this->locals[$expression];
            return $this->environment->getAt($distance, $name);
        } else {
            return $this->global->get($name);
        }
    }

    public function hasLocale(Expression $expression)
    {

        if (!isset($this->locals[$expression])) {
            return false;
        }
        return $this->locals[$expression] != null;
    }
}
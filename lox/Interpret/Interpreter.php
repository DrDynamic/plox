<?php

namespace Lox\Interpret;

use App\Attributes\Instance;
use App\Services\ErrorReporter;
use Lox\AST\Expressions\Assign;
use Lox\AST\Expressions\Binary;
use Lox\AST\Expressions\Expression;
use Lox\AST\Expressions\Grouping;
use Lox\AST\Expressions\Literal;
use Lox\AST\Expressions\Logical;
use Lox\AST\Expressions\Ternary;
use Lox\AST\Expressions\Unary;
use Lox\AST\Expressions\Variable;
use Lox\AST\ExpressionVisitor;
use Lox\AST\Statements\BlockStatement;
use Lox\AST\Statements\CompletionStatement;
use Lox\AST\Statements\ExpressionStmt;
use Lox\AST\Statements\IfStatement;
use Lox\AST\Statements\PrintStatement;
use Lox\AST\Statements\Statement;
use Lox\AST\Statements\VarStatement;
use Lox\AST\Statements\WhileStatement;
use Lox\AST\StatementVisitor;
use Lox\Runtime\Environment;
use Lox\Runtime\Errors\RuntimeError;
use Lox\Runtime\Values\NilValue;
use Lox\Runtime\Values\NumberValue;
use Lox\Runtime\Values\Value;
use Lox\Runtime\Values\ValueType;
use Lox\Scan\TokenType;

#[Instance]
class Interpreter implements ExpressionVisitor, StatementVisitor
{
    public function __construct(
        private readonly ErrorReporter $errorReporter,
        private Environment            $environment
    )
    {
    }

    public function resetEnvironment()
    {
        $this->environment = new Environment();
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

            if ($last instanceof ExpressionStmt) {
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
    private function executeBlock(array $statements, Environment $environment): void
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

    #[\Override] public function visitExpressionStmt(ExpressionStmt $statement)
    {
        $this->evaluate($statement->expression);
    }

    #[\Override] public function visitIfStmt(IfStatement $if)
    {
        if ($this->isTruthy($this->evaluate($if->condition))) {
            $this->execute($if->thenBranch);
        } else if ($if->elseBranch !== null) {
            $this->execute($if->elseBranch);
        }
    }

    #[\Override] public function visitPrintStmt(PrintStatement $print)
    {
        $result = $this->evaluate($print->expression);
        echo $result->cast(ValueType::String)->value."\n";
    }

    #[\Override] public function visitWhileStmt(WhileStatement $while)
    {
        while ($this->isTruthy($this->evaluate($while->condition))) {
            try {
                $this->execute($while->body);
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
            $value = new NilValue();
        }
        $this->environment->define($var->name, $value);
    }

    #[\Override] public function visitBlockStmt(BlockStatement $block)
    {
        $this->executeBlock($block->statements, new Environment($this->environment));
    }

    #[\Override] public function visitAssignExpr(Assign $assign)
    {
        $value = $this->evaluate($assign->value);
        $this->environment->assign($assign->name, $value);
        return $value;
    }

    #[\Override] public function visitTernaryExpr(Ternary $ternary)
    {
        return $this->isTruthy($this->evaluate($ternary->condition))
            ? $this->evaluate($ternary->then)
            : $this->evaluate($ternary->else);
    }

    #[\Override] public function visitBinaryExpr(Binary $binary)
    {

        $left  = $this->evaluate($binary->left);
        $right = $this->evaluate($binary->right);

        switch ($binary->operator->type) {
            case TokenType::BANG_EQUAL:
            case TokenType::EQUAL_EQUAL:
            case TokenType::GREATER:
            case TokenType::GREATER_EQUAL:
            case TokenType::LESS:
            case TokenType::LESS_EQUAL:
                return $left->compare($right, $binary->operator);
            case TokenType::PLUS:
            case TokenType::MINUS:
            case TokenType::SLASH:
            case TokenType::STAR:
                return $left->calc($right, $binary->operator);
            case TokenType::COMMA:
                return $right;
        }

        throw new RuntimeError($binary->operator, "Undefined binary operator.");
    }

    #[\Override] public function visitGroupingExpr(Grouping $grouping)
    {
        return $this->evaluate($grouping->expression);
    }

    #[\Override] public function visitLiteralExpr(Literal $literal)
    {
        return $literal->value;
    }

    #[\Override] public function visitLogicalExpr(Logical $logical)
    {
        $left         = $this->evaluate($logical->left);
        $leftIsTruthy = $this->isTruthy($left);

        if ($logical->operator->type == TokenType::OR) {
            if ($leftIsTruthy) return $left;
        } else {
            if (!$leftIsTruthy) return $left;
        }

        return $this->evaluate($logical->right);
    }

    #[\Override] public function visitUnaryExpr(Unary $unary)
    {
        $right = $this->evaluate($unary->right);

        switch ($unary->operator->type) {
            case TokenType::BANG:
                return !$this->isTruthy($right);
            case TokenType::MINUS:
                $this->assertNumber($unary, $right);
                return new NumberValue($right->value * -1);
        }

        return new NilValue();
    }

    #[\Override] public function visitVariableExpr(Variable $variable): Value
    {
        $value = $this->environment->get($variable->name);
        return $value;
    }

    private function assertNumber(Expression $expression, ...$values)
    {
        foreach ($values as $value) {
            if ($value instanceof NumberValue) continue;

            $operator = property_exists($expression, 'operator') ? $expression->operator : null;
            throw new RuntimeError($operator, "Operand must be number.");
        }
    }

    private function isTruthy(Value $value)
    {
        return $value->cast(ValueType::Boolean)->value;
    }
}
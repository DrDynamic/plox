<?php

namespace Lox\Interpret;

use App\Attributes\Instance;
use App\Services\ErrorReporter;
use Lox\AST\Expressions\Binary;
use Lox\AST\Expressions\Expression;
use Lox\AST\Expressions\Grouping;
use Lox\AST\Expressions\Literal;
use Lox\AST\Expressions\Ternary;
use Lox\AST\Expressions\Unary;
use Lox\AST\Expressions\Variable;
use Lox\AST\ExpressionVisitor;
use Lox\AST\Statements\ExpressionStmt;
use Lox\AST\Statements\PrintStmt;
use Lox\AST\Statements\Statement;
use Lox\AST\Statements\VarStatement;
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
        private readonly Environment   $environment
    )
    {
    }


    public function interpret(array $statements)
    {
        try {
            foreach ($statements as $statement) {
                $this->execute($statement);
            }
//            return $expression->accept($this);
        } catch (RuntimeError $exception) {
            $this->errorReporter->runtimeError($exception);
        }
    }

    private function execute(Statement $statement)
    {
        $statement->accept($this);
    }

    private function evaluate(Expression $expression): Value
    {
        return $expression->accept($this);
    }

    #[\Override] public function visitExpressionStmt(ExpressionStmt $statement)
    {
        $this->evaluate($statement->expression);
    }

    #[\Override] public function visitPrintStmt(PrintStmt $statement)
    {
        $result = $this->evaluate($statement->expression);
        echo $result->cast(ValueType::String)->value."\n";
    }

    #[\Override] public function visitVarStmt(VarStatement $statement)
    {
        if ($statement->initializer != null) {
            $value = $this->evaluate($statement->initializer);
        } else {
            $value = new NilValue();
        }
        $this->environment->define($statement->name, $value);
    }

    #[\Override] public function visitTernary(Ternary $ternary)
    {
        return $this->evaluate($ternary->condition)->cast(ValueType::Boolean)->value
            ? $this->evaluate($ternary->then)
            : $this->evaluate($ternary->else);
    }

    #[\Override] public function visitBinary(Binary $binary)
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

        return null;
    }

    #[\Override] public function visitGrouping(Grouping $grouping)
    {
        return $this->evaluate($grouping->expression);
    }

    #[\Override] public function visitLiteral(Literal $literal)
    {
        return $literal->value;
    }

    #[\Override] public function visitUnary(Unary $unary)
    {
        $right = $this->evaluate($unary->right);

        switch ($unary->operator->type) {
            case TokenType::BANG:
                return !$right->cast(ValueType::Boolean)->value;
            case TokenType::MINUS:
                $this->assertNumber($unary, $right);
                return new NumberValue($right->value * -1);
        }

        return new NilValue();
    }

    #[\Override] public function visitVariable(Variable $variable): Value
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
}
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
use Lox\AST\ExpressionVisitor;
use Lox\AST\Statements\ExpressionStmt;
use Lox\AST\Statements\PrintStmt;
use Lox\AST\Statements\Statement;
use Lox\AST\StatementVisitor;
use Lox\Runtime\Errors\RuntimeError;
use Lox\Runtime\Types\LoxType;
use Lox\Runtime\Types\NumberType;
use Lox\Scan\TokenType;

#[Instance]
class Interpreter implements ExpressionVisitor, StatementVisitor
{
    public function __construct(
        private readonly ErrorReporter $errorReporter,
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

    private function evaluate(Expression $expression)
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
        echo $result->cast(LoxType::String)->value."\n";
    }


    #[\Override] public function visitTernary(Ternary $ternary)
    {
        return $this->evaluate($ternary->condition)->cast(LoxType::Boolean)->value
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
                return !$right->cast(LoxType::Boolean)->value;
            case TokenType::MINUS:
                $this->assertNumber($unary, $right);
                return new NumberType($right->value * -1);
        }

        return null;
    }



    private function assertNumber(Expression $expression, ...$values)
    {
        foreach ($values as $value) {
            if ($value instanceof NumberType) continue;

            $operator = property_exists($expression, 'operator') ? $expression->operator : null;
            throw new RuntimeError($operator, "Operand must be number.");
        }
    }
}
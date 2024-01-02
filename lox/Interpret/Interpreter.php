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
use Lox\Scan\Token;
use Lox\Scan\TokenType;

#[Instance]
class Interpreter implements ExpressionVisitor
{
    public function __construct(
        private readonly ErrorReporter $errorReporter,
    )
    {
    }


    public function interpret(Expression $expression)
    {
        try {
            return $expression->accept($this);
        } catch (RuntimeError $exception) {
            $this->errorReporter->runtimeError($exception);
        }
    }


    #[\Override] public function visitTernary(Ternary $ternary)
    {
        return $this->isTruthy($this->evaluate($ternary->condition))
            ? $this->evaluate($ternary->then)
            : $this->evaluate($ternary->else);
    }

    #[\Override] public function visitBinary(Binary $binary)
    {
        $left  = $this->evaluate($binary->left);
        $right = $this->evaluate($binary->right);

        switch ($binary->operator->type) {
            case TokenType::BANG_EQUAL:
                return !$this->isEqual($left, $right);
            case TokenType::EQUAL_EQUAL:
                return $this->isEqual($left, $right);
            case TokenType::GREATER:
                $this->castForCompare($left,$right, $binary->operator);
                $this->assertNumber($binary, $left, $right);
                return floatval($left) > floatval($right);
            case TokenType::GREATER_EQUAL:
                $this->castForCompare($left,$right, $binary->operator);
                $this->assertNumber($binary, $left, $right);
                return floatval($left) >= floatval($right);
            case TokenType::LESS:
                $this->castForCompare($left,$right, $binary->operator);
                $this->assertNumber($binary, $left, $right);
                return floatval($left) < floatval($right);
            case TokenType::LESS_EQUAL:
                $this->castForCompare($left,$right, $binary->operator);
                $this->assertNumber($binary, $left, $right);
                return floatval($left) <= floatval($right);
            case TokenType::PLUS:
                if ($this->isNumber($left, $right)) {
                    return $left + $right;
                } else if (is_string($left) && is_string($right)) {
                    return $left.$right;
                }
                throw new RuntimeError($binary->operator, "Operands must be two numbers or two strings.");
            case TokenType::MINUS:
                return floatval($left) - floatval($right);
            case TokenType::SLASH:
                return floatval($left) / floatval($right);
            case TokenType::STAR:
                return floatval($left) * floatval($right);
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

    #[\Override] public function visitorUnary(Unary $unary)
    {
        $right = $this->evaluate($unary->right);

        switch ($unary->operator->type) {
            case TokenType::BANG:
                return !$this->isTruthy($right);
            case TokenType::PLUS:
                $this->assertNumber($unary, $right);
                return +floatval($right);
            case TokenType::MINUS:
                $this->assertNumber($unary, $right);
                return -doubleval($right);
        }

        return null;
    }

    private function evaluate(Expression $expression)
    {
        return $expression->accept($this);
    }

    public function stringify($value)
    {
        if (is_null($value)) return 'nil';

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_string($value)) {
            return "\"$value\"";
        }
        return strval($value);
    }

    private function isTruthy($value)
    {
        if (is_null($value)) return false;
        if (is_bool($value)) return $value;
        if (is_int($value)) return $value !== 0;
        if (is_double($value)) return $value !== 0;
        if (is_string($value)) return $value !== "";
        if (is_bool($value)) return $value;

        return false;
    }

    private function isEqual($a, $b)
    {
        if (is_null($a) && is_null($b)) return true;
        if (is_null($a)) return false;

        return $a === $b;
    }

    private function isNumber(...$values)
    {
        foreach ($values as $value) {
            if (!is_int($value)
                && !is_float($value)) {
                return false;
            }
        }
        return true;
    }

    private function castForCompare(&$left, &$right, Token $operatorToken)
    {
        if (in_array($operatorToken->type, [
            TokenType::GREATER,
            TokenType::GREATER_EQUAL,
            TokenType::LESS,
            TokenType::LESS_EQUAL])) {
            if (!$this->isNumber($left) || !$this->isNumber($right)) {
                if ($this->isNumber($left) && is_string($right)) {
                    $right = mb_strlen($right);
                } else {
                    $left = mb_strlen($left);
                }
            }
        }
    }


    private function assertNumber(Expression $expression, ...$values)
    {
        foreach ($values as $value) {
            if ($this->isNumber($value)) return;

            $operator = property_exists($expression, 'operator') ? $expression->operator : null;
            throw new RuntimeError($operator, "Operand must be number.");
        }
    }
}
<?php

namespace Lox\AST;

use App\Attributes\Singleton;
use Lox\AST\Expressions\Binary;
use Lox\AST\Expressions\Expression;
use Lox\AST\Expressions\Grouping;
use Lox\AST\Expressions\Literal;
use Lox\AST\Expressions\Unary;

#[Singleton]
class AstPrinter implements ExpressionVisitor
{
    public function print(Expression $expression)
    {
        return $expression->accept($this);
    }

    #[\Override] public function visitBinaryExpr(Binary $binary)
    {
        return $this->parenthesize($binary->operator->lexeme, $binary->left, $binary->right);
    }

    #[\Override] public function visitGroupingExpr(Grouping $grouping)
    {
        return $this->parenthesize("group", $grouping->expression);
    }

    #[\Override] public function visitLiteralExpr(Literal $literal)
    {
        if ($literal->value == null) return "nil";
        return strval($literal->value);
    }

    #[\Override] public function visitUnaryExpr(Unary $unary)
    {
        return $this->parenthesize($unary->operator->lexeme, $unary->right);
    }

    protected function parenthesize(string $name, Expression ...$expressions)
    {
        $str = "($name";
        foreach ($expressions as $expression) {
            $str .= " ".$expression->accept($this);
        }
        return "$str)";
    }
}
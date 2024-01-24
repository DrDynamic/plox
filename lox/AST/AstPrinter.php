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

    #[\Override] public function visitBinaryExpr(Binary $expression)
    {
        return $this->parenthesize($expression->operator->lexeme, $expression->left, $expression->right);
    }

    #[\Override] public function visitGroupingExpr(Grouping $expression)
    {
        return $this->parenthesize("group", $expression->expression);
    }

    #[\Override] public function visitLiteralExpr(Literal $expression)
    {
        if ($expression->value == null) return "nil";
        return strval($expression->value);
    }

    #[\Override] public function visitUnaryExpr(Unary $expression)
    {
        return $this->parenthesize($expression->operator->lexeme, $expression->right);
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
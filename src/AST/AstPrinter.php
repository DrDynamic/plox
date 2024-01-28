<?php

namespace src\AST;

use src\AST\Expressions\Binary;
use src\AST\Expressions\Expression;
use src\AST\Expressions\Grouping;
use src\AST\Expressions\Literal;
use src\AST\Expressions\Unary;
use src\Services\Dependency\Attributes\Singleton;

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
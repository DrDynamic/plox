<?php

namespace Lox\AST;

use Lox\AST\Expressions\Assign;
use Lox\AST\Expressions\Binary;
use Lox\AST\Expressions\Call;
use Lox\AST\Expressions\FunctionExpression;
use Lox\AST\Expressions\Grouping;
use Lox\AST\Expressions\Literal;
use Lox\AST\Expressions\Logical;
use Lox\AST\Expressions\Ternary;
use Lox\AST\Expressions\Unary;
use Lox\AST\Expressions\Variable;

interface ExpressionVisitor
{
    public function visitTernaryExpr(Ternary $expression);

    public function visitBinaryExpr(Binary $expression);

    public function visitGroupingExpr(Grouping $expression);

    public function visitLiteralExpr(Literal $expression);

    public function visitUnaryExpr(Unary $expression);

    public function visitVariableExpr(Variable $expression);

    public function visitAssignExpr(Assign $expression);

    public function visitLogicalExpr(Logical $expression);

    public function visitCallExpr(Call $call);

    public function visitFunctionExpr(FunctionExpression $expression);

}
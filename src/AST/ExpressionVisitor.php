<?php

namespace src\AST;

use src\AST\Expressions\Assign;
use src\AST\Expressions\Binary;
use src\AST\Expressions\Call;
use src\AST\Expressions\FunctionExpression;
use src\AST\Expressions\Grouping;
use src\AST\Expressions\Literal;
use src\AST\Expressions\Logical;
use src\AST\Expressions\Ternary;
use src\AST\Expressions\Unary;
use src\AST\Expressions\Variable;

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
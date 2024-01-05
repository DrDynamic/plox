<?php

namespace Lox\AST;

use Lox\AST\Expressions\Assign;
use Lox\AST\Expressions\Binary;
use Lox\AST\Expressions\Call;
use Lox\AST\Expressions\Grouping;
use Lox\AST\Expressions\Literal;
use Lox\AST\Expressions\Logical;
use Lox\AST\Expressions\Ternary;
use Lox\AST\Expressions\Unary;
use Lox\AST\Expressions\Variable;

interface ExpressionVisitor
{
    public function visitTernaryExpr(Ternary $ternary);

    public function visitBinaryExpr(Binary $binary);

    public function visitGroupingExpr(Grouping $grouping);

    public function visitLiteralExpr(Literal $literal);

    public function visitUnaryExpr(Unary $unary);

    public function visitVariableExpr(Variable $variable);

    public function visitAssignExpr(Assign $assign);

    public function visitLogicalExpr(Logical $logical);

    public function visitCallExpr(Call $call);

}
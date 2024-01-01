<?php

namespace Lox\AST;

use Lox\AST\Expressions\Binary;
use Lox\AST\Expressions\Grouping;
use Lox\AST\Expressions\Literal;
use Lox\AST\Expressions\Ternary;
use Lox\AST\Expressions\Unary;

interface ExpressionVisitor
{
    public function visitTernary(Ternary $ternary);

    public function visitBinary(Binary $binary);

    public function visitGrouping(Grouping $grouping);

    public function visitLiteral(Literal $literal);

    public function visitorUnary(Unary $unary);

}
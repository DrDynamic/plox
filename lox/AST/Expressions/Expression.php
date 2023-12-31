<?php

namespace Lox\AST\Expressions;

use Lox\AST\ExpressionVisitor;

abstract class Expression implements \JsonSerializable
{
    abstract function accept(ExpressionVisitor $visitor);
}
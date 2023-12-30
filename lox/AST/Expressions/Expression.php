<?php

namespace Lox\AST\Expressions;

use Lox\AST\ExpressionVisitor;

abstract class Expression
{
    abstract function accept(ExpressionVisitor $visitor);
}
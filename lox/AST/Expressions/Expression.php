<?php

namespace Lox\AST\Expressions;

use Lox\AST\ExpressionVisitor;
use Lox\Scaner\Token;

abstract class Expression implements \JsonSerializable
{

    public function __construct(
        public readonly Token $tokenStart,
        public readonly Token $tokenEnd
    )
    {
    }

    abstract function accept(ExpressionVisitor $visitor);
}
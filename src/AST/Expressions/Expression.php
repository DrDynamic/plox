<?php

namespace src\AST\Expressions;

use src\AST\ExpressionVisitor;
use src\Scaner\Token;

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
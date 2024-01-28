<?php

namespace src\AST\Expressions;

use src\AST\AstNode;
use src\AST\ExpressionVisitor;
use src\Scaner\Token;

abstract class Expression extends AstNode implements \JsonSerializable
{

    public function __construct(
        public readonly Token $tokenStart,
        public readonly Token $tokenEnd
    )
    {
    }

    abstract function accept(ExpressionVisitor $visitor);
}
<?php

namespace Lox;

class Token
{
    public function __construct(
        public readonly TokenType $tokenType,
        public readonly string    $lexeme,
        public readonly mixed     $literal,
        public readonly int       $line
    )
    {
    }

    public function __toString(): string
    {
        return "[{$this->tokenType->name}: $this->lexeme $this->literal]";
    }


}
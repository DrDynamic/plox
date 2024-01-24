<?php

namespace Lox\Scaner;

class Token
{
    // TODO: rename tokenType property into type
    public function __construct(
        public readonly TokenType $type,
        public readonly string    $lexeme,
        public readonly mixed     $literal,
        public readonly int       $line
    )
    {
    }

    public function __toString(): string
    {
        return "[{$this->type->name}: $this->lexeme | $this->literal]";
    }


}
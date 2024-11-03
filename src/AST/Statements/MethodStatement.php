<?php

namespace src\AST\Statements;

use src\AST\StatementVisitor;
use src\Resolver\LoxClassPropertyVisibility;
use src\Scaner\Token;
use src\Services\Arr;

class MethodStatement extends Statement
{
    public function __construct(
        Token                                      $tokenStart,
        public readonly LoxClassPropertyVisibility $visibility,
        public readonly bool                       $isStatic,
        public readonly ?Token                     $name,
        public readonly array                      $parameters,
        public readonly array                      $body)
    {
        parent::__construct($tokenStart, Arr::last($this->body)->tokenEnd);
    }

    #[\Override] function accept(StatementVisitor $visitor)
    {
        return $visitor->visitMethodStmt($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return [
            'visibility' => $this->visibility,
            'name'       => $this->name,
            'parameters' => $this->parameters,
            'body'       => $this->body,
        ];
    }
}
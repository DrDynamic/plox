<?php

namespace Lox\AST\Statements;

use App\Services\Arr;
use Lox\AST\StatementVisitor;
use Lox\Scan\Token;

class FunctionStatement extends Statement
{

    /**
     * @param Token $tokenStart
     * @param Token $name
     * @param array<Token> $parameters
     * @param array<Statement> $body
     */
    public function __construct(Token                 $tokenStart,
                                public readonly Token $name,
                                public readonly array $parameters,
                                public array          $body)
    {
        parent::__construct($tokenStart, Arr::last($this->body)->tokenEnd);
    }

    #[\Override] function accept(StatementVisitor $visitor)
    {
        $visitor->visitFunctionStmt($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return [
            'name'       => $this->name,
            'parameters' => $this->parameters,
            'body'       => $this->body,
        ];
    }
}
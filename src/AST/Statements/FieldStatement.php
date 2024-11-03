<?php

namespace src\AST\Statements;

use src\AST\Expressions\Expression;
use src\AST\StatementVisitor;
use src\Resolver\LoxClassPropertyVisibility;
use src\Scaner\Token;

class FieldStatement extends Statement
{

    public function __construct(
        public readonly Token                      $startToken,
        public readonly LoxClassPropertyVisibility $visibility,
        public readonly bool                       $isStatic,
        public readonly Token                      $name,
        public readonly ?Expression                $initializer)
    {
        $end = $this->initializer != null ? $this->initializer->tokenEnd : $this->name;
        parent::__construct($this->startToken, $end);
    }

    #[\Override] function accept(StatementVisitor $visitor)
    {
        return $visitor->visitFieldStmt($this);
    }

    #[\Override] public function jsonSerialize(): mixed
    {
        return [
            'visibility'  => $this->visibility,
            'name'        => $this->name,
            'initializer' => $this->initializer
        ];
    }

}
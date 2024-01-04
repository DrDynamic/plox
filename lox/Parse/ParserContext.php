<?php

namespace Lox\Parse;

use Lox\AST\Expressions\Expression;

class ParserContext
{
    /** @var int Number of loops the current statement is nested in */
    public int $loopCount = 0;

    /** @var array has one expression for each nested loop. If a loop has no iterator (like while or for(;;)) a null is inserted */
    public array $loopIncrements = [];

    public function enterLoop(Expression|null $iterator = null): void
    {
        $this->loopCount++;
        $this->loopIncrements[] = $iterator;
    }

    public function exitLoop(): void
    {
        $this->loopCount--;
        array_pop($this->loopIncrements);
    }
}
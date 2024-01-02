<?php

namespace Lox\AST\Statements;

use Lox\AST\StatementVisitor;

abstract class Statement implements \JsonSerializable
{
    abstract function accept(StatementVisitor $visitor);
}
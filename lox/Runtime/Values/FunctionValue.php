<?php

namespace Lox\Runtime\Values;

use Lox\AST\Expressions\Expression;
use Lox\AST\Statements\FunctionStatement;
use Lox\AST\Statements\Statement;
use Lox\Interpret\Interpreter;
use Lox\Runtime\Environment;

class FunctionValue extends BaseValue implements CallableValue
{

    public function __construct(
        private readonly FunctionStatement $declaration,
        private readonly Environment       $parentEnvironment)
    {
    }

    #[\Override] public function getType(): LoxType
    {
        return LoxType::Callable;
    }

    #[\Override] public function cast(LoxType $toType, Statement|Expression $cause): BaseValue
    {
        if ($toType == LoxType::String) {
            return new StringValue("<fn {$this->declaration->name->lexeme}>");
        }
        return parent::cast($toType, $cause);
    }


    #[\Override] public function arity(): int
    {
        return count($this->declaration->parameters);
    }

    #[\Override] public function call(array $arguments, Statement|Expression $cause): Value
    {
        /** @var Interpreter $interpreter */
        $interpreter = dependency(Interpreter::class);
        $environment = new Environment($this->parentEnvironment);

        foreach ($this->declaration->parameters as $index => $parameter) {
            $environment->define($parameter, $arguments[$index]);
        }


        $interpreter->executeBlock($this->declaration->body, $environment);
        return dependency(NilValue::class);
    }
}
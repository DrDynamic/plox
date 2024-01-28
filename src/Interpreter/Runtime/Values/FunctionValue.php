<?php

namespace src\Interpreter\Runtime\Values;

use src\AST\Expressions\Expression;
use src\AST\Expressions\FunctionExpression;
use src\AST\Statements\Statement;
use src\Interpreter\Interpreter;
use src\Interpreter\ReturnSignal;
use src\Interpreter\Runtime\Environment;
use src\Interpreter\Runtime\LoxType;

class FunctionValue extends BaseValue implements CallableValue
{

    public function __construct(
        private readonly FunctionExpression $declaration,
        private readonly Environment        $closure)
    {
    }

    #[\Override] public function getType(): LoxType
    {
        return LoxType::Callable;
    }

    #[\Override] public function cast(LoxType $toType, Statement|Expression $cause): BaseValue
    {
        if ($toType == LoxType::String) {
// TODO: add reference to file / line?
            $name = $this->declaration->name
                ? $this->declaration->name->lexeme
                : "anonymous";

            return new StringValue("<fn {$name}>");
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
        $environment = new Environment($this->closure);

        foreach ($this->declaration->parameters as $index => $parameter) {
            $environment->define($parameter, $arguments[$index]);
        }

        try {
            $interpreter->executeBlock($this->declaration->body, $environment);
        } catch (ReturnSignal $signal) {
            return $signal->value;
        }
        return dependency(NilValue::class);
    }
}
<?php

namespace src\Interpreter\Runtime\Values;

use src\AST\Expressions\Expression;
use src\AST\Statements\MethodStatement;
use src\AST\Statements\Statement;
use src\Interpreter\Interpreter;
use src\Interpreter\ReturnSignal;
use src\Interpreter\Runtime\Environment;
use src\Interpreter\Runtime\LoxType;

class MethodValue extends BaseValue implements CallableValue
{

    public function __construct(
        public readonly ClassValue      $class,
        public readonly MethodStatement $declaration,
        public readonly Environment     $closure,
        public readonly bool            $isConstructor,
        public readonly ?InstanceValue  $boundInstance = null)
    {
    }

    public function getVisibility() {
        return $this->declaration->visibility;
    }

    public function getBoundInstance(): ?InstanceValue
    {
        return $this->boundInstance;
    }

    #[\Override] public function getType(): LoxType
    {
        return LoxType::Method;
    }

    #[\Override] public function cast(LoxType $toType, Statement|Expression $cause): BaseValue
    {
        if ($toType == LoxType::String) {
// TODO: add reference to file / line?
            $name = $this->declaration->name
                ? $this->declaration->name->lexeme
                : "anonymous";

            return new StringValue("<method {$name}>");
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
            $environment->defineOrFail($parameter, $arguments[$index]);
        }

        try {
            $interpreter->executeBlock($this->declaration->body, $environment);
        } catch (ReturnSignal $signal) {
            if ($this->isConstructor) {
                return $this->closure->getAt(0, 'this');
            }
            return $signal->value;
        }
        if ($this->isConstructor) {
            return $this->closure->getAt(0, 'this');
        }
        return dependency(NilValue::class);
    }

    public function bindInstance(InstanceValue $instance): MethodValue
    {
        $environment = new Environment($this->closure);
        $environment->defineOrReplace('this', $instance);
        return new MethodValue($this->class, $this->declaration, $environment, $this->isConstructor, $instance);
    }
}
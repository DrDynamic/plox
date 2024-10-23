<?php

namespace src\Interpreter\Runtime\Values;

use src\AST\Expressions\ClassExpression;
use src\AST\Expressions\Expression;
use src\AST\Statements\Statement;
use src\Interpreter\Runtime\LoxType;

class ClassValue extends BaseValue implements CallableValue
{


    public function __construct(
        private readonly ClassExpression $declaration,
        private readonly array           $methods,
    )
    {
    }

    public function getName(): ?string
    {
        return $this->declaration->name?->lexeme;
    }

    public function getMethod(string $methodName): ?FunctionValue
    {
        return $this->methods[$methodName] ?? null;
    }

    public function getType(): LoxType
    {
        return LoxType::Klass;
    }

    #[\Override] public function cast(LoxType $toType, Statement|Expression $cause): BaseValue
    {
        if ($toType == LoxType::String) {
// TODO: add reference to file / line?
            $name = $this->getName() ?? "anonymous";
            return new StringValue("<class {$name}>");
        }
        return parent::cast($toType, $cause);
    }

    public function arity(): int
    {
        return 0;
    }

    public function call(array $arguments, Expression|Statement $cause): Value
    {
        return new InstanceValue($this);
    }


}
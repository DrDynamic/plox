<?php

namespace src\Interpreter\Runtime\Values;

use src\AST\Expressions\ClassExpression;
use src\AST\Expressions\Expression;
use src\AST\Statements\Statement;
use src\Interpreter\Runtime\LoxType;
use src\Interpreter\Runtime\Util\FieldDefinition;
use src\Resolver\LoxClassPropertyVisibility;
use src\Services\Arr;

class ClassValue extends BaseValue implements CallableValue
{

    public function __construct(
        public readonly ClassExpression $declaration,
        public array                    $methods,
        public array                    $fields,
    )
    {
    }

    public function getName(): ?string
    {
        return $this->declaration->name?->lexeme;
    }

    public function addField(string $name, LoxClassPropertyVisibility $visibility, Value $value)
    {
        $this->fields[$name] = new FieldDefinition($visibility, $value);
    }

    public function getMethod(string $methodName): ?MethodValue
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
        $constructor = $this->getMethod('init');
        if ($constructor !== null) {
            return $constructor->arity();
        }
        return 0;
    }

    public function call(array $arguments, Expression|Statement $cause): Value
    {
        $instance    = new InstanceValue($this, $this->makeInstanceFields());
        $constructor = $this->getMethod('init');
        if ($constructor !== null) {
            $constructor->bindInstance($instance)->call($arguments, $cause);
        }

        return $instance;
    }

    private function makeInstanceFields()
    {
        return Arr::clone($this->fields);
    }


}
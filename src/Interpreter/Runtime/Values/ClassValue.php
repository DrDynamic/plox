<?php

namespace src\Interpreter\Runtime\Values;

use src\AST\Expressions\ClassExpression;
use src\AST\Expressions\Expression;
use src\AST\Statements\FieldStatement;
use src\AST\Statements\MethodStatement;
use src\AST\Statements\Statement;
use src\Interpreter\Runtime\Environment;
use src\Interpreter\Runtime\Errors\RuntimeError;
use src\Interpreter\Runtime\ExecutionContext;
use src\Interpreter\Runtime\LoxType;
use src\Interpreter\Runtime\Traits\HasVisibilityAssertion;
use src\Interpreter\Runtime\Util\FieldDefinition;
use src\Resolver\LoxClassPropertyVisibility;
use src\Scaner\Token;
use src\Services\Arr;

class ClassValue extends BaseValue implements CallableValue, GetAccess, SetAccess
{
    use HasVisibilityAssertion;

    public function __construct(
        public readonly ClassExpression $declaration,
        public array                    $staticMethods = [],
        public array                    $staticFields = [],
        public array                    $methods = [],
        public array                    $fields = [],
    )
    {
    }

    public function getName(): ?string
    {
        return $this->declaration->name?->lexeme;
    }

    public function addField(FieldStatement $field, Value $value)
    {
        $definition = new FieldDefinition($field->visibility, $value);
        if ($field->isStatic) {
            $this->staticFields[$field->name->lexeme] = $definition;
        } else {
            $this->fields[$field->name->lexeme] = $definition;
        }
    }

    public function addMethod(MethodStatement $method, Environment $environment)
    {

        $methodValue = new MethodValue($this, $method, $environment, $method->name->lexeme === 'init');
        if ($method->isStatic) {
            $this->staticMethods[$method->name->lexeme] = $methodValue;
        } else {
            $this->methods[$method->name->lexeme] = $methodValue;
        }
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

    public function getOrFail(Token $name, ExecutionContext $executionContext)
    {
        $field = $this->staticFields[$name->lexeme] ?? null;
        if ($field !== null) {
            $this->assertVisibilityAccess($field, $executionContext, $name, $this);
            return $field->value;
        }

        $method = $this->staticMethods[$name->lexeme] ?? null;
        if ($method !== null) {
            $this->assertVisibilityAccess($method, $executionContext, $name, $this);
//            return $method->bindInstance($this);
            return $method;
        }

        throw  new RuntimeError($name, "Undefined propery '$name->lexeme'");
    }

    public function setOrFail(Token $name, Value $value, ExecutionContext $executionContext)
    {
        if (isset($this->staticMethods[$name->lexeme])) {
            throw new RuntimeError($name, "Can't overwrite methods.");
        }

        if (!isset($this->staticFields[$name->lexeme])) {
            $this->staticFields[$name->lexeme] = new FieldDefinition(LoxClassPropertyVisibility::PUBLIC, $value);
            return $value;
        } else {
            $this->assertVisibilityAccess($this->staticFields[$name->lexeme], $executionContext, $name, $this);
            return $this->staticFields[$name->lexeme]->value = $value;
        }
    }

    private function makeInstanceFields()
    {
        return Arr::clone($this->fields);
    }
}
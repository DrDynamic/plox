<?php

namespace src\Interpreter\Runtime;

use src\Interpreter\Runtime\Values\ClassValue;
use src\Interpreter\Runtime\Values\InstanceValue;
use src\Interpreter\Runtime\Values\MethodValue;

class ExecutionContext
{
    private array $stack;

    public function pushContext($context)
    {
        $this->stack[] = $context;
    }

    public function popContext()
    {
        return array_pop($this->stack);
    }

    public function containsClassOrInstanceOf($class)
    {
        foreach ($this->stack as $context) {
            if (($context instanceof InstanceValue
                    || $context instanceof MethodValue)
                && $context->class === $class) {
                return true;
            } else if ($context instanceof ClassValue
                && $context === $class) {
                return true;
            }
        }
        return false;
    }
}
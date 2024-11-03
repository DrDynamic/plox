<?php

namespace src\Interpreter\Runtime\Traits;

use src\Interpreter\Runtime\Errors\RuntimeError;
use src\Interpreter\Runtime\ExecutionContext;
use src\Resolver\LoxClassPropertyVisibility;
use src\Scaner\Token;

trait HasVisibilityAssertion
{

    public function assertVisibilityAccess($property, ExecutionContext $executionContext, Token $name, $class)
    {
        if ($property->getVisibility() !== LoxClassPropertyVisibility::PRIVATE) {
            return;
        }

        if (!$executionContext->containsClassOrInstanceOf($class)) {
            throw new RuntimeError($name, "Can't access private method.");
        }
    }
}
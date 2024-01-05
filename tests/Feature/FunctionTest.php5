<?php

use Lox\Runtime\Values\BaseValue;
use Lox\Runtime\Values\CallableValue;
use Lox\Runtime\Values\LoxType;
use Lox\Runtime\Values\NilValue;
use Lox\Scan\Token;
use Lox\Scan\TokenType;

class PrintMock extends BaseValue implements CallableValue
{

    public bool $wasCalled = false;

    #[\Override] public function getType(): LoxType
    {
        return LoxType::Callable;
    }

    #[\Override] public function arity(): int
    {
        return 0;
    }

    #[\Override] public function call(array $arguments, \Lox\AST\Statements\Statement|\Lox\AST\Expressions\Expression $cause): \Lox\Runtime\Values\Value
    {
        return dependency(NilValue::class);
    }
}

it('can call functions', function () {
    $printVar  = new Token(TokenType::IDENTIFIER, 'print', null, 0);
    $printFunc = new PrintMock();
    $this->environment->delete($printVar);
    $this->environment->define($printVar, $printFunc);

    execute('print("Hello, World")');
    expect($printFunc->wasCalled)
        ->toBeTrue();

});
<?php

use Lox\Runtime\LoxType;
use Lox\Runtime\Values\BaseValue;
use Lox\Runtime\Values\CallableValue;
use Lox\Runtime\Values\NilValue;
use Lox\Runtime\Values\NumberValue;
use Lox\Runtime\Values\StringValue;
use Lox\Scaner\Token;
use Lox\Scaner\TokenType;

class MockFunction extends BaseValue implements CallableValue
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
        $this->wasCalled = true;
        return dependency(NilValue::class);
    }
}

it('can call native functions', function () {
    $mockVar  = new Token(TokenType::IDENTIFIER, 'myAwesomeFunction', null, 0);
    $mockFunc = new MockFunction();
    $this->environment->define($mockVar, $mockFunc);

    execute('myAwesomeFunction()');
    expect($mockFunc->wasCalled)
        ->toBeTrue();
});

it('can call user defined functions', function () {
    execute('
    var a = nil
    function myAwesomeFunction() {
        a="good call!" 
    }
    myAwesomeFunction()
    ');
    expect($this->environment)
        ->toHave('a', new StringValue('good call!'));
});

it('lets functions access their parents scope', function () {
    execute('
    var a = nil
    function parent() {
        var b = nil
        function child() {
            b = "a value"
        }
        child()
        a = b
    }
    parent()
    ');
    expect($this->environment)
        ->toHave('a', new StringValue('a value'));
});

it('can have a return value', function () {
    // it implicitly returns nil (when no return statement gets executed)
    execute('
        function test() {
            var internal = nil
        }
        
        var a = test()
    ');
    expect($this->environment)
        ->toHave('a', new NilValue())
        ->toNotHave('internal');

    resetLox();

    execute('
        function test() {
            return 5
        }
        
        var a = test()
    ');
    expect($this->environment)
        ->toHave('a', new NumberValue(5));

    resetLox();

    execute('
        var a = nil
        function test() {
            return
            
            a = 42
        }
        
        test()
    ');
    expect($this->environment)
        ->toHave('a', new NilValue());
});

// TODO: implement
//it('can call strings as functions', function () {
//    $mockVar  = new Token(TokenType::IDENTIFIER, 'myAwesomeFunction', null, 0);
//    $mockFunc = new MockFunction();
//    $this->environment->define($mockVar, $mockFunc);
//
//    execute('"myAwesomeFunction"()');
//    expect($mockFunc->wasCalled)
//        ->toBeTrue();
//});
<?php

use phpDocumentor\Reflection\PseudoTypes\IntegerValue;
use src\Interpreter\Runtime\LoxType;
use src\Interpreter\Runtime\Values\BaseValue;
use src\Interpreter\Runtime\Values\CallableValue;
use src\Interpreter\Runtime\Values\NilValue;
use src\Interpreter\Runtime\Values\NumberValue;
use src\Interpreter\Runtime\Values\StringValue;

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

    #[\Override] public function call(array $arguments, \src\AST\Statements\Statement|\src\AST\Expressions\Expression $cause): \src\Interpreter\Runtime\Values\Value
    {
        $this->wasCalled = true;
        return dependency(NilValue::class);
    }
}

it('can call native functions', function () {
    $mockFunc = new MockFunction();
    $this->environment->defineOrReplace('myAwesomeFunction', $mockFunc);

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

it('can have arguments', function () {
    execute('
        function add(a, b) {
            return a + b;
        }
        var result = add(1,2);
    ');

    expect($this->environment)
        ->toHave('a', new IntegerValue(3));
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
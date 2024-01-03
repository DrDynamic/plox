<?php


use Lox\Runtime\Values\NilValue;
use Lox\Runtime\Values\NumberValue;

it('can execute code on conditions', function () {
    execute('var a var b if(true) if(false) a=1 else b=1');
    expect($this->environment)
        ->toHave('a', new NilValue())
        ->toHave('b', new NumberValue(1));

    test()->interpreter->resetEnvironment();

//    dd()

    resetLox();

    execute('var a var b if(true) if(true) a=1 else b=1');
    expect($this->environment)
        ->toHave('a', new NumberValue(1))
        ->toHave('b', new NilValue());

    resetLox();
    execute('var a var b if(false) if(true) a=1 else b=1');
    expect($this->environment)
        ->toHave('a', new NilValue())
        ->toHave('b', new NilValue());


});
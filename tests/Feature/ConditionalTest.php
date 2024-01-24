<?php


use Lox\Runtime\Values\BooleanValue;
use Lox\Runtime\Values\NilValue;
use Lox\Runtime\Values\NumberValue;
use Lox\Runtime\Values\StringValue;

it('can execute code on conditions', function () {
    execute('var a var b if(true) if(false) a=1 else b=1');
    expect($this->environment)
        ->toHave('a', new NilValue())
        ->toHave('b', new NumberValue(1));

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

it('supports ternary operator', function () {
    expect(evaluate('true ? 4 : 2'))
        ->toEqual(new NumberValue(4));

    expect(evaluate('false ? 4 : 2'))
        ->toEqual(new NumberValue(2));

    expect(evaluate('(true) ? 4 : 2'))
        ->toEqual(new NumberValue(4));

    expect(evaluate('(false) ? 4 : 2'))
        ->toEqual(new NumberValue(2));
});

it('supports logical operators', function () {
    // or expressions
    expect(evaluate('false or true'))
        ->toEqual(new BooleanValue(true));

    expect(evaluate('true or false'))
        ->toEqual(new BooleanValue(true));

    expect(evaluate('true or true'))
        ->toEqual(new BooleanValue(true));

    expect(evaluate('false or false'))
        ->toEqual(new BooleanValue(false));

    // and expressions
    expect(evaluate('false and true'))
        ->toEqual(new BooleanValue(false));

    expect(evaluate('true and false'))
        ->toEqual(new BooleanValue(false));

    expect(evaluate('false and false'))
        ->toEqual(new BooleanValue(false));

    expect(evaluate('true and true'))
        ->toEqual(new BooleanValue(true));
});

it('evaluates logicals to their value instead of bool', function () {
    expect(evaluate('nil or "hi"'))
        ->toEqual(new StringValue("hi"));

    expect(evaluate('5 or 0'))
        ->toEqual(new NumberValue(5));

    expect(evaluate('nil and "string"'))
        ->toEqual(new NilValue());

    expect(evaluate('"string" and nil'))
        ->toEqual(new NilValue());
});
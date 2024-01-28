<?php
// TODO: error reporting

use src\Interpreter\Runtime\Values\BooleanValue;
use src\Interpreter\Runtime\Values\NumberValue;
use src\Interpreter\Runtime\Values\StringValue;

it('can calculate', function () {
    expect(evaluate('(2+4)*(4+2)'))
        ->toEqual(new NumberValue(36));

    expect(evaluate('2+4*4+2'))
        ->toEqual(new NumberValue(20));

});

it('supports the comma operator', function () {
    expect(evaluate('(2,4,3)'))
        ->toEqual(new NumberValue(3));

    expect(evaluate('2,4,5'))
        ->toEqual(new NumberValue(5));
});

it('compares numbers with strings', function () {
    expect(evaluate('"Lorem" < 6'))
        ->toEqual(new BooleanValue(true));
    expect(evaluate('"Lorem" < 5'))
        ->toEqual(new BooleanValue(false));

    expect(evaluate('6 > "Lorem"'))
        ->toEqual(new BooleanValue(true));
    expect(evaluate('5 > "Lorem"'))
        ->toEqual(new BooleanValue(false));

    expect(evaluate('"Lorem" <= 5'))
        ->toEqual(new BooleanValue(true));
    expect(evaluate('"Lorem" <= 4'))
        ->toEqual(new BooleanValue(false));

    expect(evaluate('"Lorem" > 4'))
        ->toEqual(new BooleanValue(true));
    expect(evaluate('"Lorem" > 5'))
        ->toEqual(new BooleanValue(false));

    expect(evaluate('"Lorem" >= 5'))
        ->toEqual(new BooleanValue(true));
    expect(evaluate('"Lorem" >= 6'))
        ->toEqual(new BooleanValue(false));
});

it('compares numbers', function () {
    expect(evaluate('5 == 5'))
        ->toEqual(new BooleanValue(true));
    expect(evaluate('5 == 4'))
        ->toEqual(new BooleanValue(false));

    expect(evaluate('5 != 4'))
        ->toEqual(new BooleanValue(true));
    expect(evaluate('5 != 5'))
        ->toEqual(new BooleanValue(false));

    expect(evaluate('5 < 6'))
        ->toEqual(new BooleanValue(true));
    expect(evaluate('5 < 5'))
        ->toEqual(new BooleanValue(false));

    expect(evaluate('5 <= 5'))
        ->toEqual(new BooleanValue(true));
    expect(evaluate('5 <= 4'))
        ->toEqual(new BooleanValue(false));


    expect(evaluate('5 > 4'))
        ->toEqual(new BooleanValue(true));
    expect(evaluate('5 > 5'))
        ->toEqual(new BooleanValue(false));

    expect(evaluate('5 >= 5'))
        ->toEqual(new BooleanValue(true));

    expect(evaluate('5 >= 6'))
        ->toEqual(new BooleanValue(false));
});

it('can concatenate string', function () {
    expect(evaluate('"Hello" + ", World"'))
        ->toEqual(new StringValue('Hello, World'));

    expect(evaluate('"Fifty" + 5'))
        ->toEqual(new StringValue('Fifty5'));

    expect(evaluate('5 + "Fifty"'))
        ->toEqual(new StringValue('5Fifty'));
});

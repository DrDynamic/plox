<?php
// TODO: error reporting

function evaluate(string $source)
{
    $lox = dependency(\Lox\Lox::class);
    return $lox->runString($source)->value;
}

it('can calculate', function () {
    expect(evaluate("(2+4)*(4+2)"))
        ->toEqual(36);

    expect(evaluate("2+4*4+2"))
        ->toEqual(20);

});

it('supports the comma operator', function () {
    expect(evaluate('(2,4)'))
        ->toEqual(4);

    expect(evaluate('2,4'))
        ->toEqual(4);
});

it('supports ternary operator', function () {
    expect(evaluate('true ? 4 : 2'))
        ->toEqual(4);

    expect(evaluate('false ? 4 : 2'))
        ->toEqual(2);

    expect(evaluate('(true) ? 4 : 2'))
        ->toEqual(4);

    expect(evaluate('(false) ? 4 : 2'))
        ->toEqual(2);
});


it('compares numbers with strings', function () {
    expect(evaluate('"Lorem" < 6'))
        ->toBeTrue();
    expect(evaluate('"Lorem" < 5'))
        ->toBeFalse();

    expect(evaluate('6 > "Lorem"'))
        ->toBeTrue();
    expect(evaluate('5 > "Lorem"'))
        ->toBeFalse();

    expect(evaluate('"Lorem" <= 5'))
        ->toBeTrue();
    expect(evaluate('"Lorem" <= 4'))
        ->toBeFalse();

    expect(evaluate('"Lorem" > 4'))
        ->toBeTrue();
    expect(evaluate('"Lorem" > 5'))
        ->toBeFalse();

    expect(evaluate('"Lorem" >= 5'))
        ->toBeTrue();
    expect(evaluate('"Lorem" >= 6'))
        ->toBeFalse();
});

it('compares numbers', function () {
    expect(evaluate('5 == 5'))
        ->toBeTrue();
    expect(evaluate('5 == 4'))
        ->toBeFalse();

    expect(evaluate('5 != 4'))
        ->toBeTrue();
    expect(evaluate('5 != 5'))
        ->toBeFalse();

    expect(evaluate('5 < 6'))
        ->toBeTrue();
    expect(evaluate('5 < 5'))
        ->toBeFalse();

    expect(evaluate('5 <= 5'))
        ->toBeTrue();
    expect(evaluate('5 <= 4'))
        ->toBeFalse();

    expect(evaluate('5 > 4'))
        ->toBeTrue();
    expect(evaluate('5 > 5'))
        ->toBeFalse();

    expect(evaluate('5 >= 5'))
        ->toBeTrue();
    expect(evaluate('5 >= 6'))
        ->toBeFalse();
});

it('can concatenate string', function () {
    expect(evaluate('"Hello" + ", World"'))
        ->toEqual('Hello, World');

    expect(evaluate('"Fifty" + 5'))
        ->toEqual('Fifty5');
});
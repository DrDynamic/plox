<?php
// TODO: error reporting

function evaluate(string $source)
{
    /** @var \Lox\Scan\Scanner $scanner */
    $scanner = dependency(\Lox\Scan\Scanner::class);
    /** @var \Lox\Parse\Parser $parser */
    $parser = dependency(\Lox\Parse\Parser::class);
    /** @var \Lox\Interpret\Interpreter $interpreter */
    $interpreter = dependency(\Lox\Interpret\Interpreter::class);

    $tokens = $scanner->scanTokens($source);
    $ast    = $parser->parse($tokens);
    return $interpreter->interpret($ast);
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
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

it('supports the comma operator', function() {
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

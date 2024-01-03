<?php

use Lox\Runtime\Values\BooleanValue;
use Lox\Runtime\Values\NilValue;
use Lox\Runtime\Values\NumberValue;
use Lox\Runtime\Values\StringValue;
use Lox\Scan\Token;
use Lox\Scan\TokenType;

expect()->extend('variable', function ($name, $value) {
    $variable = new Token(TokenType::IDENTIFIER, $name, null, 0);
    \PHPUnit\Framework\assertTrue(test()->environment->has($variable));
    \PHPUnit\Framework\assertEquals(test()->environment->get($variable), $value);
});

it('declare variables', function () {
    expect(evaluate('var a'))
        ->variable('a', new NilValue());
    expect(evaluate('var b=nil'))
        ->variable('b', new NilValue());
    expect(evaluate('var c=true'))
        ->variable('c', new BooleanValue(true));
    expect(evaluate('var d=1'))
        ->variable('d', new NumberValue(1));
    expect(evaluate('var e="Lorem"'))
        ->variable('e', new StringValue("Lorem"));
});
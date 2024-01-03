<?php

use Lox\Runtime\Values\BooleanValue;
use Lox\Runtime\Values\NilValue;
use Lox\Runtime\Values\NumberValue;
use Lox\Runtime\Values\StringValue;
use Lox\Scan\Token;
use Lox\Scan\TokenType;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

expect()->extend('toHave', function ($name, $value = null) {
    $variable = new Token(TokenType::IDENTIFIER, $name, null, 0);

    assertTrue($this->value->has($variable));
    if ($value !== null) {
        assertEquals($this->value->get($variable), $value);
    }
});

expect()->extend('toNotHave', function ($name) {
    $variable = new Token(TokenType::IDENTIFIER, $name, null, 0);
    assertFalse($this->value->has($variable));
});

it('can declare variables', function () {
    execute('var a');
    expect($this->environment)
        ->toHave('a', new NilValue());

    execute('var b=nil');
    expect($this->environment)
        ->toHave('b', new NilValue());

    execute('var c=true');
    expect($this->environment)
        ->toHave('c', new BooleanValue(true));

    execute('var d=1');
    expect($this->environment)
        ->toHave('d', new NumberValue(1));
    execute('var e="Lorem"');
    expect($this->environment)
        ->toHave('e', new StringValue("Lorem"));

});

it('can mutate variables (even to different types)', function () {
    execute('var a');
    expect($this->environment)
        ->toHave('a', new NilValue());

    execute('var b=nil');
    expect($this->environment)
        ->toHave('b', new NilValue());

    execute('a="One"');
    expect($this->environment)
        ->toHave('a', new StringValue("One"));

    execute('b=1');
    expect($this->environment)
        ->toHave('b', new NumberValue(1));
});

it('supports scoped variables', function () {
    execute('var a {var b}');
    expect($this->environment)
        ->toHave('a', new NilValue())
        ->toNotHave('b');
});
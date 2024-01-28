<?php

use src\Interpreter\Runtime\Values\BooleanValue;
use src\Interpreter\Runtime\Values\NilValue;
use src\Interpreter\Runtime\Values\NumberValue;
use src\Interpreter\Runtime\Values\StringValue;


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
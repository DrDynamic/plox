<?php

use Lox\Runtime\Values\NumberValue;

it('supports while loops', function () {
    execute('
   var i = 0
   while(i < 5) {
    i = i + 1
   }
   ');
    expect($this->environment)
        ->toHave('i', new NumberValue(5));
});
<?php

use src\Interpreter\Runtime\Values\NumberValue;

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

it('supports for loops', function () {
    execute('
   var y = 0
   for(var i=0 ; i<5 ; i=i+1) {
    y = y + 1
   }
   ');
    expect($this->environment)
        ->toNotHave('i')
        ->toHave('y', new NumberValue(5));

    resetLox();

    execute('
   var y = 0
   for(; y<5 ; y=y+1) {
   // do something
   }
   ');
    expect($this->environment)
        ->toHave('y', new NumberValue(5));

    resetLox();

    execute('
   var y = 0
   for(; y<5 ;) {
    y = y+1
   }
   ');
    expect($this->environment)
        ->toHave('y', new NumberValue(5));
});

it('supports break and continue statements', function () {
    execute('
    var y
    for(var i=0; i<=5; i=i+1) {
      if(i == 1 or i == 3 or i == 5) continue
      y = i
    }
    ');
    expect($this->environment)
        ->toHave('y', new NumberValue(4));


    resetLox();

    execute('
    var y
    for(var i=0; i<=100; i=i+1) {
      if(i == 10) break
      y = i
    }
    ');
    expect($this->environment)
        ->toHave('y', new NumberValue(9));


    resetLox();

    execute('
    var y=0
    var z=0
    while(y<10) {
      for(var i=0; i<=100; i=i+1) {
        if(i > 10) {
            y = y+1
            break
        }
      }
      z = y
    }
    print(z)
    ');
    expect($this->environment)
        ->toHave('z', new NumberValue(10));

});
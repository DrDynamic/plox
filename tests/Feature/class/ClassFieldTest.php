<?php

use src\Interpreter\Runtime\Values\BooleanValue;

it('can inherit fields', function () {
    execute('
    class Animal {
        public var isAlive = true;
    }
    
    class Cat extends Animal {
        
    }
    
    var cat = Cat();
    var isAlive = cat.isAlive;
    ');

    expect($this->environment)
        ->toHave('isAlive', new BooleanValue(true));
});
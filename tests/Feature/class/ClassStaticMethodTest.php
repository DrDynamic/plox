<?php

use src\Interpreter\Runtime\Values\StringValue;

it('can inherit static methods', function () {
    execute('
    class Animal {
        public static function eat() {
            return "nom nom";
        }
    }
    
    class Cat extends Animal {
    }
    var result = Cat.eat();
    ');
    expect($this->environment)
        ->toHave('result', new StringValue("nom nom"));
});

it('can override static methods', function () {
    execute('
    class Animal {
        public static function eat() {
            return "I don\'t know what";
        }
    }
    
    class Cat extends Animal {
        public static function eat() {
            return "nom nom";
        }
    }
    var result = Cat.eat();
    ');
    expect($this->environment)
        ->toHave('result', new StringValue("nom nom"));
});
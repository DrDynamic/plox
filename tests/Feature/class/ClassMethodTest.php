<?php

use src\Interpreter\Runtime\Values\StringValue;

it('can inherit methods', function () {
    execute('
    class Animal {
        public function eat() {
            return "nom nom";
        }
    }
    
    class Cat extends Animal {
    }
    var cat = Cat();
    var result = cat.eat();
    ');
    expect($this->environment)
        ->toHave('result', new StringValue("nom nom"));
});

it('can override methods', function () {
    execute('
    class Animal {
        public function eat() {
            return "I don\'t know what";
        }
    }
    
    class Cat extends Animal {
        public function eat() {
            return "nom nom";
        }
    }
    var cat = Cat();
    var result = cat.eat();
    ');
    expect($this->environment)
        ->toHave('result', new StringValue("nom nom"));
});
<?php

it('can declare classes', function () {
    execute('
    class Greeter {
        function sayHello() {
            print("Hello");
        }
    }
   ');
    expect($this->environment)
        ->toHave('Greeter');
});

it('can declare anonymous classes', function () {
    execute('
    var greeter = class {
        function sayHello() {
            print("Hello");
        }
    }
   ');
    expect($this->environment)
        ->toHave('greeter');
});


<?php

use src\Interpreter\Runtime\Values\StringValue;

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

it('can declare empty classes', function () {
    execute('
    class Greeter {}
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

it('can instantiate classes', function () {
    execute('
    class Greeter {}
    var greeter = Greeter();
    ');
    expect($this->environment)
        ->toHave('greeter');
});

it('can access fields on instances', function () {
    execute('
    var instance = class{}();
    instance.key = "value";
    
    var readValue = instance.key;
    ');

    expect($this->environment)
        ->toHave('readValue', new StringValue('value'));
});

it('can access methods on instances', function () {
    execute('
        class Greeter {
            function getGreeting(name) {
                return "Hello "+name;
            }
        }
        
        var greeter = Greeter();
        var result = greeter.getGreeting("John");
    ');
    expect($this->environment)
        ->toHave('result', new StringValue('Hello John'));
});

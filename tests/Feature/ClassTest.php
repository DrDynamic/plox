<?php

use phpDocumentor\Reflection\PseudoTypes\IntegerValue;
use src\Interpreter\Runtime\Values\BooleanValue;
use src\Interpreter\Runtime\Values\NumberValue;
use src\Interpreter\Runtime\Values\StringValue;
use src\Scaner\Token;
use src\Scaner\TokenType;
use src\Services\ErrorReporter;

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

it('can access the current instance on methods', function () {
    execute('
        class Greeter {
            function getGreeting() {
                return "Hello " + this.name;
            }
        }
        
        var greeter = Greeter();
        greeter.name = "John";
        var result = greeter.getGreeting();
    ');
    expect($this->environment)
        ->toHave('result', new StringValue('Hello John'));
});

it('reports an error when this is used outside of a class', function () {
    $thisToken     = new Token(TokenType::THIS, 'this', null, 1);
    $errorReporter = mock(ErrorReporter::class);
    $errorReporter->shouldReceive('errorAt')->with(Mockery::any(), "Can't use 'this' outside of a class.")->once()->andSet('hadError', true);
    resetLox([
        ErrorReporter::class => $errorReporter
    ]);

    execute('var a = this;');

    $thisToken     = new Token(TokenType::THIS, 'this', null, 1);
    $errorReporter = mock(ErrorReporter::class);
    $errorReporter->shouldReceive('errorAt')->with(Mockery::any(), "Can't use 'this' outside of a class.")->once()->andSet('hadError', true);


    resetLox([
        ErrorReporter::class => $errorReporter
    ]);

    execute('function(){var a = this;}');
});

it('can have a constructor', function (){
    execute('
    class Person {
        function init(name, age, isAlive) {
            this.name = name;
            this.age = age;
            this.isAlive = isAlive;
        }
    }
    
    var john = Person("John Doe", 42, true);
    var name = john.name;
    var age = john.age;
    var isAlive = john.isAlive;
    ');
    expect($this->environment)
        ->toHave('john')
        ->toHave('name', new StringValue('John Doe'))
        ->toHave('age', new NumberValue(42))
        ->toHave('isAlive', new BooleanValue(true));
})->only();
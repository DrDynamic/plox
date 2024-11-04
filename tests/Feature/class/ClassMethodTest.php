<?php

use src\Interpreter\Runtime\Values\StringValue;
use src\Services\ErrorReporter;

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

it('can call parent methods via super', function () {
    execute('
    class Animal {
        public function eat() {
            return "nom nom";
       }
    }
    
    class Cat extends Animal {
        public function eat() {
            return super.eat()
        }
    }
    
    class Bombay extends Cat {}
    
    var cat = Bombay();
    var result = cat.eat();
   ');
    expect($this->environment)
        ->toHave('result', new StringValue("nom nom"));
});

it('can\'t call super outside if a class', function () {
    $errorReporter = mock(ErrorReporter::class);
    $errorReporter->allows()->errorAt(Mockery::any(), "Can't use 'super' outside of a class.")
        ->andSet('hadError', true)
        ->once();
    resetLox([
        ErrorReporter::class => $errorReporter
    ]);
    execute('
        var result = super.someMethod();
    ');
});

it('can\'t call methods that don\'t exist via super', function () {
    $errorReporter = mock(ErrorReporter::class);
    $errorReporter->allows()->runtimeError(Mockery::any()) //, "Can't use 'super' outside of a class.")
    ->andSet('hadError', true)
        ->once();
    resetLox([
        ErrorReporter::class => $errorReporter
    ]);
    execute('
    class Animal {
    }
    class Cat extends Animal {
        public function eat() {
            return super.eat();
        }
    }
    
    Cat().eat();
    ');
});

it('can\'t call super in a class that doesn\'t has a superclass', function () {
    $errorReporter = mock(ErrorReporter::class);
    $errorReporter->allows()->errorAt(Mockery::any(), "Can't use 'super' in a class with no superclass.")
        ->andSet('hadError', true)
        ->once();
    resetLox([
        ErrorReporter::class => $errorReporter
    ]);
    execute('
    class Animal {
        public function eat() {
            return super.eat();
        }
    }
    Animal().eat();
    ');
});
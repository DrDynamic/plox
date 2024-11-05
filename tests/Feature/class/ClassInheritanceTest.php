<?php

use src\Interpreter\Runtime\Values\BooleanValue;
use src\Interpreter\Runtime\Values\StringValue;
use src\Services\ErrorReporter;


it('can\'t inherit itself', function () {
    $errorReporter = mock(ErrorReporter::class);
    $errorReporter->allows()->errorAt(Mockery::any(), "A class can't inherit from itself.")
        ->andSet('hadError', true)
        ->once();
    resetLox([
        ErrorReporter::class => $errorReporter
    ]);
    execute('
    class Animal extends Animal {
        public var isAlive = true;
    }
    ');
});


it('runs the constructor of a superclass if it has one', function () {
    execute('
    class Animal {
        function init() {
            this.isAlive = true;
        }
    }
    
    class Cat extends Animal {}
    var cat = Cat();
    var result = cat.isAlive;
    ');
    expect($this->environment)
        ->toHave('result', new BooleanValue(true));
});

it('doesn\'t run the constructor of a superclass if subclass has one', function () {
    execute('
    class Animal {
        function init() {
            this.isAlive = true;
        }
    }
    
    class Cat extends Animal {
        function init() {
            this.color = "Black";
        }
    }
    var cat = Cat();
    var result = cat.color;
    ');
    expect($this->environment)
        ->toHave('result', new StringValue("Black"));
});
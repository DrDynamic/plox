<?php

use src\Interpreter\Runtime\Values\BooleanValue;
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


it('runs runs the constructor of a superclass if it has one', function () {
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
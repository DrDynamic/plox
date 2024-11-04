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

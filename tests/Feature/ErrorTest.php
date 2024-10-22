<?php

use src\Services\ErrorReporter;

class ErrorReporterMock extends ErrorReporter
{
    public $mock = true;

    public function reset()
    {
        $this->hadError        = false;
        $this->hadRuntimeError = false;
    }

    #[\Override] public function runtimeError(\src\Interpreter\Runtime\Errors\RuntimeError $runtimeError)
    {
        $this->hadRuntimeError = true;
    }

    #[\Override] public function report(int $line, string $where, string $message)
    {
        $this->hadError = true;
    }
}

beforeEach(function () {
    resetLox([
        ErrorReporter::class => new ErrorReporterMock()
    ]);
});

it('rises an error if completion statements are not in a loop', function () {
    execute('
    break
    ');

    expect(test()->errorReporter->hadError)->toBeTrue();

    resetLox([
        ErrorReporter::class => new ErrorReporterMock()
    ]);

    execute('
    var a = 0;
    while(a < 10) {
        a = a+1
        break
    }
    ');
    expect(test()->errorReporter->hadError)->toBeFalse();

});

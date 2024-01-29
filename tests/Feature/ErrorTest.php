<?php

use src\Services\Dependency\Dependency;
use src\Services\ErrorReporter;

class ErrorReporterMock extends ErrorReporter
{
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

Dependency::getInstance()->singleton(ErrorReporter::class, new ErrorReporterMock());

it('rises an error if completion statements are not in a loop', function () {
    $reporter = dependency(ErrorReporter::class);
    $reporter->reset();

    execute('
    break
    ');

    expect($reporter->hadError)->toBeTrue();

    resetLox();

    execute('
    var a = 0;
    while(a < 10) {
        a = a+1
        break
    }
    ');

});

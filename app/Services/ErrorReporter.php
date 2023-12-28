<?php

namespace App\Services;

use App\Attributes\Singleton;

#[Singleton]
class ErrorReporter
{
    public bool $hasError = false;

    public function reset() {
        $this->hasError = false;
    }
    public function error(int $line, string $message)
    {
        $this->report($line, "", $message);
    }

    public function report(int $line, string $where, string $message)
    {
        fwrite(STDERR, "[$line] Error $where: $message");
        $this->hasError = true;
    }

}
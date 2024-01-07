<?php

namespace Lox\Runtime\Native;

use App\Attributes\Singleton;
use Lox\Runtime\Native\Functions\LoxClock;
use Lox\Runtime\Native\Functions\LoxPrint;

// TODO: find better name / structure (maybe use this for Interpreter implemented functions, classes and FFI)
#[Singleton]
class Natives
{
    const FUNCTIONS = [
        'print' => LoxPrint::class,
        'clock' => LoxClock::class,
    ];

    public readonly array $nativeFunctions;

    public function __construct()
    {
        // initialize native functions
        $this->nativeFunctions = array_map(fn($class) => new $class, self::FUNCTIONS);
    }
}
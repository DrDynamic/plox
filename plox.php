<?php

use src\Lox;
use src\Services\Dependency\Dependency;

require __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/src/helpers.php';

// TODO: not very clean... (needed for constructor of lox\Interpreter\Interpreter.php) / Also in tests/Pest.php
Dependency::getInstance()->instance(WeakMap::class, fn() => new WeakMap());

/** @var Lox $lox */
$lox = dependency(Lox::class);
if (isset($argv[1])) {
    if (isset($argv[2]) && $argv[2] == "--print") {
        $result =  $lox->reverseAST($argv[1]);
        echo $result;
        return;
    } else if (isset($argv[2]) && $argv[2] == "--json") {
        $result =  $lox->jsonAST($argv[1]);
        echo $result;
        return;
    }
    $lox->runFile($argv[1]);
} else {
    $lox->runCli();
}

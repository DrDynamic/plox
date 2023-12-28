<?php
require __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/app/Services/helpers.php';

use Lox\Lox;

$lox = dependency(Lox::class);

$s = dependency(\App\Services\ServiceA::class);

$lox->runCli();

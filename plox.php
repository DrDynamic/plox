<?php
use Lox\Lox;

require __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/app/Services/helpers.php';

/*
 *\/
 /*
 */

/** @var Lox $lox */
$lox = dependency(Lox::class);
if(isset($argv[1])) {
    $lox->runFile($argv[1]);
}else {
    $lox->runCli();
}

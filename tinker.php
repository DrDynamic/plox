<?php

use Lox\Lox;

require __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/app/Services/helpers.php';


/** @var Lox $lox */
$lox = dependency(Lox::class);

$code = '// this is a comment
(( )){} // grouping stuff
!*+-/=<> <= == // operators
"Lorem ipsum dolor sit amet!"';
$lox->runString($code);

$lox->runCli();

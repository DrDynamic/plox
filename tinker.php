<?php

use Lox\Lox;

require __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/app/Services/helpers.php';


dependency(Lox::class)->runCli();

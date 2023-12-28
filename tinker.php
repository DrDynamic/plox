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

$source = '
    // Single-character tokens.
    (){},.-+;/*
    
    // One or two character tokens.
    ! != = == > >= < <=
    
    // Literals.
    //case IDENTIFIER;
    "Lorem Ipsum"13.37
    
    /********
 *\/
 /* 
 */
    
    // Keywords.
    _identifier_ and class else false fun for if nil or print return super this true var while
';
$lox->runString($source);

$lox->runCli();

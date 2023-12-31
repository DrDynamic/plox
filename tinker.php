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
function runTokens($lox)
{
    $source = '
    // Single-character tokens.
    (){},.-+;*/
    
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
}
//runTokens($lox);
//dump(dependency(\Lox\AST\AstPrinter::class)->print($root, true));

$lox->runCli();

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

$root = new \Lox\AST\Expressions\Binary(
    new \Lox\AST\Expressions\Literal(2),
    new \Lox\Scan\Token(\Lox\Scan\TokenType::STAR, "*", null, 0),
    new \Lox\AST\Expressions\Grouping(
        new \Lox\AST\Expressions\Binary(
            new \Lox\AST\Expressions\Literal(5),
            new \Lox\Scan\Token(\Lox\Scan\TokenType::PLUS, "+", null, 0),
            new \Lox\AST\Expressions\Literal(5)
        )
    )
);

dump(dependency(\Lox\AST\AstPrinter::class)->print($root, true));

$lox->runCli();

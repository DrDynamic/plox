<?php

use Lox\Scan\Scanner;
use Lox\Scan\TokenType;

require_once __DIR__.'/../../app/Services/helpers.php';

expect()->extend('toHaveType', function (TokenType $type) {
    \PHPUnit\Framework\assertEquals($type, $this->value->tokenType);
});

expect()->extend('toHaveLexeme', function (string $lexeme) {
    \PHPUnit\Framework\assertEquals($lexeme, $this->value->lexeme);
});

expect()->extend('toHaveLiteral', function ($literal) {
    \PHPUnit\Framework\assertEquals($literal, $this->value->literal);
});
expect()->extend('toHaveLine', function (int $line) {
    \PHPUnit\Framework\assertEquals($line, $this->value->line);
});

it('parses sourcecode to tokens', function () {
    $source = '
    // Single-character tokens.
    (){},.-+;*/
    
    // One or two character tokens.
    ! != = == > >= < <=
    
    // Literals.
    //case IDENTIFIER;
    "Lorem Ipsum"13.37
///*    
        /********
 *\/
 /* 
//
 */
    
    // Keywords.
    _identifier_ and class else false fun for if nil or print return super this true var while
';


    /** @var Scanner $scanner */
    $scanner = dependency(\Lox\Scan\Scanner::class);

    $tokens = $scanner->scanTokens($source);

    $line           = 3;
    $expectedTokens = [
        ['type' => TokenType::LEFT_PAREN, 'lexeme' => "(", 'literal' => null, 'line' => $line],
        ['type' => TokenType::RIGHT_PAREN, 'lexeme' => ")", 'literal' => null, 'line' => $line],
        ['type' => TokenType::LEFT_BRACE, 'lexeme' => "{", 'literal' => null, 'line' => $line],
        ['type' => TokenType::RIGHT_BRACE, 'lexeme' => "}", 'literal' => null, 'line' => $line],
        ['type' => TokenType::COMMA, 'lexeme' => ",", 'literal' => null, 'line' => $line],
        ['type' => TokenType::DOT, 'lexeme' => ".", 'literal' => null, 'line' => $line],
        ['type' => TokenType::MINUS, 'lexeme' => "-", 'literal' => null, 'line' => $line],
        ['type' => TokenType::PLUS, 'lexeme' => "+", 'literal' => null, 'line' => $line],
        ['type' => TokenType::SEMICOLON, 'lexeme' => ";", 'literal' => null, 'line' => $line],
        ['type' => TokenType::STAR, 'lexeme' => "*", 'literal' => null, 'line' => $line],
        ['type' => TokenType::SLASH, 'lexeme' => "/", 'literal' => null, 'line' => $line]
    ];
    $line           = 6;
    $expectedTokens = array_merge($expectedTokens, [
        ['type' => TokenType::BANG, 'lexeme' => "!", 'literal' => null, 'line' => $line],
        ['type' => TokenType::BANG_EQUAL, 'lexeme' => "!=", 'literal' => null, 'line' => $line],
        ['type' => TokenType::EQUAL, 'lexeme' => "=", 'literal' => null, 'line' => $line],
        ['type' => TokenType::EQUAL_EQUAL, 'lexeme' => "==", 'literal' => null, 'line' => $line],
        ['type' => TokenType::GREATER, 'lexeme' => ">", 'literal' => null, 'line' => $line],
        ['type' => TokenType::GREATER_EQUAL, 'lexeme' => ">=", 'literal' => null, 'line' => $line],
        ['type' => TokenType::LESS, 'lexeme' => "<", 'literal' => null, 'line' => $line],
        ['type' => TokenType::LESS_EQUAL, 'lexeme' => "<=", 'literal' => null, 'line' => $line]
    ]);
    $line           = 10;
    $expectedTokens = array_merge($expectedTokens, [
        ['type' => TokenType::STRING, 'lexeme' => '"Lorem Ipsum"', 'literal' => 'Lorem Ipsum', 'line' => $line],
        ['type' => TokenType::NUMBER, 'lexeme' => '13.37', 'literal' => 13.37, 'line' => $line]
    ]);
    $line           = 19;
    $expectedTokens = array_merge($expectedTokens, [
        ['type' => TokenType::IDENTIFIER, 'lexeme' => '_identifier_', 'literal' => null, 'line' => $line],
        ['type' => TokenType::AND, 'lexeme' => 'and', 'literal' => null, 'line' => $line],
        ['type' => TokenType::CLS, 'lexeme' => 'class', 'literal' => null, 'line' => $line],
        ['type' => TokenType::ELSE, 'lexeme' => 'else', 'literal' => null, 'line' => $line],
        ['type' => TokenType::FALSE, 'lexeme' => 'false', 'literal' => null, 'line' => $line],
        ['type' => TokenType::FUN, 'lexeme' => 'fun', 'literal' => null, 'line' => $line],
        ['type' => TokenType::FOR, 'lexeme' => 'for', 'literal' => null, 'line' => $line],
        ['type' => TokenType::IF, 'lexeme' => 'if', 'literal' => null, 'line' => $line],
        ['type' => TokenType::NIL, 'lexeme' => 'nil', 'literal' => null, 'line' => $line],
        ['type' => TokenType::OR, 'lexeme' => 'or', 'literal' => null, 'line' => $line],
        ['type' => TokenType::PRINT, 'lexeme' => 'print', 'literal' => null, 'line' => $line],
        ['type' => TokenType::RETURN, 'lexeme' => 'return', 'literal' => null, 'line' => $line],
        ['type' => TokenType::SUPER, 'lexeme' => 'super', 'literal' => null, 'line' => $line],
        ['type' => TokenType::THIS, 'lexeme' => 'this', 'literal' => null, 'line' => $line],
        ['type' => TokenType::TRUE, 'lexeme' => 'true', 'literal' => null, 'line' => $line],
        ['type' => TokenType::VAR, 'lexeme' => 'var', 'literal' => null, 'line' => $line],
        ['type' => TokenType::WHILE, 'lexeme' => 'while', 'literal' => null, 'line' => $line]
    ]);
    $expectedTokens = array_merge($expectedTokens, [
        ['type' => TokenType::EOF, 'lexeme' => '', 'literal' => null, 'line' => 20],
    ]);

    foreach ($expectedTokens as $expectedToken) {
        expect(array_shift($tokens))
            ->toHaveType($expectedToken['type'])
            ->toHaveLexeme($expectedToken['lexeme'])
            ->toHaveLiteral($expectedToken['literal'])
            ->toHaveLine($expectedToken['line']);
    }

    expect($tokens)->toBeEmpty();
});

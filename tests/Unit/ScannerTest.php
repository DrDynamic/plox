<?php

use src\Scaner\Scanner;
use src\Scaner\TokenType;

// TODO: test error reporting
expect()->extend('toHaveType', function (TokenType $type) {
    \PHPUnit\Framework\assertEquals($type, $this->value->type);
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
    _identifier_ and class else false function for if nil or return super this true var while
';


    /** @var Scanner $scanner */
    $scanner = dependency(\src\Scaner\Scanner::class);

    $tokens = $scanner->scanTokens($source);

    $expectedTokens = [
        ['type' => TokenType::LINE_BREAK, 'lexeme' => '', 'literal' => null, 'line' => 1],
        ['type' => TokenType::LINE_BREAK, 'lexeme' => '', 'literal' => null, 'line' => 2],
    ];

    $line           = 3;
    $expectedTokens = array_merge($expectedTokens, [
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
    ]);

    $expectedTokens = array_merge($expectedTokens, [
        ['type' => TokenType::LINE_BREAK, 'lexeme' => '', 'literal' => null, 'line' => 3],
        ['type' => TokenType::LINE_BREAK, 'lexeme' => '', 'literal' => null, 'line' => 4],
        ['type' => TokenType::LINE_BREAK, 'lexeme' => '', 'literal' => null, 'line' => 5],
    ]);

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

    $expectedTokens = array_merge($expectedTokens, [
        ['type' => TokenType::LINE_BREAK, 'lexeme' => '', 'literal' => null, 'line' => 6],
        ['type' => TokenType::LINE_BREAK, 'lexeme' => '', 'literal' => null, 'line' => 7],
        ['type' => TokenType::LINE_BREAK, 'lexeme' => '', 'literal' => null, 'line' => 8],
        ['type' => TokenType::LINE_BREAK, 'lexeme' => '', 'literal' => null, 'line' => 9],
    ]);

    $line           = 10;
    $expectedTokens = array_merge($expectedTokens, [
        ['type' => TokenType::STRING, 'lexeme' => '"Lorem Ipsum"', 'literal' => 'Lorem Ipsum', 'line' => $line],
        ['type' => TokenType::NUMBER, 'lexeme' => '13.37', 'literal' => 13.37, 'line' => $line]
    ]);

    $expectedTokens = array_merge($expectedTokens, [
        ['type' => TokenType::LINE_BREAK, 'lexeme' => '', 'literal' => null, 'line' => 10],
        ['type' => TokenType::LINE_BREAK, 'lexeme' => '', 'literal' => null, 'line' => 11],
        ['type' => TokenType::LINE_BREAK, 'lexeme' => '', 'literal' => null, 'line' => 12],
        ['type' => TokenType::LINE_BREAK, 'lexeme' => '', 'literal' => null, 'line' => 13],
        ['type' => TokenType::LINE_BREAK, 'lexeme' => '', 'literal' => null, 'line' => 14],
        ['type' => TokenType::LINE_BREAK, 'lexeme' => '', 'literal' => null, 'line' => 15],
        ['type' => TokenType::LINE_BREAK, 'lexeme' => '', 'literal' => null, 'line' => 16],
        ['type' => TokenType::LINE_BREAK, 'lexeme' => '', 'literal' => null, 'line' => 17],
        ['type' => TokenType::LINE_BREAK, 'lexeme' => '', 'literal' => null, 'line' => 18],
    ]);

    $line           = 19;
    $expectedTokens = array_merge($expectedTokens, [
        ['type' => TokenType::IDENTIFIER, 'lexeme' => '_identifier_', 'literal' => null, 'line' => $line],
        ['type' => TokenType::AND, 'lexeme' => 'and', 'literal' => null, 'line' => $line],
        ['type' => TokenType::CLS, 'lexeme' => 'class', 'literal' => null, 'line' => $line],
        ['type' => TokenType::ELSE, 'lexeme' => 'else', 'literal' => null, 'line' => $line],
        ['type' => TokenType::FALSE, 'lexeme' => 'false', 'literal' => null, 'line' => $line],
        ['type' => TokenType::FUNCTION, 'lexeme' => 'function', 'literal' => null, 'line' => $line],
        ['type' => TokenType::FOR, 'lexeme' => 'for', 'literal' => null, 'line' => $line],
        ['type' => TokenType::IF, 'lexeme' => 'if', 'literal' => null, 'line' => $line],
        ['type' => TokenType::NIL, 'lexeme' => 'nil', 'literal' => null, 'line' => $line],
        ['type' => TokenType::OR, 'lexeme' => 'or', 'literal' => null, 'line' => $line],
        ['type' => TokenType::RETURN, 'lexeme' => 'return', 'literal' => null, 'line' => $line],
        ['type' => TokenType::SUPER, 'lexeme' => 'super', 'literal' => null, 'line' => $line],
        ['type' => TokenType::THIS, 'lexeme' => 'this', 'literal' => null, 'line' => $line],
        ['type' => TokenType::TRUE, 'lexeme' => 'true', 'literal' => null, 'line' => $line],
        ['type' => TokenType::VAR, 'lexeme' => 'var', 'literal' => null, 'line' => $line],
        ['type' => TokenType::WHILE, 'lexeme' => 'while', 'literal' => null, 'line' => $line]
    ]);
    $expectedTokens = array_merge($expectedTokens, [
        ['type' => TokenType::LINE_BREAK, 'lexeme' => '', 'literal' => null, 'line' => 19],
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

<?php

use Lox\Scanner;
use Lox\TokenType;

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
    
        /********
 *\/
 /* 
//
 */
    
    // Keywords.
    _identifier_ and class else false fun for if nil or print return super this true var while
';


    /** @var Scanner $scanner */
    $scanner = dependency(\Lox\Scanner::class);

    $tokens = $scanner->scanTokens($source);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::LEFT_PAREN)
        ->toHaveLexeme("(")
        ->toHaveLiteral(null)
        ->toHaveLine(3);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::RIGHT_PAREN)
        ->toHaveLexeme(")")
        ->toHaveLiteral(null)
        ->toHaveLine(3);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::LEFT_BRACE)
        ->toHaveLexeme("{")
        ->toHaveLiteral(null)
        ->toHaveLine(3);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::RIGHT_BRACE)
        ->toHaveLexeme("}")
        ->toHaveLiteral(null)
        ->toHaveLine(3);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::COMMA)
        ->toHaveLexeme(",")
        ->toHaveLiteral(null)
        ->toHaveLine(3);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::DOT)
        ->toHaveLexeme(".")
        ->toHaveLiteral(null)
        ->toHaveLine(3);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::MINUS)
        ->toHaveLexeme("-")
        ->toHaveLiteral(null)
        ->toHaveLine(3);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::PLUS)
        ->toHaveLexeme("+")
        ->toHaveLiteral(null)
        ->toHaveLine(3);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::SEMICOLON)
        ->toHaveLexeme(";")
        ->toHaveLiteral(null)
        ->toHaveLine(3);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::STAR)
        ->toHaveLexeme("*")
        ->toHaveLiteral(null)
        ->toHaveLine(3);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::SLASH)
        ->toHaveLexeme("/")
        ->toHaveLiteral(null)
        ->toHaveLine(3);


    expect(array_shift($tokens))
        ->toHaveType(TokenType::BANG)
        ->toHaveLexeme("!")
        ->toHaveLiteral(null)
        ->toHaveLine(6);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::BANG_EQUAL)
        ->toHaveLexeme("!=")
        ->toHaveLiteral(null)
        ->toHaveLine(6);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::EQUAL)
        ->toHaveLexeme("=")
        ->toHaveLiteral(null)
        ->toHaveLine(6);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::EQUAL_EQUAL)
        ->toHaveLexeme("==")
        ->toHaveLiteral(null)
        ->toHaveLine(6);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::GREATER)
        ->toHaveLexeme(">")
        ->toHaveLiteral(null)
        ->toHaveLine(6);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::GREATER_EQUAL)
        ->toHaveLexeme(">=")
        ->toHaveLiteral(null)
        ->toHaveLine(6);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::LESS)
        ->toHaveLexeme("<")
        ->toHaveLiteral(null)
        ->toHaveLine(6);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::LESS_EQUAL)
        ->toHaveLexeme("<=")
        ->toHaveLiteral(null)
        ->toHaveLine(6);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::STRING)
        ->toHaveLexeme('"Lorem Ipsum"')
        ->toHaveLiteral('Lorem Ipsum')
        ->toHaveLine(10);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::NUMBER)
        ->toHaveLexeme('13.37')
        ->toHaveLiteral(13.37)
        ->toHaveLine(10);


    expect(array_shift($tokens))
        ->toHaveType(TokenType::IDENTIFIER)
        ->toHaveLexeme('_identifier_')
        ->toHaveLiteral(null)
        ->toHaveLine(19);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::AND)
        ->toHaveLexeme('and')
        ->toHaveLiteral(null)
        ->toHaveLine(19);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::CLS)
        ->toHaveLexeme('class')
        ->toHaveLiteral(null)
        ->toHaveLine(19);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::ELSE)
        ->toHaveLexeme('else')
        ->toHaveLiteral(null)
        ->toHaveLine(19);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::FALSE)
        ->toHaveLexeme('false')
        ->toHaveLiteral(null)
        ->toHaveLine(19);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::FUN)
        ->toHaveLexeme('fun')
        ->toHaveLiteral(null)
        ->toHaveLine(19);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::FOR)
        ->toHaveLexeme('for')
        ->toHaveLiteral(null)
        ->toHaveLine(19);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::IF)
        ->toHaveLexeme('if')
        ->toHaveLiteral(null)
        ->toHaveLine(19);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::NIL)
        ->toHaveLexeme('nil')
        ->toHaveLiteral(null)
        ->toHaveLine(19);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::OR)
        ->toHaveLexeme('or')
        ->toHaveLiteral(null)
        ->toHaveLine(19);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::PRINT)
        ->toHaveLexeme('print')
        ->toHaveLiteral(null)
        ->toHaveLine(19);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::RETURN)
        ->toHaveLexeme('return')
        ->toHaveLiteral(null)
        ->toHaveLine(19);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::SUPER)
        ->toHaveLexeme('super')
        ->toHaveLiteral(null)
        ->toHaveLine(19);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::THIS)
        ->toHaveLexeme('this')
        ->toHaveLiteral(null)
        ->toHaveLine(19);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::TRUE)
        ->toHaveLexeme('true')
        ->toHaveLiteral(null)
        ->toHaveLine(19);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::VAR)
        ->toHaveLexeme('var')
        ->toHaveLiteral(null)
        ->toHaveLine(19);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::WHILE)
        ->toHaveLexeme('while')
        ->toHaveLiteral(null)
        ->toHaveLine(19);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::EOF)
        ->toHaveLexeme('')
        ->toHaveLiteral(null)
        ->toHaveLine(20);

    expect($tokens)->toBeEmpty();
});

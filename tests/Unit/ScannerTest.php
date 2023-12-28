<?php

use Lox\Scanner;
use Lox\Token;
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

function testToken(Token $token, TokenType $type, $lexeme, $literal, $line)
{
    dd($this);
}

it('parses sourcecode to tokens', function () {
    $source = '// Single-character tokens.
    (){},.-+;/*
    
    // One or two character tokens.
    ! != = == > >= < <=
    
    // Literals.
    //case IDENTIFIER;
    "Lorem Ipsum"13.37
    
    // Keywords.
    //case AND;
    //case CLS;
    //case ELSE;
    //case FALSE;
    //case FUN;
    //case FOR;
    //case IF;
    //case NIL;
    //case OR;
    //case PRINT;
    //case RETURN;
    //case SUPER;
    //case THIS;
    //case TRUE;
    //case VAR;
    //case WHILE;';


    /** @var Scanner $scanner */
    $scanner = dependency(\Lox\Scanner::class);

    $tokens = $scanner->scanTokens($source);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::LEFT_PAREN)
        ->toHaveLexeme("(")
        ->toHaveLiteral(null)
        ->toHaveLine(2);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::RIGHT_PAREN)
        ->toHaveLexeme(")")
        ->toHaveLiteral(null)
        ->toHaveLine(2);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::LEFT_BRACE)
        ->toHaveLexeme("{")
        ->toHaveLiteral(null)
        ->toHaveLine(2);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::RIGHT_BRACE)
        ->toHaveLexeme("}")
        ->toHaveLiteral(null)
        ->toHaveLine(2);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::COMMA)
        ->toHaveLexeme(",")
        ->toHaveLiteral(null)
        ->toHaveLine(2);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::DOT)
        ->toHaveLexeme(".")
        ->toHaveLiteral(null)
        ->toHaveLine(2);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::MINUS)
        ->toHaveLexeme("-")
        ->toHaveLiteral(null)
        ->toHaveLine(2);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::PLUS)
        ->toHaveLexeme("+")
        ->toHaveLiteral(null)
        ->toHaveLine(2);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::SEMICOLON)
        ->toHaveLexeme(";")
        ->toHaveLiteral(null)
        ->toHaveLine(2);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::SLASH)
        ->toHaveLexeme("/")
        ->toHaveLiteral(null)
        ->toHaveLine(2);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::STAR)
        ->toHaveLexeme("*")
        ->toHaveLiteral(null)
        ->toHaveLine(2);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::BANG)
        ->toHaveLexeme("!")
        ->toHaveLiteral(null)
        ->toHaveLine(5);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::BANG_EQUAL)
        ->toHaveLexeme("!=")
        ->toHaveLiteral(null)
        ->toHaveLine(5);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::EQUAL)
        ->toHaveLexeme("=")
        ->toHaveLiteral(null)
        ->toHaveLine(5);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::EQUAL_EQUAL)
        ->toHaveLexeme("==")
        ->toHaveLiteral(null)
        ->toHaveLine(5);

    // ! != = == > >= < <=
    expect(array_shift($tokens))
        ->toHaveType(TokenType::GREATER)
        ->toHaveLexeme(">")
        ->toHaveLiteral(null)
        ->toHaveLine(5);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::GREATER_EQUAL)
        ->toHaveLexeme(">=")
        ->toHaveLiteral(null)
        ->toHaveLine(5);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::LESS)
        ->toHaveLexeme("<")
        ->toHaveLiteral(null)
        ->toHaveLine(5);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::LESS_EQUAL)
        ->toHaveLexeme("<=")
        ->toHaveLiteral(null)
        ->toHaveLine(5);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::STRING)
        ->toHaveLexeme('"Lorem Ipsum"')
        ->toHaveLiteral('Lorem Ipsum')
        ->toHaveLine(9);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::NUMBER)
        ->toHaveLexeme('13.37')
        ->toHaveLiteral(13.37)
        ->toHaveLine(9);

    expect(array_shift($tokens))
        ->toHaveType(TokenType::EOF)
        ->toHaveLexeme('')
        ->toHaveLiteral(null)
        ->toHaveLine(27);

    expect($tokens)->toBeEmpty();
});

<?php

// TODO: test error reporting
function createToken(\Lox\Scan\TokenType $type, $lexeme, $literal)
{
    return new \Lox\Scan\Token($type, $lexeme, $literal, 0);
}

expect()->extend('toHaveProperty', function ($property, $value) {
    \PHPUnit\Framework\assertEquals($value, $this->value->{$property});
});

expect()->extend('toHaveOperator', function (\Lox\Scan\TokenType $type) {
    \PHPUnit\Framework\assertEquals($type, $this->value->operator->tokenType);
});

it('parses tokens to an expression', function () {

    $tokens = [
        createToken(\Lox\Scan\TokenType::NUMBER, "2", 2),
        createToken(\Lox\Scan\TokenType::PLUS, "+", null),
        createToken(\Lox\Scan\TokenType::NUMBER, "4", 4),
        createToken(\Lox\Scan\TokenType::STAR, "*", null),
        createToken(\Lox\Scan\TokenType::NUMBER, "4", 4),
        createToken(\Lox\Scan\TokenType::PLUS, "+", null),
        createToken(\Lox\Scan\TokenType::NUMBER, "2", 2),
        createToken(\Lox\Scan\TokenType::EOF, "", null),
    ];

    /** @var \Lox\Parse\Parser $parser */
    $parser = dependency(\Lox\Parse\Parser::class);
    $ast    = $parser->parse($tokens);

    /*
     * ast:bin [
     *  left: bin [
     *    left: 2
     *    op: +
     *    right: bin [
     *      left: 4
     *      op: *
     *      right: 4
     *    ]
     *  ]
     *  op: +
     *  right: 2
     * ]
     */
    expect($ast)->toBeInstanceOf(\Lox\AST\Expressions\Binary::class)
        ->toHaveOperator(\Lox\Scan\TokenType::PLUS);

    expect($ast->left)->toBeInstanceOf(\Lox\AST\Expressions\Binary::class)
        ->toHaveOperator(\Lox\Scan\TokenType::PLUS);
    expect($ast->right)->toBeInstanceOf(\Lox\AST\Expressions\Literal::class)
        ->toHaveProperty('value', 2);

    expect($ast->left->left)->toBeInstanceOf(\Lox\AST\Expressions\Literal::class)
        ->toHaveProperty('value', 2);

    expect($ast->left->right)->toBeInstanceOf(\Lox\AST\Expressions\Binary::class)
        ->toHaveOperator(\Lox\Scan\TokenType::STAR);

    expect($ast->left->right->left)->toBeInstanceOf(\Lox\AST\Expressions\Literal::class)
        ->toHaveProperty('value', 4);

    expect($ast->left->right->right)->toBeInstanceOf(\Lox\AST\Expressions\Literal::class)
        ->toHaveProperty('value', 4);
});
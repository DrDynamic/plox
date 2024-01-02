<?php

// TODO: test error reporting
function createToken(\Lox\Scan\TokenType $type, $lexeme, $literal)
{
    return new \Lox\Scan\Token($type, $lexeme, $literal, 0);
}

expect()->extend('toHaveValue', function ($value) {
    \PHPUnit\Framework\assertEquals($value, $this->value->value->value);
});

expect()->extend('toHaveOperator', function (\Lox\Scan\TokenType $type) {
    \PHPUnit\Framework\assertEquals($type, $this->value->operator->type);
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
    expect($ast[0]->expression)->toBeInstanceOf(\Lox\AST\Expressions\Binary::class)
        ->toHaveOperator(\Lox\Scan\TokenType::PLUS);

    expect($ast[0]->expression->left)->toBeInstanceOf(\Lox\AST\Expressions\Binary::class)
        ->toHaveOperator(\Lox\Scan\TokenType::PLUS);
    expect($ast[0]->expression->right)->toBeInstanceOf(\Lox\AST\Expressions\Literal::class)
        ->toHaveValue(2.0);

    expect($ast[0]->expression->left->left)->toBeInstanceOf(\Lox\AST\Expressions\Literal::class)
        ->toHaveValue(2.0);

    expect($ast[0]->expression->left->right)->toBeInstanceOf(\Lox\AST\Expressions\Binary::class)
        ->toHaveOperator(\Lox\Scan\TokenType::STAR);

    expect($ast[0]->expression->left->right->left)->toBeInstanceOf(\Lox\AST\Expressions\Literal::class)
        ->toHaveValue(4.0);

    expect($ast[0]->expression->left->right->right)->toBeInstanceOf(\Lox\AST\Expressions\Literal::class)
        ->toHaveValue(4.0);
});

it('parses tokens with groupings', function () {

    $tokens = [
        createToken(\Lox\Scan\TokenType::LEFT_PAREN, "(", null),
        createToken(\Lox\Scan\TokenType::NUMBER, "2", 2),
        createToken(\Lox\Scan\TokenType::PLUS, "+", null),
        createToken(\Lox\Scan\TokenType::NUMBER, "4", 4),
        createToken(\Lox\Scan\TokenType::RIGHT_PAREN, ")", null),
        createToken(\Lox\Scan\TokenType::STAR, "*", null),
        createToken(\Lox\Scan\TokenType::NUMBER, "4", 4),
        createToken(\Lox\Scan\TokenType::EOF, "", null),
    ];

    /** @var \Lox\Parse\Parser $parser */
    $parser = dependency(\Lox\Parse\Parser::class);
    $ast    = $parser->parse($tokens);
//    dd($ast);
    /*
     * ast:bin [
     *  left: grp [
     *    expression: bin [
     *      left: 2
     *      op: +
     *      right: 4
     *    ]
     *    op: *
     *    right: 4
     *  ]
     */
    expect($ast[0]->expression)->toBeInstanceOf(\Lox\AST\Expressions\Binary::class)
        ->toHaveOperator(\Lox\Scan\TokenType::STAR);

    expect($ast[0]->expression->left)->toBeInstanceOf(\Lox\AST\Expressions\Grouping::class);
    expect($ast[0]->expression->left->expression)->toBeInstanceOf(\Lox\AST\Expressions\Binary::class)
        ->toHaveOperator(\Lox\Scan\TokenType::PLUS);

    expect($ast[0]->expression->left->expression->left)->toBeInstanceOf(\Lox\AST\Expressions\Literal::class)
        ->toHaveValue(2.0);

    expect($ast[0]->expression->left->expression->right)->toBeInstanceOf(\Lox\AST\Expressions\Literal::class)
        ->toHaveValue(4.0);

    expect($ast[0]->expression->right)->toBeInstanceOf(\Lox\AST\Expressions\Literal::class)
        ->toHaveValue(4.0);
});

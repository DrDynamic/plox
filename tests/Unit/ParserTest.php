<?php

// TODO: test error reporting
function createToken(\Lox\Scaner\TokenType $type, $lexeme, $literal)
{
    return new \Lox\Scaner\Token($type, $lexeme, $literal, 0);
}

expect()->extend('toHaveValue', function ($value) {
    \PHPUnit\Framework\assertEquals($value, $this->value->value->value);
});

expect()->extend('toHaveOperator', function (\Lox\Scaner\TokenType $type) {
    \PHPUnit\Framework\assertEquals($type, $this->value->operator->type);
});

it('parses tokens to an expression', function () {

    $tokens = [
        createToken(\Lox\Scaner\TokenType::NUMBER, "2", 2),
        createToken(\Lox\Scaner\TokenType::PLUS, "+", null),
        createToken(\Lox\Scaner\TokenType::NUMBER, "4", 4),
        createToken(\Lox\Scaner\TokenType::STAR, "*", null),
        createToken(\Lox\Scaner\TokenType::NUMBER, "4", 4),
        createToken(\Lox\Scaner\TokenType::PLUS, "+", null),
        createToken(\Lox\Scaner\TokenType::NUMBER, "2", 2),
        createToken(\Lox\Scaner\TokenType::EOF, "", null),
    ];

    /** @var \Lox\Parser\Parser $parser */
    $parser = dependency(\Lox\Parser\Parser::class);
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
        ->toHaveOperator(\Lox\Scaner\TokenType::PLUS);

    expect($ast[0]->expression->left)->toBeInstanceOf(\Lox\AST\Expressions\Binary::class)
        ->toHaveOperator(\Lox\Scaner\TokenType::PLUS);
    expect($ast[0]->expression->right)->toBeInstanceOf(\Lox\AST\Expressions\Literal::class)
        ->toHaveValue(2.0);

    expect($ast[0]->expression->left->left)->toBeInstanceOf(\Lox\AST\Expressions\Literal::class)
        ->toHaveValue(2.0);

    expect($ast[0]->expression->left->right)->toBeInstanceOf(\Lox\AST\Expressions\Binary::class)
        ->toHaveOperator(\Lox\Scaner\TokenType::STAR);

    expect($ast[0]->expression->left->right->left)->toBeInstanceOf(\Lox\AST\Expressions\Literal::class)
        ->toHaveValue(4.0);

    expect($ast[0]->expression->left->right->right)->toBeInstanceOf(\Lox\AST\Expressions\Literal::class)
        ->toHaveValue(4.0);
});

it('parses tokens with groupings', function () {

    $tokens = [
        createToken(\Lox\Scaner\TokenType::LEFT_PAREN, "(", null),
        createToken(\Lox\Scaner\TokenType::NUMBER, "2", 2),
        createToken(\Lox\Scaner\TokenType::PLUS, "+", null),
        createToken(\Lox\Scaner\TokenType::NUMBER, "4", 4),
        createToken(\Lox\Scaner\TokenType::RIGHT_PAREN, ")", null),
        createToken(\Lox\Scaner\TokenType::STAR, "*", null),
        createToken(\Lox\Scaner\TokenType::NUMBER, "4", 4),
        createToken(\Lox\Scaner\TokenType::EOF, "", null),
    ];

    /** @var \Lox\Parser\Parser $parser */
    $parser = dependency(\Lox\Parser\Parser::class);
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
        ->toHaveOperator(\Lox\Scaner\TokenType::STAR);

    expect($ast[0]->expression->left)->toBeInstanceOf(\Lox\AST\Expressions\Grouping::class);
    expect($ast[0]->expression->left->expression)->toBeInstanceOf(\Lox\AST\Expressions\Binary::class)
        ->toHaveOperator(\Lox\Scaner\TokenType::PLUS);

    expect($ast[0]->expression->left->expression->left)->toBeInstanceOf(\Lox\AST\Expressions\Literal::class)
        ->toHaveValue(2.0);

    expect($ast[0]->expression->left->expression->right)->toBeInstanceOf(\Lox\AST\Expressions\Literal::class)
        ->toHaveValue(4.0);

    expect($ast[0]->expression->right)->toBeInstanceOf(\Lox\AST\Expressions\Literal::class)
        ->toHaveValue(4.0);
});

<?php

// TODO: test error reporting
function createToken(\src\Scaner\TokenType $type, $lexeme, $literal)
{
    return new \src\Scaner\Token($type, $lexeme, $literal, 0);
}

expect()->extend('toHaveValue', function ($value) {
    \PHPUnit\Framework\assertEquals($value, $this->value->value->value);
});

expect()->extend('toHaveOperator', function (\src\Scaner\TokenType $type) {
    \PHPUnit\Framework\assertEquals($type, $this->value->operator->type);
});

it('parses tokens to an expression', function () {

    $tokens = [
        createToken(\src\Scaner\TokenType::NUMBER, "2", 2),
        createToken(\src\Scaner\TokenType::PLUS, "+", null),
        createToken(\src\Scaner\TokenType::NUMBER, "4", 4),
        createToken(\src\Scaner\TokenType::STAR, "*", null),
        createToken(\src\Scaner\TokenType::NUMBER, "4", 4),
        createToken(\src\Scaner\TokenType::PLUS, "+", null),
        createToken(\src\Scaner\TokenType::NUMBER, "2", 2),
        createToken(\src\Scaner\TokenType::EOF, "", null),
    ];

    /** @var \src\Parser\Parser $parser */
    $parser = dependency(\src\Parser\Parser::class);
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
    expect($ast[0]->expression)->toBeInstanceOf(\src\AST\Expressions\Binary::class)
        ->toHaveOperator(\src\Scaner\TokenType::PLUS);

    expect($ast[0]->expression->left)->toBeInstanceOf(\src\AST\Expressions\Binary::class)
        ->toHaveOperator(\src\Scaner\TokenType::PLUS);
    expect($ast[0]->expression->right)->toBeInstanceOf(\src\AST\Expressions\Literal::class)
        ->toHaveValue(2.0);

    expect($ast[0]->expression->left->left)->toBeInstanceOf(\src\AST\Expressions\Literal::class)
        ->toHaveValue(2.0);

    expect($ast[0]->expression->left->right)->toBeInstanceOf(\src\AST\Expressions\Binary::class)
        ->toHaveOperator(\src\Scaner\TokenType::STAR);

    expect($ast[0]->expression->left->right->left)->toBeInstanceOf(\src\AST\Expressions\Literal::class)
        ->toHaveValue(4.0);

    expect($ast[0]->expression->left->right->right)->toBeInstanceOf(\src\AST\Expressions\Literal::class)
        ->toHaveValue(4.0);
});

it('parses tokens with groupings', function () {

    $tokens = [
        createToken(\src\Scaner\TokenType::LEFT_PAREN, "(", null),
        createToken(\src\Scaner\TokenType::NUMBER, "2", 2),
        createToken(\src\Scaner\TokenType::PLUS, "+", null),
        createToken(\src\Scaner\TokenType::NUMBER, "4", 4),
        createToken(\src\Scaner\TokenType::RIGHT_PAREN, ")", null),
        createToken(\src\Scaner\TokenType::STAR, "*", null),
        createToken(\src\Scaner\TokenType::NUMBER, "4", 4),
        createToken(\src\Scaner\TokenType::EOF, "", null),
    ];

    /** @var \src\Parser\Parser $parser */
    $parser = dependency(\src\Parser\Parser::class);
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
    expect($ast[0]->expression)->toBeInstanceOf(\src\AST\Expressions\Binary::class)
        ->toHaveOperator(\src\Scaner\TokenType::STAR);

    expect($ast[0]->expression->left)->toBeInstanceOf(\src\AST\Expressions\Grouping::class);
    expect($ast[0]->expression->left->expression)->toBeInstanceOf(\src\AST\Expressions\Binary::class)
        ->toHaveOperator(\src\Scaner\TokenType::PLUS);

    expect($ast[0]->expression->left->expression->left)->toBeInstanceOf(\src\AST\Expressions\Literal::class)
        ->toHaveValue(2.0);

    expect($ast[0]->expression->left->expression->right)->toBeInstanceOf(\src\AST\Expressions\Literal::class)
        ->toHaveValue(4.0);

    expect($ast[0]->expression->right)->toBeInstanceOf(\src\AST\Expressions\Literal::class)
        ->toHaveValue(4.0);
});

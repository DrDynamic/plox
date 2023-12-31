<?php

namespace Lox\Parse;

use App\Attributes\Instance;
use App\Services\ErrorReporter;
use Lox\AST\Expressions\Binary;
use Lox\AST\Expressions\Expression;
use Lox\AST\Expressions\Grouping;
use Lox\AST\Expressions\Literal;
use Lox\AST\Expressions\Ternary;
use Lox\AST\Expressions\Unary;
use Lox\AST\Statements\ExpressionStmt;
use Lox\AST\Statements\PrintStmt;
use Lox\AST\Statements\Statement;
use Lox\Runtime\Types\BooleanType;
use Lox\Runtime\Types\NilType;
use Lox\Runtime\Types\NumberType;
use Lox\Runtime\Types\StringType;
use Lox\Scan\Token;
use Lox\Scan\TokenType;

#[Instance]
class Parser
{

    /** @var array<Token> */
    private array $tokens;
    private int $current = 0;

    public function __construct(
        private readonly ErrorReporter $errorReporter
    )
    {

    }

    /**
     * @param array $tokens
     * @return array<Statement>
     */
    public function parse(array $tokens): array
    {
        $this->tokens  = $tokens;
        $this->current = 0;

        $statements = [];

        while (!$this->isAtEnd()) {
            $statements[] = $this->statement();
        }

        return $statements;
    }

    private function statement(): Statement
    {
        if ($this->match(TokenType::PRINT)) {
            return $this->printStmt();
        }
        return $this->expressionStmt();
    }

    private function printStmt(): Statement
    {
        $value = $this->expression();
//        $this->consume(TokenType::SEMICOLON, "Expected ; after value.");
        return new PrintStmt($value);
    }

    private function expressionStmt(): Statement
    {
        $value = $this->expression();
//        $this->consume(TokenType::SEMICOLON, "Expected ; after value.");
        return new ExpressionStmt($value);
    }

    private function expression(): Expression
    {
        return $this->ternary();
    }

    private function ternary(): Expression
    {
        $expression = $this->comma();

        $question = $this->peek();
        if ($this->match(TokenType::QUESTION_MARK)) {
            $then = $this->comma();

            if ($this->match(TokenType::COLON)) {
                $colon = $this->previous();
                $else  = $this->comma();

                return new Ternary($expression, $question, $then, $colon, $else);
            }
            throw $this->error($question, "Ternary operator needs colon ':'");
        }
        return $expression;
    }

    private function comma(): Expression
    {
        $expression = $this->equality();
        while ($this->match(TokenType::COMMA)) {
            $operator   = $this->previous();
            $right      = $this->equality();
            $expression = new Binary($expression, $operator, $right);
        }
        return $expression;
    }

    private function equality(): Expression
    {
        $expression = $this->comparison();
        while ($this->match(TokenType::BANG_EQUAL, TokenType::EQUAL_EQUAL)) {
            $operator   = $this->previous();
            $right      = $this->comparison();
            $expression = new Binary($expression, $operator, $right);
        }
        return $expression;
    }

    private function comparison(): Expression
    {
        $expression = $this->term();

        while ($this->match(TokenType::GREATER, TokenType::GREATER_EQUAL, TokenType::LESS, TokenType::LESS_EQUAL)) {
            $operator   = $this->previous();
            $right      = $this->term();
            $expression = new Binary($expression, $operator, $right);
        }
        return $expression;
    }

    private function term(): Expression
    {
        $expression = $this->factor();
        while ($this->match(TokenType::MINUS, TokenType::PLUS)) {
            $operator   = $this->previous();
            $right      = $this->factor();
            $expression = new Binary($expression, $operator, $right);
        }
        return $expression;
    }

    private function factor(): Expression
    {
        $expression = $this->unary();
        while ($this->match(TokenType::SLASH, TokenType::STAR)) {
            $operator   = $this->previous();
            $rigth      = $this->unary();
            $expression = new Binary($expression, $operator, $rigth);
        }
        return $expression;
    }

    private function unary(): Expression
    {
        if ($this->match(TokenType::BANG, TokenType::MINUS)) {
            $operator = $this->previous();
            $right    = $this->unary();
            return new Unary($operator, $right);
        }

        return $this->primary();
    }

    private function primary(): Expression
    {
        switch (true) {
            case $this->match(TokenType::TRUE):
                return new Literal(new BooleanType(true));
            case $this->match(TokenType::FALSE):
                return new Literal(new BooleanType(false));
            case $this->match(TokenType::NIL):
                return new Literal(new NilType());
            case $this->match(TokenType::NUMBER):
                return new Literal(new NumberType($this->previous()->literal));
            case $this->match(TokenType::STRING):
                return new Literal(new StringType($this->previous()->literal));
            case $this->match(TokenType::LEFT_PAREN):
                $expression = $this->expression();
                $this->consume(TokenType::RIGHT_PAREN, "Expected ')' after expression.");
                return new Grouping($expression);
            default:
                throw $this->error($this->peek(), "Expect expression.");
        }
    }

    private function isAtEnd()
    {
        return $this->peek()->type == TokenType::EOF;
    }

    private function match(TokenType ...$types): bool
    {
        if ($this->isAtEnd()) return false;

        $token = $this->peek();
        if (in_array($token->type, $types)) {
            $this->advance();
            return true;
        }
        return false;
    }

    private function check(TokenType $type)
    {
        if ($this->isAtEnd()) return false;
        return $this->peek()->type == $type;
    }

    private function advance(): Token
    {
        if (!$this->isAtEnd()) $this->current++;
        return $this->previous();
    }

    private function peek()
    {
        return $this->tokens[$this->current];
    }

    private function previous(): Token
    {
        return $this->tokens[$this->current - 1];
    }

    private function consume(TokenType $tokenType, string $message): Token
    {
        if ($this->check($tokenType)) return $this->advance();
        throw $this->error($this->peek(), $message);
    }

    private function error(Token $token, string $message): \Throwable
    {
        $this->errorReporter->errorAt($token, $message);
        return new ParseError();
    }

    private function synchonize()
    {
        $this->advance();

        while (!$this->isAtEnd()) {
            if ($this->previous()->type == TokenType::EOF) return;

            switch ($this->peek()->type) {
                case TokenType::CLS:
                case TokenType::FUN:
                case TokenType::VAR:
                case TokenType::FOR:
                case TokenType::IF:
                case TokenType::WHILE:
                case TokenType::PRINT:
                case TokenType::RETURN:
                    return;
            }
        }
        $this->advance();
    }
}
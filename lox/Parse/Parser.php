<?php

namespace Lox\Parse;

use App\Attributes\Instance;
use App\Services\ErrorReporter;
use Lox\AST\Expressions\Assign;
use Lox\AST\Expressions\Binary;
use Lox\AST\Expressions\Expression;
use Lox\AST\Expressions\Grouping;
use Lox\AST\Expressions\Literal;
use Lox\AST\Expressions\Logical;
use Lox\AST\Expressions\Ternary;
use Lox\AST\Expressions\Unary;
use Lox\AST\Expressions\Variable;
use Lox\AST\Statements\BlockStatement;
use Lox\AST\Statements\CompletionStatement;
use Lox\AST\Statements\ExpressionStmt;
use Lox\AST\Statements\IfStatement;
use Lox\AST\Statements\PrintStatement;
use Lox\AST\Statements\Statement;
use Lox\AST\Statements\VarStatement;
use Lox\AST\Statements\WhileStatement;
use Lox\Runtime\Values\BooleanValue;
use Lox\Runtime\Values\NilValue;
use Lox\Runtime\Values\NumberValue;
use Lox\Runtime\Values\StringValue;
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
            $statements[] = $this->declaration(new ParserContext());
        }

        return $statements;
    }

    private function declaration(ParserContext $context): Statement|null
    {
        try {
            if ($this->match(TokenType::VAR)) {
                return $this->varDeclaration($context);
            }
            return $this->statement($context);
        } catch (ParseError $error) {
            $this->synchonize();
            return null;
        }
    }

    private function varDeclaration(ParserContext $context): Statement
    {
        $name        = $this->consume(TokenType::IDENTIFIER, "Expect variable name.");
        $initializer = null;
        if ($this->match(TokenType::EQUAL)) {
            $initializer = $this->expression($context);
        }

        $this->match(TokenType::SEMICOLON);
//        $this->consume(TokenType::SEMICOLON, "Expected ; after value.");

        return new VarStatement($name, $initializer);
    }

    private function statement(ParserContext $context): Statement
    {
        switch (true) {
            case $this->match(TokenType::FOR):
                return $this->forStmt($context);
            case $this->match(TokenType::IF):
                return $this->ifStmt($context);
            case $this->match(TokenType::PRINT):
                return $this->printStmt($context);
            case $this->match(TokenType::WHILE):
                return $this->whileStmt($context);
            case $this->match(TokenType::BREAK):
            case $this->match(TokenType::CONTINUE):
                return $this->completionStmt($context);
            case $this->match(TokenType::LEFT_BRACE):
                return new BlockStatement($this->blockStmt($context));
            default:
                return $this->expressionStmt($context);
        }
    }

    private function forStmt(ParserContext $context)
    {

        $this->consume(TokenType::LEFT_PAREN, "Expect '(' after 'for'.");

        if ($this->match(TokenType::SEMICOLON)) {
            $initializer = null;
        } else if ($this->match(TokenType::VAR)) {
            $initializer = $this->varDeclaration($context);
        } else {
            $initializer = $this->expressionStmt($context);
        }

        $condition = null;
        if (!$this->check(TokenType::SEMICOLON)) {
            $condition = $this->expression($context);
        }

        $this->consume(TokenType::SEMICOLON, "Exprect ';' after loop condition.");

        $increment = null;
        if (!$this->check(TokenType::RIGHT_PAREN)) {
            $increment = $this->expression($context);
        }

        $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after for clauses.");

        $context->enterLoop($increment);

        $body = $this->statement($context);


        if ($increment != null) {
            $body = new BlockStatement([
                $body,
                new ExpressionStmt($increment)
            ]);
        }

        $context->exitLoop();

        if ($condition == null) $condition = new Literal(new BooleanValue(true));

        $body = new WhileStatement($condition, $body);

        if ($initializer != null) {
            $body = new BlockStatement([
                $initializer,
                $body
            ]);
        }
        return $body;
    }

    private function ifStmt(ParserContext $context): Statement
    {
        $this->consume(TokenType::LEFT_PAREN, "Expect '(' after 'if'.");
        $condition = $this->expression($context);
        $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after if condition.");

        $thenBranch = $this->statement($context);
        $elseBranch = null;

        if ($this->match(TokenType::ELSE)) {
            $elseBranch = $this->statement($context);
        }

        return new IfStatement($condition, $thenBranch, $elseBranch);
    }

    private function printStmt(ParserContext $context): Statement
    {
        $value = $this->expression($context);
        $this->match(TokenType::SEMICOLON);
//        $this->consume(TokenType::SEMICOLON, "Expected ; after value.");
        return new PrintStatement($value);
    }

    private function whileStmt(ParserContext $context): Statement
    {
        $this->consume(TokenType::LEFT_PAREN, "Expect '(' after 'while'.");
        $condition = $this->expression($context);
        $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after condition.");

        $context->enterLoop();

        $body = $this->statement($context);

        $context->exitLoop();

        return new WhileStatement($condition, $body);
    }

    private function completionStmt(ParserContext $context): Statement
    {
        if ($context->loopCount > 0) {
            $completion = new CompletionStatement($this->previous());
            $this->match(TokenType::SEMICOLON);

            $increment = end($context->loopIncrements);
            if ($increment != null) {
                return new BlockStatement([
                    new ExpressionStmt($increment),
                    $completion
                ]);
            }

            return $completion;
        }
        throw $this->error($this->previous(), "Expect 'break' and 'continue' to be in a loop.");
    }

    private function blockStmt(ParserContext $context)
    {
        $statements = [];
        while (!$this->check(TokenType::RIGHT_BRACE) && !$this->isAtEnd()) {
            $statements[] = $this->declaration($context);
        }
        $this->consume(TokenType::RIGHT_BRACE, "Expect '}' after block.");
        return $statements;
    }

    private function expressionStmt(ParserContext $context): Statement
    {
        $value = $this->expression($context);
        $this->match(TokenType::SEMICOLON);
//        $this->consume(TokenType::SEMICOLON, "Expected ; after value.");
        return new ExpressionStmt($value);
    }

    private function expression(ParserContext $context): Expression
    {
        return $this->ternary($context);
    }

    private function ternary(ParserContext $context): Expression
    {
        $expression = $this->assignment($context);

        $question = $this->peek();
        if ($this->match(TokenType::QUESTION_MARK)) {
            $then = $this->assignment($context);

            if ($this->match(TokenType::COLON)) {
                $colon = $this->previous();
                $else  = $this->assignment($context);

                return new Ternary($expression, $question, $then, $colon, $else);
            }
            throw $this->error($question, "Ternary operator needs colon ':'");
        }
        return $expression;
    }

    private function assignment(ParserContext $context): Expression
    {
        $expression = $this->or($context);

        if ($this->match(TokenType::EQUAL)) {
            $equals = $this->previous();
            $value  = $this->assignment($context);

            if ($expression instanceof Variable) {
                $name = $expression->name;
                return new Assign($name, $value);
            }

            $this->error($equals, "Invalid assignment target.");
        }

        return $expression;
    }

    private function or(ParserContext $context): Expression
    {
        $expression = $this->and($context);

        while ($this->match(TokenType::OR)) {
            $operator = $this->previous();
            $right    = $this->and($context);

            $expression = new Logical($expression, $operator, $right);
        }

        return $expression;
    }

    private function and(ParserContext $context): Expression
    {
        $expression = $this->comma($context);

        while ($this->match(TokenType::AND)) {
            $operator = $this->previous();
            $right    = $this->comma($context);

            $expression = new Logical($expression, $operator, $right);
        }

        return $expression;
    }

    private function comma(ParserContext $context): Expression
    {
        $expression = $this->equality($context);
        while ($this->match(TokenType::COMMA)) {
            $operator   = $this->previous();
            $right      = $this->equality($context);
            $expression = new Binary($expression, $operator, $right);
        }
        return $expression;
    }

    private function equality(ParserContext $context): Expression
    {
        $expression = $this->comparison($context);
        while ($this->match(TokenType::BANG_EQUAL, TokenType::EQUAL_EQUAL)) {
            $operator   = $this->previous();
            $right      = $this->comparison($context);
            $expression = new Binary($expression, $operator, $right);
        }
        return $expression;
    }

    private function comparison(ParserContext $context): Expression
    {
        $expression = $this->term($context);

        while ($this->match(TokenType::GREATER, TokenType::GREATER_EQUAL, TokenType::LESS, TokenType::LESS_EQUAL)) {
            $operator   = $this->previous();
            $right      = $this->term($context);
            $expression = new Binary($expression, $operator, $right);
        }
        return $expression;
    }

    private function term(ParserContext $context): Expression
    {
        $expression = $this->factor($context);
        while ($this->match(TokenType::MINUS, TokenType::PLUS)) {
            $operator   = $this->previous();
            $right      = $this->factor($context);
            $expression = new Binary($expression, $operator, $right);
        }
        return $expression;
    }

    private function factor(ParserContext $context): Expression
    {
        $expression = $this->unary($context);
        while ($this->match(TokenType::SLASH, TokenType::STAR)) {
            $operator   = $this->previous();
            $right      = $this->unary($context);
            $expression = new Binary($expression, $operator, $right);
        }
        return $expression;
    }

    private function unary(ParserContext $context): Expression
    {
        if ($this->match(TokenType::BANG, TokenType::MINUS)) {
            $operator = $this->previous();
            $right    = $this->unary($context);
            return new Unary($operator, $right);
        }

        return $this->primary($context);
    }

    private function primary(ParserContext $context): Expression
    {
        switch (true) {
            case $this->match(TokenType::TRUE):
                return new Literal(new BooleanValue(true));
            case $this->match(TokenType::FALSE):
                return new Literal(new BooleanValue(false));
            case $this->match(TokenType::NIL):
                return new Literal(new NilValue());
            case $this->match(TokenType::NUMBER):
                return new Literal(new NumberValue($this->previous()->literal));
            case $this->match(TokenType::STRING):
                return new Literal(new StringValue($this->previous()->literal));
            case $this->match(TokenType::LEFT_PAREN):
                $expression = $this->expression($context);
                $this->consume(TokenType::RIGHT_PAREN, "Expected ')' after expression.");
                return new Grouping($expression);
            case $this->match(TokenType::IDENTIFIER):
                return new Variable($this->previous());
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
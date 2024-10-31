<?php

namespace src\Parser;

use src\AST\Expressions\Assign;
use src\AST\Expressions\Binary;
use src\AST\Expressions\Call;
use src\AST\Expressions\ClassExpression;
use src\AST\Expressions\Expression;
use src\AST\Expressions\FunctionExpression;
use src\AST\Expressions\Get;
use src\AST\Expressions\Grouping;
use src\AST\Expressions\Literal;
use src\AST\Expressions\Logical;
use src\AST\Expressions\Set;
use src\AST\Expressions\Ternary;
use src\AST\Expressions\ThisExpression;
use src\AST\Expressions\Unary;
use src\AST\Expressions\Variable;
use src\AST\Statements\BlockStatement;
use src\AST\Statements\CompletionStatement;
use src\AST\Statements\ExpressionStatement;
use src\AST\Statements\FieldStatement;
use src\AST\Statements\IfStatement;
use src\AST\Statements\MethodStatement;
use src\AST\Statements\ReturnStatement;
use src\AST\Statements\Statement;
use src\AST\Statements\VarStatement;
use src\AST\Statements\WhileStatement;
use src\Interpreter\Runtime\Values\BooleanValue;
use src\Interpreter\Runtime\Values\NilValue;
use src\Interpreter\Runtime\Values\NumberValue;
use src\Interpreter\Runtime\Values\StringValue;
use src\Resolver\LoxClassPropertyVisibility;
use src\Scaner\Token;
use src\Scaner\TokenType;
use src\Services\Dependency\Attributes\Instance;
use src\Services\ErrorReporter;

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
            switch (true) {
//                case $this->match(TokenType::FUNCTION):
//                    return $this->function("function", $context);
                case $this->match(TokenType::VAR):
                    return $this->varDeclaration($context);
            }

            return $this->statement($context);
        } catch (ParseError $error) {
            $this->synchonize();
            return null;
        }
    }

    private function fieldDeclaration(LoxClassPropertyVisibility $visibility, ParserContext $context): Statement
    {
        $startToken  = $this->previous();
        $name        = $this->consume(TokenType::IDENTIFIER, "Expect field name.");
        $initializer = null;
        if ($this->match(TokenType::EQUAL)) {
            $initializer = $this->expression($context);
        }

        $this->match(TokenType::SEMICOLON);

        return new FieldStatement($startToken, $visibility, $name, $initializer);
    }

    private function varDeclaration(ParserContext $context): Statement
    {
        $startToken  = $this->previous();
        $name        = $this->consume(TokenType::IDENTIFIER, "Expect variable name.");
        $initializer = null;
        if ($this->match(TokenType::EQUAL)) {
            $initializer = $this->expression($context);
        }

        $this->match(TokenType::SEMICOLON);

        return new VarStatement($startToken, $name, $initializer);
    }

    private function statement(ParserContext $context): Statement
    {
        switch (true) {
            case $this->match(TokenType::FOR):
                return $this->forStmt($context);
            case $this->match(TokenType::IF):
                return $this->ifStmt($context);
            case $this->match(TokenType::RETURN):
                return $this->returnStmt($context);
            case $this->match(TokenType::WHILE):
                return $this->whileStmt($context);
            case $this->match(TokenType::BREAK):
            case $this->match(TokenType::CONTINUE):
                return $this->completionStmt($context);
            case $this->match(TokenType::LEFT_BRACE):
                $leftBrace  = $this->previous();
                $statements = $this->blockStmt($context);
                $rightBrace = $this->previous();

                return new BlockStatement($leftBrace, $statements, $rightBrace);
            default:
                return $this->expressionStmt($context);
        }
    }

    private function forStmt(ParserContext $context)
    {
        $tokenFor = $this->previous();

        $this->consume(TokenType::LEFT_PAREN, "Expect '(' after 'for'.");

        if ($this->match(TokenType::SEMICOLON)) {
            $tokenInitializer = null;
            $initializer      = null;
        } else if ($this->match(TokenType::VAR)) {
            $tokenInitializer = $this->previous();
            $initializer      = $this->varDeclaration($context);
        } else {
            $tokenInitializer = $this->peek();
            $initializer      = $this->expressionStmt($context);
        }

        $condition      = null;
        $tokenCondition = null;
        if (!$this->check(TokenType::SEMICOLON)) {
            $tokenCondition = $this->peek();
            $condition      = $this->expression($context);
        }

        $tokenAfterCondition = $this->consume(TokenType::SEMICOLON, "Exprect ';' after loop condition.");

        $increment      = null;
        $tokenIncrement = null;
        if (!$this->check(TokenType::RIGHT_PAREN)) {
            $tokenIncrement = $this->peek();
            $increment      = $this->expression($context);
        }

        $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after for clauses.");

        $context->enterLoop($increment);

        $tokenBody = $this->peek();
        $body      = $this->statement($context);

        $tokenEnd = $this->previous();
        if ($increment != null) {
            $body = new BlockStatement(
                $tokenIncrement,
                [
                    $body,
                    new ExpressionStatement($increment)
                ],
                $tokenEnd);
        }

        $context->exitLoop();

        if ($condition == null) $condition = new Literal(new BooleanValue(true), $tokenAfterCondition);

        $body = new WhileStatement($tokenFor, $condition, $body);

        if ($initializer != null) {
            $body = new BlockStatement(
                $tokenInitializer,
                [
                    $initializer,
                    $body
                ],
                $tokenEnd);
        }
        return $body;
    }

    private function ifStmt(ParserContext $context): Statement
    {
        $start = $this->previous();
        $this->consume(TokenType::LEFT_PAREN, "Expect '(' after 'if'.");
        $condition = $this->expression($context);
        $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after if condition.");

        $thenBranch = $this->statement($context);
        $elseBranch = null;

        if ($this->match(TokenType::ELSE)) {
            $elseBranch = $this->statement($context);
        }

        return new IfStatement($start, $condition, $thenBranch, $elseBranch);
    }

    private function whileStmt(ParserContext $context): Statement
    {
        $startToken = $this->previous();
        $this->consume(TokenType::LEFT_PAREN, "Expect '(' after 'while'.");
        $condition = $this->expression($context);
        $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after condition.");

        $context->enterLoop();

        $body = $this->statement($context);

        $context->exitLoop();

        return new WhileStatement($startToken, $condition, $body);
    }

    private function returnStmt(ParserContext $context): Statement
    {
        $keyword = $this->previous();
        $value   = new Literal(dependency(NilValue::class), $keyword);

        if (!$this->checkStrict(TokenType::LINE_BREAK) && !$this->checkStrict(TokenType::SEMICOLON)) {
            $value = $this->expression($context);
        }

        $this->match(TokenType::SEMICOLON);

        return new ReturnStatement($keyword, $value);
    }

    private function completionStmt(ParserContext $context): Statement
    {
        if ($context->loopCount > 0) {
            $completionToken = $this->previous();
            $completion      = new CompletionStatement($completionToken);
            $this->match(TokenType::SEMICOLON);

            $increment = end($context->loopIncrements);
            if ($increment != null && $completionToken->type == TokenType::CONTINUE) {
                return new BlockStatement(
                    $completionToken,
                    [
                        new ExpressionStatement($increment->copy()),
                        $completion
                    ],
                    $completionToken);
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

        return new ExpressionStatement($value);
    }

    private function expression(ParserContext $context): Expression
    {
        return $this->assignment($context);
    }

    private function ternary(ParserContext $context): Expression
    {
        $expression = $this->or($context);

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
        $expression = $this->ternary($context);

        if ($this->match(TokenType::EQUAL)) {
            $equals = $this->previous();
            $value  = $this->assignment($context);

            if ($expression instanceof Variable) {
                $name = $expression->name;
                return new Assign($name, $value);
            } else if ($expression instanceof Get) {
                return new Set($expression->object, $expression->name, $value);
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
        $expression = $this->equality($context);

        while ($this->match(TokenType::AND)) {
            $operator = $this->previous();
            $right    = $this->equality($context);

            $expression = new Logical($expression, $operator, $right);
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

        return $this->call($context);
    }

    private function call(ParserContext $context): Expression
    {
        $expression = $this->primary($context);

        while (true) {
            if ($this->match(TokenType::LEFT_PAREN)) {
                $expression = $this->finishCall($context, $expression);
            } else if ($this->match(TokenType::DOT)) {
                $name       = $this->consume(TokenType::IDENTIFIER, "Expect property name after '.'.");
                $expression = new Get($expression, $name);
            } else {
                break;
            }
        }

        return $expression;
    }

    private function finishCall(ParserContext $context, Expression $callee): Call
    {
        $arguments = [];
        if (!$this->check(TokenType::RIGHT_PAREN)) {
            do {
                if (count($arguments) >= 255) {
                    $this->error($this->peek(), "Can't have more than 255 arguments.");
                }
                $arguments[] = $this->expression($context);
            } while ($this->match(TokenType::COMMA));
        }

        $paren = $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after arguments.");

        return new Call($callee, $arguments, $paren);
    }

    private function primary(ParserContext $context): Expression
    {
        switch (true) {
            case $this->match(TokenType::TRUE):
                return new Literal(new BooleanValue(true), $this->previous());
            case $this->match(TokenType::FALSE):
                return new Literal(new BooleanValue(false), $this->previous());
            case $this->match(TokenType::NIL):
                return new Literal(new NilValue(), $this->previous());
            case $this->match(TokenType::NUMBER):
                return new Literal(new NumberValue($this->previous()->literal), $this->previous());
            case $this->match(TokenType::STRING):
                return new Literal(new StringValue($this->previous()->literal), $this->previous());
            case $this->match(TokenType::THIS):
                return new ThisExpression($this->previous());
            case $this->match(TokenType::LEFT_PAREN):
                $leftParen  = $this->previous();
                $expression = $this->expression($context);
                $rightParen = $this->consume(TokenType::RIGHT_PAREN, "Expected ')' after expression.");
                return new Grouping($leftParen, $expression, $rightParen);
            case $this->match(TokenType::IDENTIFIER):
                return new Variable($this->previous());
            case $this->match(TokenType::CLS):
                return $this->classDeclaration($context);
            case $this->match(TokenType::FUNCTION):
                return $this->function($context);
            default:
                throw $this->error($this->peek(), "Expect expression.");
        }
    }

    private function classDeclaration(ParserContext $context)
    {
        $tokenStart = $this->previous();

        $name = null;
        if ($this->match(TokenType::IDENTIFIER)) {
            $name = $this->previous();
        }

        $this->consume(TokenType::LEFT_BRACE, "Expect '{' before Class body.");

        $visibility = LoxClassPropertyVisibility::PUBLIC;
        $body       = [];
        while (!$this->check(TokenType::RIGHT_BRACE) && !$this->isAtEnd()) {
            if ($this->match(TokenType::PUBLIC)) {
                $visibility = LoxClassPropertyVisibility::PUBLIC;
            } else if ($this->match(TokenType::PRIVATE)) {
                $visibility = LoxClassPropertyVisibility::PRIVATE;
            }

            if ($this->match(TokenType::VAR)) {
                $body[] = $this->fieldDeclaration($visibility, $context);
            } else if ($this->match(TokenType::FUNCTION)) {
                $body[] = $this->method($visibility, $context);
            }
        }
        $this->consume(TokenType::RIGHT_BRACE, "Expect '}' after Class body.");

        return new ClassExpression($tokenStart, $name, null, $body);
    }

    private function method(LoxClassPropertyVisibility $visibility, ParserContext $context): MethodStatement
    {
        $tokenStart = $this->previous();
        $name       = $this->consume(TokenType::IDENTIFIER, "Expect methods to have a name.");
        $parameters = $this->functionParameters("method");
        $this->consume(TokenType::LEFT_BRACE, "Expect '{' before method body.");
        $body = $this->blockStmt($context);

        return new MethodStatement($tokenStart, $visibility, $name, $parameters, $body);
    }

    private function function (ParserContext $context): FunctionExpression
    {
        $tokenStart = $this->previous();

        $name = null;
        if ($this->match(TokenType::IDENTIFIER)) {
            $name = $this->previous();
        }

        $parameters = $this->functionParameters('function');
        $this->consume(TokenType::LEFT_BRACE, "Expect '{' before function body.");
        $body = $this->blockStmt($context);

        return new FunctionExpression($tokenStart, $name, $parameters, $body);
    }

    private function functionParameters(string $kind)
    {
        $this->consume(TokenType::LEFT_PAREN, "Expect '(' after $kind name.");
        $parameters = [];
        if (!$this->check(TokenType::RIGHT_PAREN)) {
            do {
                if (count($parameters) >= 255) {
                    $this->error($this->peek(), "Can't have more than 255 parameters");
                }
                $parameters[] = $this->consume(TokenType::IDENTIFIER, "Expect parameter name.");
            } while ($this->match(TokenType::COMMA));
        }
        $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after parameters.");
        return $parameters;
    }

    private function isAtEnd(): bool
    {
        while ($this->peek()->type == TokenType::LINE_BREAK) {
            $this->current++;
        }

        return $this->peek()->type == TokenType::EOF;
    }

    private function match(TokenType ...$types): bool
    {
        if ($this->isAtEnd()) return false;

        while ($this->matchStrict(TokenType::LINE_BREAK)) continue;

        $token = $this->peek();
        if (in_array($token->type, $types)) {
            $this->advance();
            return true;
        }
        return false;
    }

    private function matchStrict(TokenType ...$types): bool
    {
//        if ($this->isAtEnd()) return false;

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

        while ($this->matchStrict(TokenType::LINE_BREAK)) continue;
        return $this->peek()->type == $type;
    }

    private function checkStrict(TokenType $type)
    {
//        if ($this->isAtEnd()) return false;
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
        while ($this->matchStrict(TokenType::LINE_BREAK)) continue;
        throw $this->error($this->peek(), $message);
    }

    private function consumeStrict(TokenType $tokenType, string $message): Token
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
                case TokenType::FUNCTION:
                case TokenType::VAR:
                case TokenType::FOR:
                case TokenType::IF:
                case TokenType::WHILE:
                case TokenType::RETURN:
                    return;
            }
        }
        $this->advance();
    }
}
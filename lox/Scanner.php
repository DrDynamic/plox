<?php

namespace Lox;

use App\Attributes\Instance;
use App\Services\ErrorReporter;

#[Instance]
class Scanner
{

    private string $source;
    private array $tokens = [];

    private int $start = 0;
    private int $current = 0;
    private int $line = 1;

    public function __construct(
        private readonly ErrorReporter $errorReporter

    )
    {
    }

    public function scanTokens(string $source)
    {
        $this->source  = $source;
        $this->start   = 0;
        $this->current = 0;
        $this->line    = 0;
        $this->tokens  = [];


        while (!$this->isAtEnd()) {
            $this->start = $this->current;
            $this->scanToken();
        }
        $this->addToken(TokenType::EOF, includeLexeme: false);

        return $this->tokens;
    }

    private function isAtEnd(): bool
    {
        return $this->current >= mb_strlen($this->source);
    }

    private function scanToken()
    {
        $char = $this->advance();
        switch ($char) {
            case '(':
                $this->addToken(TokenType::LEFT_PAREN);
                break;
            case ')':
                $this->addToken(TokenType::RIGHT_PAREN);
                break;
            case '{':
                $this->addToken(TokenType::LEFT_BRACE);
                break;
            case '}':
                $this->addToken(TokenType::RIGHT_BRACE);
                break;
            case ',':
                $this->addToken(TokenType::COMMA);
                break;
            case '.':
                $this->addToken(TokenType::DOT);
                break;
            case '-':
                $this->addToken(TokenType::MINUS);
                break;
            case '+':
                $this->addToken(TokenType::PLUS);
                break;
            case ';':
                $this->addToken(TokenType::SEMICOLON);
                break;
            case '*':
                $this->addToken(TokenType::STAR);
                break;
            case '!':
                $this->addToken($this->match('=') ? TokenType::BANG_EQUAL : TokenType::BANG);
                break;
            case '=':
                $this->addToken($this->match('=') ? TokenType::EQUAL_EQUAL : TokenType::EQUAL);
                break;
            case '<':
                $this->addToken($this->match('=') ? TokenType::LESS_EQUAL : TokenType::LESS);
                break;
            case '>':
                $this->addToken($this->match('=') ? TokenType::GREATER_EQUAL : TokenType::EQUAL);
                break;
            case '/':
                if ($this->match('/')) {
                    // TODO: put comments into tokens too
                    while ($this->peek() != "\n" && !$this->isAtEnd()) $this->advance();
                } else {
                    $this->addToken(TokenType::SLASH);
                }
            case ' ':
            case "\r":
            case "\t":
                // ignore whitespace
                break;
            case "\n":
                $this->line++;
                break;
            default:
                $this->errorReporter->error($this->line, "Unexpected Character.");
                break;
        }
    }


    private function addToken(TokenType $type, mixed $literal = null, bool $includeLexeme = true)
    {
        $lexeme         = $includeLexeme ? mb_substr($this->source, $this->start, $this->current - $this->start) : "";
        $this->tokens[] = new Token($type, $lexeme, $literal, $this->line);
    }

    protected function charAt(int $position)
    {
        // used mb_substr instead of $source[$position] to support multibyte input
        return mb_substr($this->source, $position, 1);
    }

    protected function advance()
    {
        return $this->charAt($this->current++);
    }

    protected function match(string $expected): bool
    {
        if ($this->isAtEnd()) return false;
        if ($this->charAt($this->current) != $expected) return false;
        $this->current++;

        return true;
    }

    protected function peek(): string
    {
        if ($this->isAtEnd()) return "\0";
        return $this->charAt($this->current);
    }

}
<?php

namespace src\Scaner;

use src\Services\Dependency\Attributes\Instance;
use src\Services\ErrorReporter;

#[Instance]
class Scanner
{

    private string $source;
    private array $tokens = [];

    private int $start = 0;
    private int $current = 0;
    private int $line = 1;

    private const KEYWORDS = [
        'and'      => TokenType::AND,
        'class'    => TokenType::CLS,
        'else'     => TokenType::ELSE,
        'false'    => TokenType::FALSE,
        'for'      => TokenType::FOR,
        'function' => TokenType::FUNCTION,
        'if'       => TokenType::IF,
        'nil'      => TokenType::NIL,
        'or'       => TokenType::OR,
        'return'   => TokenType::RETURN,
        'super'    => TokenType::SUPER,
        'this'     => TokenType::THIS,
        'true'     => TokenType::TRUE,
        'var'      => TokenType::VAR,
        'while'    => TokenType::WHILE,
        'break'    => TokenType::BREAK,
        'continue' => TokenType::CONTINUE,
        'public'   => TokenType::PUBLIC,
        'private'  => TokenType::PRIVATE,
    ];

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
        $this->line    = 1;
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
            case ':':
                $this->addToken(TokenType::COLON);
                break;
            case '?':
                $this->addToken(TokenType::QUESTION_MARK);
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
                $this->addToken($this->match('=') ? TokenType::GREATER_EQUAL : TokenType::GREATER);
                break;
            case '/':
                if ($this->match('/')) {
                    // TODO: put comments into tokens too
                    while ($this->peek() != "\n" && !$this->isAtEnd()) $this->advance();
                } else if ($this->match("*")) {
                    $before = $this->current;
                    while ($this->peek() != '*' || $this->peekNext() != '/') {
                        if ($this->advance() == "\n") {
                            $this->addToken(TokenType::LINE_BREAK, null, false);
                            $this->line++;
                        }
                    }
                    $this->advance();
                    $this->advance();

                } else {
                    $this->addToken(TokenType::SLASH);
                }
                break;
            case '"':
                $this->string();
                break;
            case ' ':
            case "\r":
            case "\t":
                // ignore whitespace
                break;
            case "\n":
                $this->addToken(TokenType::LINE_BREAK, null, false);
                $this->line++;
                break;
            default:
                if ($this->isDigit($char)) {
                    $this->number();
                } else if ($this->isAlpha($char)) {
                    $this->identifier();
                } else {
                    if (empty($this->tokens)) {
                        $this->errorReporter->error($this->line, "Unexpected Character ($char).");
                    } else {
                        $this->errorReporter->errorAt(new Token(TokenType::ERROR, $char, null, $this->line), "Unexpected Character ($char).");
                    }
                }
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

    protected function peekNext(): string
    {
        if ($this->current + 1 >= mb_strlen($this->source)) return "\0";
        return $this->charAt($this->current + 1);
    }

    protected function isDigit(string $char): bool
    {
        return is_numeric($char);
    }

    protected function isAlpha(string $char)
    {
        return preg_match('/[a-zA-Z_]/', $char) === 1;
    }

    protected function isAlphaNumeric(string $char)
    {
        return $this->isAlpha($char) || $this->isDigit($char);
    }

    protected function string()
    {
        while ($this->peek() != '"' && !$this->isAtEnd()) {
            if ($this->peek() == "\n") {
                $this->addToken(TokenType::LINE_BREAK, null, false);
                $this->line++;
            }
            $this->advance();
        }

        if ($this->isAtEnd()) {
            $this->errorReporter->error($this->line, "Expected '\"' but got EOF");
            return;
        }

        // include the closing "
        $this->advance();

        // get the string without the starting and ending "
        $value = mb_substr($this->source, $this->start + 1, $this->current - $this->start - 2);
        // TODO: unescape escape sequences
        $this->addToken(TokenType::STRING, $value);
    }

    protected function number()
    {
        while ($this->isDigit($this->peek())) $this->advance();

        if ($this->peek() == '.' && $this->isDigit($this->peekNext())) {
            $this->advance();
            while ($this->isDigit($this->peek())) $this->advance();
        }
        $value = floatval(mb_substr($this->source, $this->start, $this->current - $this->start));
        $this->addToken(TokenType::NUMBER, $value);
    }

    protected function identifier()
    {
        while ($this->isAlphaNumeric($this->peek())) $this->advance();
        $value = mb_substr($this->source, $this->start, $this->current - $this->start);

        $type = TokenType::IDENTIFIER;
        if (isset(self::KEYWORDS[$value])) {
            $type = self::KEYWORDS[$value];
        }
        $this->addToken($type);
    }

}
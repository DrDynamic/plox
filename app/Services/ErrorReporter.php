<?php

namespace App\Services;

use App\Attributes\Singleton;
use Lox\Scan\Token;
use Lox\Scan\TokenType;

#[Singleton]
class ErrorReporter
{
    public bool $hadError = false;
    public bool $hadRuntimeError = false;

    public function reset()
    {
        $this->hadError = false;
    }

    public function errorAfter(Token $token, string $message)
    {
        if ($token->tokenType == TokenType::EOF) {
            $this->report($token->line, "at end", $message);
        } else {
            $this->report($token->line, "after '$token->lexeme'", $message);
        }
    }

    public function errorAt(Token $token, string $message)
    {
        if ($token->tokenType == TokenType::EOF) {
            $this->report($token->line, "at end", $message);
        } else {
            $this->report($token->line, "at '$token->lexeme'", $message);
        }
    }

    public function error(int $line, string $message)
    {
        $this->report($line, "", $message);
    }

    public function runtimeError(\Lox\Interpret\RuntimeError $runtimeError)
    {
        $line = $runtimeError->token->line;
        fwrite(STDERR,
            $runtimeError->getMessage()."\n[line $line] near {$runtimeError->token->lexeme}\n");
        $this->hadRuntimeError = true;
    }

    public function report(int $line, string $where, string $message)
    {
        fwrite(STDERR, "[$line] Error $where: $message\n");
        $this->hadError = true;
    }


}
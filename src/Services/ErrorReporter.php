<?php

namespace src\Services;

use src\Scaner\Token;
use src\Scaner\TokenType;
use src\Services\Dependency\Attributes\Singleton;

#[Singleton]
class ErrorReporter
{
    public bool $hadError = false;
    public bool $hadRuntimeError = false;

    public function reset()
    {
        $this->hadError = false;
    }

    public function errorAt(Token $token, string $message)
    {
        if ($token->type == TokenType::EOF) {
            $this->report($token->line, "at end", $message);
        } else {
            $this->report($token->line, "at '$token->lexeme'", $message);
        }
    }

    public function error(int $line, string $message)
    {
        $this->report($line, "", $message);
    }

    public function runtimeError(\src\Interpreter\Runtime\Errors\RuntimeError $runtimeError)
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
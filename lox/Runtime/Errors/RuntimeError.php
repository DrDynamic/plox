<?php

namespace Lox\Runtime\Errors;

use JetBrains\PhpStorm\Pure;
use Lox\Scan\Token;
use Throwable;

class RuntimeError extends \Exception
{
    public readonly Token $token;

    #[Pure] public function __construct(Token $token, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->token = $token;
    }

}
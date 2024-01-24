<?php

namespace Lox;


use App\Attributes\Instance;
use App\Services\Arr;
use App\Services\ErrorReporter;
use Lox\Interpreter\Interpreter;
use Lox\Parser\Parser;
use Lox\Resolver\Resolver;
use Lox\Runtime\LoxType;
use Lox\Scaner\Scanner;

#[Instance]
class Lox
{
    private bool $hadError = false;


    public function __construct(
        private readonly Scanner       $scanner,
        private readonly Parser        $parser,
        private readonly Resolver      $resolver,
        private readonly Interpreter   $interpreter,
        private readonly ErrorReporter $errorReporter,
    )
    {
    }

    public function runString(string $source)
    {
        [$result, $expression] = $this->run($source);
        return $result;
    }

    public function runFile(string $file)
    {
        // TODO: replace with something to lazy load file line by line
        $source = file_get_contents($file);
        $this->run($source);

        if ($this->errorReporter->hadError) exit(ExitCodes::EX_DATAERR);
        if ($this->errorReporter->hadRuntimeError) exit(ExitCodes::EX_SOFTWARE);
    }

    public function runCli()
    {
        // TODO: use something like psy/psysh?

        while (true) {
            $line = readline('>');
            if (in_array($line, ['exit', 'exit;', 'q', 'quit', false])) {
                break;
            }
            [$result, $statements] = $this->run($line);
            if ($result) {
                echo $result->cast(LoxType::String, Arr::last($statements))->value."\n";
            }
            $this->errorReporter->reset();
        }
    }

    private function run(string $source)
    {
        $tokens     = $this->scanner->scanTokens($source);
        $statements = $this->parser->parse($tokens);

        if ($this->errorReporter->hadError) return null;

        $this->resolver->resolveAll($statements);

        if ($this->errorReporter->hadError) return null;


        return [$this->interpreter->interpret($statements), $statements];
    }
}
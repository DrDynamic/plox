<?php

namespace Lox;


use App\Attributes\Instance;
use App\Services\Arr;
use App\Services\ErrorReporter;
use Lox\Interpret\Interpreter;
use Lox\Parse\Parser;
use Lox\Runtime\Values\ValueType;
use Lox\Scan\Scanner;

#[Instance]
class Lox
{
    private bool $hadError = false;


    public function __construct(
        private readonly Scanner       $scanner,
        private readonly Parser        $parser,
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
                echo $result->cast(ValueType::String, Arr::last($statements))->value."\n";
            }
            $this->errorReporter->reset();
        }
    }

    private function run(string $source)
    {
        $tokens     = $this->scanner->scanTokens($source);
        $statements = $this->parser->parse($tokens);

        if ($this->errorReporter->hadError) return null;

        return [$this->interpreter->interpret($statements), $statements];
    }

    private function error(int $line, string $message)
    {
        $this->report($line, "", $message);
    }

    private static function report(int $line, string $where, string $message)
    {
        fwrite(STDERR, "[$line] Error$where: $message\n");
    }
}
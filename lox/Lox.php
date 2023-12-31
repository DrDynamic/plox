<?php

namespace Lox;


use App\Attributes\Instance;
use App\Services\ErrorReporter;
use Lox\Interpret\Interpreter;
use Lox\Parse\Parser;
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
        $this->run($source);
        if ($this->errorReporter->hadError) exit(ExitCodes::EX_DATAERR);
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
            $this->run($line);
            $this->errorReporter->reset();
        }
    }

    private function run(string $source)
    {
        $tokens     = $this->scanner->scanTokens($source);
        $expression = $this->parser->parse($tokens);

        if ($this->errorReporter->hadError) return;
//        echo (new AstPrinter(true))->print($expression)."\n";

        $result = $this->interpreter->interpret($expression);
        echo $this->interpreter->stringify($result)."\n";

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
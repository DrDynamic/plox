<?php

namespace src;


use src\AST\AstPrinter;
use src\Interpreter\Interpreter;
use src\Interpreter\Runtime\LoxType;
use src\Parser\Parser;
use src\Resolver\Resolver;
use src\Scaner\Scanner;
use src\Services\Arr;
use src\Services\Dependency\Attributes\Instance;
use src\Services\ErrorReporter;

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

    public function reverseAST(string $file) {
        $source = file_get_contents($file);

        $tokens     = $this->scanner->scanTokens($source);
        $statements = $this->parser->parse($tokens);
        $this->resolver->resolveAll($statements);

        $astPrinter = new AstPrinter();
        $serialized = "";

        foreach ($statements as $statement) {
            $serialized .= $statement->accept($astPrinter);
        }

        return $serialized;
    }

    public function jsonAST(string $file) {
        $source = file_get_contents($file);

        $tokens     = $this->scanner->scanTokens($source);
        $statements = $this->parser->parse($tokens);
        $this->resolver->resolveAll($statements);

        return json_encode($statements, JSON_PRETTY_PRINT);
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
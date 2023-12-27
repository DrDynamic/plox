<?php

namespace Lox;

class Lox
{
    private bool $hadError = false;

    public function runFile(string $file)
    {
        // TODO: replace with something to lazy load file line by line
        $code = file_get_contents($file);
        $this->run($code);

        if($this->hadError) exit(65);
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
        }
    }

    private function run(string $code)
    {
        echo "[$code]\n";
    }

    private function error(int $line, string $message)
    {
        $this->report($line, "", $message);
    }

    private static function report(int $line, string $where, string $message)
    {
        fwrite(STDERR, "[$line] Error$where: $message");
    }
}
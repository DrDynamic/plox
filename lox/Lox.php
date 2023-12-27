<?php

namespace Lox;

class Lox
{
    public static function runFile(string $file)
    {
        // TODO: replace with something to lazy load file line by line
        $code = file_get_contents($file);
        self::run($code);
    }

    public static function runCli()
    {
        // TODO: use something like psy/psysh?

        while (true) {
            $line = readline('>');
            if (in_array($line, ['exit', 'exit;', 'q', 'quit', false])) {
                break;
            }
            self::run($line);
        }
    }

    private static function run(string $code)
    {
        echo "[$code]\n";
    }
}
<?php

use App\Services\ErrorReporter;
use Lox\Interpret\Interpreter;
use Lox\Lox;
use Lox\Parse\Parser;
use Lox\Runtime\Environment;
use Lox\Runtime\Values\Value;
use Lox\Scan\Scanner;
use Lox\Scan\Token;
use Lox\Scan\TokenType;

require_once __DIR__.'/../app/Services/helpers.php';

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

// uses(Tests\TestCase::class)->in('Feature');
uses(\Tests\TestCase::class)->beforeEach(function () {
    $this->errorReporter = dependency(ErrorReporter::class);
    $this->environment   = dependency(Environment::class);

    $this->scanner     = dependency(Scanner::class);
    $this->parser      = dependency(Parser::class);
    $this->interpreter = new Interpreter($this->errorReporter, $this->environment);

    $this->lox = new Lox($this->scanner, $this->parser, $this->interpreter, $this->errorReporter);
})->in(__DIR__);

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/


/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/


function execute(string $source): void
{
    test()->lox->runString($source);
}

function evaluate(string $source): Value
{
    test()->environment = new Environment();
    test()->interpreter = new Interpreter(test()->errorReporter, test()->environment);
    test()->lox         = new Lox(test()->scanner, test()->parser, test()->interpreter, test()->errorReporter);

    test()->lox->runString("var _result = ($source)");
    return test()->environment->get(new Token(TokenType::IDENTIFIER, '_result', null, 0));
}

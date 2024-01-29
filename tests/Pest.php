<?php

use src\Interpreter\Interpreter;
use src\Interpreter\Runtime\Environment;
use src\Interpreter\Runtime\Values\BaseValue;
use src\Lox;
use src\Parser\Parser;
use src\Resolver\Resolver;
use src\Scaner\Scanner;
use src\Scaner\Token;
use src\Scaner\TokenType;
use src\Services\Dependency\Dependency;
use src\Services\ErrorReporter;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

require_once __DIR__.'/../src/helpers.php';

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
    resetLox();
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

expect()->extend('toHave', function ($name, $value = null) {
    $variable = new Token(TokenType::IDENTIFIER, $name, null, 0);
    assertTrue($this->value->has($variable));
    if ($value !== null) {
        assertEquals($this->value->get($variable), $value);
    }
});

expect()->extend('toNotHave', function ($name) {
    $variable = new Token(TokenType::IDENTIFIER, $name, null, 0);
    assertFalse($this->value->has($variable));
});

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

function resetLox(array $dependencies = []): void
{
    Dependency::reset();
    $dependency = Dependency::getInstance();
    // TODO: not very clean... (needed for constructor of lox\Interpreter\Interpreter.php) / Also in /plox.php

    if (isset($dependencies[WeakMap::class])) {
        $dependency->instance(WeakMap::class, $dependencies[WeakMap::class]);
    } else {
        $dependency->instance(WeakMap::class, fn() => new WeakMap());
    }

    if (isset($dependencies[ErrorReporter::class])) {
        $dependency->singleton(ErrorReporter::class, $dependencies[ErrorReporter::class]);
    }
    test()->errorReporter = dependency(ErrorReporter::class);

    if (isset($dependencies[Environment::class])) {
        $dependency->instance(Environment::class, $dependencies[Environment::class]);
    }
    test()->environment = dependency(Environment::class);

    if (isset($dependencies[Interpreter::class])) {
        $dependency->singleton(Interpreter::class, $dependencies[Interpreter::class]);
        test()->interpreter = dependency(Interpreter::class);
    } else {
        test()->interpreter = new Interpreter(test()->errorReporter, test()->environment, new WeakMap());
        $dependency->singleton(Interpreter::class, test()->interpreter);
    }

    if (isset($dependencies[Scanner::class])) {
        $dependency->instance(Scanner::class, $dependencies[Scanner::class]);
    }
    test()->scanner = dependency(Scanner::class);

    if (isset($dependencies[Parser::class])) {
        $dependency->instance(Parser::class, $dependencies[Parser::class]);
    }
    test()->parser = dependency(Parser::class);

    if (isset($dependencies[Resolver::class])) {
        $dependency->instance(Resolver::class, $dependencies[Resolver::class]);
    }
    test()->resolver = dependency(Resolver::class);


    test()->lox = new Lox(test()->scanner, test()->parser, test()->resolver, dependency(Interpreter::class), test()->errorReporter);

}

function execute(string $source): void
{
    test()->lox->runString($source);
}

function evaluate(string $source): BaseValue
{
    return test()->lox->runString($source);
}

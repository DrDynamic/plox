<?php

namespace Lox\Resolver;

use App\Attributes\Instance;
use App\Services\Arr;
use App\Services\ErrorReporter;
use Lox\AST\Expressions\Assign;
use Lox\AST\Expressions\Binary;
use Lox\AST\Expressions\Call;
use Lox\AST\Expressions\Expression;
use Lox\AST\Expressions\FunctionExpression;
use Lox\AST\Expressions\Grouping;
use Lox\AST\Expressions\Literal;
use Lox\AST\Expressions\Logical;
use Lox\AST\Expressions\Ternary;
use Lox\AST\Expressions\Unary;
use Lox\AST\Expressions\Variable;
use Lox\AST\ExpressionVisitor;
use Lox\AST\Statements\BlockStatement;
use Lox\AST\Statements\CompletionStatement;
use Lox\AST\Statements\ExpressionStatement;
use Lox\AST\Statements\IfStatement;
use Lox\AST\Statements\ReturnStatement;
use Lox\AST\Statements\VarStatement;
use Lox\AST\Statements\WhileStatement;
use Lox\AST\StatementVisitor;
use Lox\Interpreter\Interpreter;
use Lox\Scaner\Token;

#[Instance]
class Resolver implements ExpressionVisitor, StatementVisitor
{
    private $scopes = [];
    private $currentFunction = LoxFunctionType::NONE;

    public function __construct(
        private readonly ErrorReporter $errorReporter,
        private readonly Interpreter   $interpreter
    )
    {
    }

    public function resolve($statementOrExpression)
    {
        $statementOrExpression->accept($this);
    }

    public function resolveAll(array $statementsOrExpressions)
    {
        foreach ($statementsOrExpressions as $statement) {
            $this->resolve($statement);
        }
    }

    #[\Override] public function visitExpressionStmt(ExpressionStatement $statement)
    {
        $this->resolve($statement->expression);
    }

    #[\Override] public function visitVarStmt(VarStatement $statement)
    {
        $this->declare($statement->name);
        if ($statement->initializer != null) {
            $this->resolve($statement->initializer);
        }
        $this->define($statement->name);
    }

    #[\Override] public function visitBlockStmt(BlockStatement $statement)
    {
        $this->beginScope();
        $this->resolveAll($statement->statements);
        $this->endScope();
    }

    #[\Override] public function visitIfStmt(IfStatement $statement)
    {
        $this->resolve($statement->condition);
        $this->resolve($statement->thenBranch);
        if ($statement->elseBranch != null) {
            $this->resolve($statement->elseBranch);
        }
    }

    #[\Override] public function visitWhileStmt(WhileStatement $statement)
    {
        $this->resolve($statement->condition);
        $this->resolve($statement->body);
    }

    #[\Override] public function visitCompletionStmt(CompletionStatement $statement)
    {
        // nothing to traverse
    }

    #[\Override] public function visitReturnStmt(ReturnStatement $statement)
    {
        if ($this->currentFunction == LoxFunctionType::NONE) {
            $this->errorReporter->errorAt($statement->tokenStart, "Can't return from top-level code.");
        }

        if ($statement->value != null) {
            $this->resolve($statement->value);
        }
    }

    #[\Override] public function visitTernaryExpr(Ternary $expression)
    {
        $this->resolve($expression->condition);
        $this->resolve($expression->then);
        $this->resolve($expression->else);
    }

    #[\Override] public function visitBinaryExpr(Binary $expression)
    {
        $this->resolve($expression->left);
        $this->resolve($expression->right);
    }

    #[\Override] public function visitGroupingExpr(Grouping $expression)
    {
        $this->resolve($expression->expression);
    }

    #[\Override] public function visitLiteralExpr(Literal $expression)
    {
        // nothing to traverse
    }

    #[\Override] public function visitUnaryExpr(Unary $expression)
    {
        $this->resolve($expression->right);
    }

    #[\Override] public function visitVariableExpr(Variable $expression)
    {
        if (!empty($this->scopes)
            && isset(end($this->scopes)[$expression->name->lexeme])
            && end($this->scopes)[$expression->name->lexeme] === false) {
            dd("visitVar", $this->scopes, end($this->scopes)[$expression->name->lexeme]);
            $this->errorReporter->errorAt($expression->name, "Can't read local variable in its own initializer.");
        }

        $this->resolveLocal($expression, $expression->name);
    }

    #[\Override] public function visitAssignExpr(Assign $expression)
    {
        $this->resolve($expression->value);
        $this->resolveLocal($expression, $expression->name);
    }

    #[\Override] public function visitLogicalExpr(Logical $expression)
    {
        $this->resolve($expression->left);
        $this->resolve($expression->right);
    }

    #[\Override] public function visitCallExpr(Call $call)
    {
        $this->resolve($call->callee);
        $this->resolveAll($call->arguments);
    }

    #[\Override] public function visitFunctionExpr(FunctionExpression $expression)
    {
        if ($expression->name != null) {
            $this->declare($expression->name);
            $this->define($expression->name);
        }

        $this->resolveFunction($expression, LoxFunctionType::FUNCTION);
    }

    private function beginScope()
    {
        $this->scopes[] = [];
    }

    private function resolveLocal(Expression $expression, Token $name)
    {
        for ($i = count($this->scopes) - 1; $i >= 0; $i--) {
            if (isset($this->scopes[$i][$name->lexeme])) {
                $this->interpreter->resolve($expression, count($this->scopes) - 1 - $i);
                return;
            }
        }
    }

    private function endScope()
    {
        Arr::pop($this->scopes);
    }

    private function declare(Token $name)
    {
        if (empty($this->scopes)) return;

        $scope = end($this->scopes);

        if (array_key_exists($name->lexeme, $this->scopes)) {
            $this->errorReporter->errorAt($name, "Variable $name->lexeme is already declared in this scope!");
        }

        $scope[$name->lexeme] = false;
    }

    private function define(Token $name)
    {
        if (empty($this->scopes)) return;

        $scope                = end($this->scopes);
        $scope[$name->lexeme] = true;
    }

    private function resolveFunction(FunctionExpression $expression, LoxFunctionType $type)
    {
        $enclosingFunction     = $this->currentFunction;
        $this->currentFunction = $type;

        $this->declare($expression->name);
        $this->define($expression->name);

        $this->beginScope();
        foreach ($expression->parameters as $parameter) {
            $this->declare($parameter);
            $this->define($parameter);
        }
        $this->resolveAll($expression->body);
        $this->endScope();

        $this->currentFunction = $enclosingFunction;
    }


}
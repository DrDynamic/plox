<?php

namespace src\Resolver;

use src\AST\Expressions\Assign;
use src\AST\Expressions\Binary;
use src\AST\Expressions\Call;
use src\AST\Expressions\Expression;
use src\AST\Expressions\FunctionExpression;
use src\AST\Expressions\Grouping;
use src\AST\Expressions\Literal;
use src\AST\Expressions\Logical;
use src\AST\Expressions\Ternary;
use src\AST\Expressions\Unary;
use src\AST\Expressions\Variable;
use src\AST\ExpressionVisitor;
use src\AST\Statements\BlockStatement;
use src\AST\Statements\CompletionStatement;
use src\AST\Statements\ExpressionStatement;
use src\AST\Statements\IfStatement;
use src\AST\Statements\ReturnStatement;
use src\AST\Statements\VarStatement;
use src\AST\Statements\WhileStatement;
use src\AST\StatementVisitor;
use src\Interpreter\Interpreter;
use src\Scaner\Token;
use src\Services\Arr;
use src\Services\Dependency\Attributes\Instance;
use src\Services\ErrorReporter;

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

        $scope = Arr::pop($this->scopes);

        if (array_key_exists($name->lexeme, $scope)) {
            $this->errorReporter->errorAt($name, "Variable $name->lexeme is already declared in this scope!");
        }

        $scope[$name->lexeme] = false;
        $this->scopes[]       = $scope;
    }

    private function define(Token $name)
    {
        if (empty($this->scopes)) return;

        $scope                = Arr::pop($this->scopes);
        $scope[$name->lexeme] = true;
        $this->scopes[] = $scope;
    }

    private function resolveFunction(FunctionExpression $expression, LoxFunctionType $type)
    {
        $enclosingFunction     = $this->currentFunction;
        $this->currentFunction = $type;

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
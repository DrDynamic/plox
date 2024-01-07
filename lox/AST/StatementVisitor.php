<?php

namespace Lox\AST;

use Lox\AST\Statements\BlockStatement;
use Lox\AST\Statements\CompletionStatement;
use Lox\AST\Statements\ExpressionStatement;
use Lox\AST\Statements\FunctionStatement;
use Lox\AST\Statements\IfStatement;
use Lox\AST\Statements\VarStatement;
use Lox\AST\Statements\WhileStatement;

interface StatementVisitor
{
    public function visitExpressionStmt(ExpressionStatement $statement);

    public function visitVarStmt(VarStatement $statement);

    public function visitBlockStmt(BlockStatement $statement);

    public function visitIfStmt(IfStatement $statement);

    public function visitWhileStmt(WhileStatement $statement);

    public function visitCompletionStmt(CompletionStatement $statement);

    public function visitFunctionStmt(FunctionStatement $statement);
}
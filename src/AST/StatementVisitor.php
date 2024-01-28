<?php

namespace src\AST;

use src\AST\Statements\BlockStatement;
use src\AST\Statements\CompletionStatement;
use src\AST\Statements\ExpressionStatement;
use src\AST\Statements\IfStatement;
use src\AST\Statements\ReturnStatement;
use src\AST\Statements\VarStatement;
use src\AST\Statements\WhileStatement;

interface StatementVisitor
{
    public function visitExpressionStmt(ExpressionStatement $statement);

    public function visitVarStmt(VarStatement $statement);

    public function visitBlockStmt(BlockStatement $statement);

    public function visitIfStmt(IfStatement $statement);

    public function visitWhileStmt(WhileStatement $statement);

    public function visitCompletionStmt(CompletionStatement $statement);

    public function visitReturnStmt(ReturnStatement $statement);
}
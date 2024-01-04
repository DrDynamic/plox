<?php

namespace Lox\AST;

use Lox\AST\Statements\BlockStatement;
use Lox\AST\Statements\CompletionStatement;
use Lox\AST\Statements\ExpressionStmt;
use Lox\AST\Statements\IfStatement;
use Lox\AST\Statements\PrintStatement;
use Lox\AST\Statements\VarStatement;
use Lox\AST\Statements\WhileStatement;

interface StatementVisitor
{
    public function visitExpressionStmt(ExpressionStmt $statement);

    public function visitPrintStmt(PrintStatement $print);

    public function visitVarStmt(VarStatement $var);

    public function visitBlockStmt(BlockStatement $block);

    public function visitIfStmt(IfStatement $if);

    public function visitWhileStmt(WhileStatement $while);

    public function visitCompletionStmt(CompletionStatement $completion);
}
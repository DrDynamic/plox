<?php

namespace Lox\AST;

use Lox\AST\Statements\BlockStatement;
use Lox\AST\Statements\ExpressionStmt;
use Lox\AST\Statements\IfStatement;
use Lox\AST\Statements\PrintStmt;
use Lox\AST\Statements\VarStatement;

interface StatementVisitor
{
    public function visitExpressionStmt(ExpressionStmt $statement);

    public function visitPrintStmt(PrintStmt $statement);

    public function visitVarStmt(VarStatement $statement);

    public function visitBlockStmt(BlockStatement $block);

    public function visitIfStmt(IfStatement $if);
}
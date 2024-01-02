<?php

namespace Lox\AST;

interface StatementVisitor
{
    public function visitExpressionStmt(Statements\ExpressionStmt $statement);

    public function visitPrintStmt(Statements\PrintStmt $statement);

    public function visitVarStmt(Statements\VarStatement $statement);
}
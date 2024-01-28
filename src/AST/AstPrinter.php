<?php

namespace src\AST;

use src\AST\Expressions\Assign;
use src\AST\Expressions\Binary;
use src\AST\Expressions\Call;
use src\AST\Expressions\FunctionExpression;
use src\AST\Expressions\Grouping;
use src\AST\Expressions\Literal;
use src\AST\Expressions\Logical;
use src\AST\Expressions\Ternary;
use src\AST\Expressions\Unary;
use src\AST\Expressions\Variable;
use src\AST\Statements\BlockStatement;
use src\AST\Statements\CompletionStatement;
use src\AST\Statements\ExpressionStatement;
use src\AST\Statements\IfStatement;
use src\AST\Statements\ReturnStatement;
use src\AST\Statements\VarStatement;
use src\AST\Statements\WhileStatement;
use src\Services\Dependency\Attributes\Singleton;

#[Singleton]
class AstPrinter implements ExpressionVisitor, StatementVisitor
{
    public $indent = "  ";
    private $currentIndent = "";

    #[\Override] public function visitBinaryExpr(Binary $expression)
    {
        $left  = $expression->left->accept($this);
        $right = $expression->right->accept($this);

        return "$left {$expression->operator->lexeme} $right";
    }

    #[\Override] public function visitGroupingExpr(Grouping $expression)
    {
        return $this->indent("(".$expression->accept($this).")\n");
    }

    #[\Override] public function visitLiteralExpr(Literal $expression)
    {
        if ($expression->value == null) return "nil";
        return strval($expression->value->value);
    }

    #[\Override] public function visitUnaryExpr(Unary $expression)
    {
        $right = $expression->right->accept($this);
        return "{$expression->operator->lexeme}$right";
    }

    #[\Override] public function visitTernaryExpr(Ternary $expression)
    {
        $condition = $expression->condition->accept($this);
        $then      = $expression->then->accept($this);
        $else      = $expression->else->accept($this);

        return "$condition ? $then : $else\n";
    }

    #[\Override] public function visitVariableExpr(Variable $expression)
    {
        return $expression->name->lexeme;
    }

    #[\Override] public function visitAssignExpr(Assign $expression)
    {
        return $this->indent("{$expression->name->lexeme} = ".$expression->value->accept($this)."\n");
    }

    #[\Override] public function visitLogicalExpr(Logical $expression)
    {
        $left  = $expression->left->accept($this);
        $right = $expression->right->accept($this);

        return "$left {$expression->operator->lexeme} $right";
    }

    #[\Override] public function visitCallExpr(Call $call)
    {
        $arguments = [];
        foreach ($call->arguments as $argument) {
            $arguments[] = $argument->accept($this);
        }

        return $this->indent($call->callee->accept($this)."(".implode(', ', $arguments).")\n");
    }

    #[\Override] public function visitFunctionExpr(FunctionExpression $expression)
    {
        $parameters = [];
        foreach ($expression->parameters as $parameter) {
            $parameters[] = $parameter->lexeme;
        }
        $result = $this->indent("{$expression->name->lexeme}(".implode(', ', $parameters).") {");

        $this->addIndent();
        foreach ($expression->body as $item) {
            $result .= $this->indent($item->accept($this)."\n");
        }
        $this->removeIndent();

        $result .= $this->indent("}\n");
        return $result;
    }

    #[\Override] public function visitExpressionStmt(ExpressionStatement $statement)
    {
        return $statement->expression->accept($this);
    }

    #[\Override] public function visitVarStmt(VarStatement $statement)
    {
        if ($statement->initializer == null) {
            return $this->indent("var {$statement->name->lexeme}");
        } else {
            $initializer = $statement->initializer->accept($this);
            return $this->indent("var {$statement->name->lexeme} = $initializer\n");
        }
    }

    #[\Override] public function visitBlockStmt(BlockStatement $statement)
    {
        $result = $this->indent("{\n");
        $this->addIndent();
        foreach ($statement->statements as $statement) {
            $result .= $statement->accept($this)."\n";
        }
        $this->removeIndent();
        $result .= $this->indent("}\n");

        return $result;
    }

    #[\Override] public function visitIfStmt(IfStatement $statement)
    {
        $result = $this->indent("if(".$statement->condition->accept($this).") {\n");

        $this->addIndent();
        $result .= $this->indent($statement->thenBranch->accept($this)."\n");
        $this->removeIndent();
        $result .= $this->indent("}");
        if ($statement->elseBranch != null) {
            $result .= " else {\n";

            $this->addIndent();
            $result .= $this->indent($statement->elseBranch->accept($this)."\n");
            $this->removeIndent();
            $result .= $this->indent("}\n");
            return $result;
        }
        return $result."\n";
    }

    #[\Override] public function visitWhileStmt(WhileStatement $statement)
    {
        $result = $this->indent("while(".$statement->condition->accept($this).") \n");
        $result .= $statement->body->accept($this)."\n";

        return $result;
    }

    #[\Override] public function visitCompletionStmt(CompletionStatement $statement)
    {
        return $this->indent("{$statement->operator->lexeme}\n");
    }

    #[\Override] public function visitReturnStmt(ReturnStatement $statement)
    {
        if ($statement->value == null) {
            return $this->indent("return\n");
        } else {
            return $this->indent("return ".$statement->value->accept($this)."\n");
        }
    }

    private function indent($str)
    {
        return $this->currentIndent.$str;
    }

    private function addIndent()
    {
        $this->currentIndent .= $this->indent;
    }

    private function removeIndent()
    {
        $this->currentIndent = substr($this->currentIndent, strlen($this->indent));
    }
}
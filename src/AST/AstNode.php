<?php

namespace src\AST;

class AstNode
{
    public function copy()
    {
        return unserialize(serialize($this));
    }
}
<?php

namespace src\Services;

use src\Services\Dependency\Attributes\Singleton;

#[Singleton]
class IdService
{
    private int $lastId = 0;

    public function createId()
    {
        return ++$this->lastId;
    }
}
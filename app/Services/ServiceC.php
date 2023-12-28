<?php

namespace App\Services;

class ServiceC
{
    public function __construct(
        private readonly ServiceB $service,
    )
    {
    }
}
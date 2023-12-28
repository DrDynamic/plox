<?php

namespace App\Services;

class ServiceB
{
    public function __construct(
        private readonly ServiceA $service,
    )
    {
    }
}
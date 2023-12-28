<?php

namespace App\Services;

class ServiceA
{
    public function __construct(
        private readonly ServiceC $service,
    )
    {
    }
}
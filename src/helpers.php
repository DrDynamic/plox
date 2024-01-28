<?php

use src\Services\Dependency\Dependency;

if (!function_exists('dependency')) {
    /**
     * Get the available dependency from an abstract
     * @param string|null $abstract
     * @return mixed
     */
    function dependency($abstract = null)
    {
        if (is_null($abstract)) {
            return Dependency::getInstance();
        }
        return Dependency::getInstance()->make($abstract);
    }
}

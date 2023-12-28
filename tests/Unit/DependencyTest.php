<?php
require_once __DIR__.'/../../app/Services/helpers.php';

#[\App\Attributes\Singleton]
class SingletonClass
{
}

#[\App\Attributes\Instance]
class InstanceClass
{
}

it('creates a singleton just once', function () {
    // Instances need to be cached, because object_hashes are reused by php
    $dependencies = [
        dependency(SingletonClass::class),
        dependency(SingletonClass::class),
        dependency(SingletonClass::class),
        dependency(SingletonClass::class),
        dependency(SingletonClass::class)
    ];

    $hash = spl_object_hash(dependency(SingletonClass::class));

    foreach ($dependencies as $dependency) {
        $this->assertEquals($hash, spl_object_hash($dependency));
    }
});

it('creates an instance for each dependency, that requests it', function () {
    // Instances need to be cached, because object_hashes are reused by php
    $dependencies = [
        dependency(SingletonClass::class),
        dependency(SingletonClass::class),
        dependency(SingletonClass::class),
        dependency(SingletonClass::class),
        dependency(SingletonClass::class)
    ];

    $hash = spl_object_hash(dependency(SingletonClass::class));

    foreach ($dependencies as $dependency) {
        $this->assertEquals($hash, spl_object_hash($dependency));
    }
});
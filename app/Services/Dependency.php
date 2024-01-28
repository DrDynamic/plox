<?php

namespace App\Services;

use App\Attributes\Instance;
use App\Attributes\Singleton;
use App\Exceptions\DependencyResolutionException;
use Closure;
use ReflectionParameter;

class Dependency
{
    protected static self|null $instance = null;

    private array $dependencies = [];

    /** @var array $buildStack to find cyclic dependencies */
    private array $buildStack = [];

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            self::reset();
        }

        return static::$instance;
    }

    public static function reset()
    {
        static::$instance = new static;
    }

    /**
     * true when a dependency is registered, false otherwise
     * @param $abstract
     * @return bool
     */
    public function has($abstract): bool
    {
        return isset($this->dependencies[$abstract]);
    }

    /**
     * returns an instance of a dependency
     * @param $abstract
     * @return array|mixed
     * @throws DependencyResolutionException
     */
    public function make($abstract): mixed
    {
        if ($this->has($abstract)) {
            return $this->buildDependency($abstract);
        } else {
            return $this->createAndCacheDependency($abstract, $abstract);
        }
    }

    /**
     * define a singleton (also overrides existing ones)
     * @return void
     */
    public function singleton($abstract, $concrete)
    {
        // concrete could be callable (to crete the instance) or the direct instance
        if (is_callable($concrete)) {
            $this->dependencies[$abstract] = $concrete();
        } else {
            $this->dependencies[$abstract] = $concrete;
        }

    }

    public function instance($abstract, callable $concrete)
    {
        $this->dependencies[$abstract] = $concrete;
    }

    protected function createAndCacheDependency($abstract, $concrete): mixed
    {
        if (in_array($concrete, $this->buildStack)) {
            $previous = implode(', ', $this->buildStack);

            throw new DependencyResolutionException("Cyclic dependency detected while building $previous");
        }

        try {
            $reflector = new \ReflectionClass($concrete);
        } catch (\ReflectionException $e) {
            throw new DependencyResolutionException("Target class [$concrete] does not exist.", 0, $e);
        }

        $factory = $this->createDependencyFactory($reflector, $concrete);

        $singleton = $reflector->getAttributes(Singleton::class);
        $instance  = $reflector->getAttributes(Instance::class);

        if (count($singleton) > 0) {
            $this->dependencies[$abstract] = $factory();
            return $this->dependencies[$abstract];
        } else if (count($instance) > 0) {
            $this->dependencies[$abstract] = $factory;
            return $factory();
        }

        throw new DependencyResolutionException("Unspecified dependency type Class $concrete");
    }


    /**
     * @param $concrete
     * @return Closure|mixed
     * @throws DependencyResolutionException
     */
    protected function createDependencyFactory(\ReflectionClass $reflector, $concrete)
    {


        if (!$reflector->isInstantiable()) {
            if (!empty($this->buildStack)) {
                $previous = implode(', ', $this->buildStack);

                $message = "Target [$concrete] is not instantiable while building [$previous].";
            } else {
                $message = "Target [$concrete] is not instantiable.";
            }
            throw new DependencyResolutionException($message);
        }

        $constructor = $reflector->getConstructor();

        $this->buildStack[] = $concrete;

        if (is_null($constructor)) {
            array_pop($this->buildStack);
            return $this->getFactory($reflector, []);
        }

        $reflectionDependencies = $constructor->getParameters();

        $resolvedDependencies = [];
        try {
            foreach ($reflectionDependencies as $reflectionDependency) {
                // If the class is null, it means the dependency is a string or some other
                // primitive type which we can not resolve since it is not a class and
                // we will just bomb out with an error since we have no-where to go.
                $resolvedDependencies[] = is_null($this->getParameterClassName($reflectionDependency))
                    ? $this->resolvePrimitive($reflectionDependency)
                    : $this->resolveClass($reflectionDependency);
            }
        } catch (DependencyResolutionException $e) {
            array_pop($this->buildStack);
            throw $e;
        }

        array_pop($this->buildStack);
        return $this->getFactory($reflector, $resolvedDependencies);
    }

    protected function buildDependency($abstract)
    {
        $concrete = $this->dependencies[$abstract];

        if ($concrete instanceof Closure) {
            // build method (instance)
            return $concrete();
        }
        // already build (singleton)
        return $concrete;
    }

    protected function getParameterClassName($parameter)
    {
        $type = $parameter->getType();

        if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }

        $name = $type->getName();

        if (!is_null($class = $parameter->getDeclaringClass())) {
            if ($name === 'self') {
                return $class->getName();
            }

            if ($name === 'parent' && $parent = $class->getParentClass()) {
                return $parent->getName();
            }
        }

        return $name;
    }

    /**
     * Resolve a non-class hinted primitive dependency.
     *
     * @param ReflectionParameter $parameter
     * @return mixed
     *
     * @throws DependencyResolutionException
     */
    protected function resolvePrimitive(ReflectionParameter $parameter): mixed
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->isVariadic()) {
            return [];
        }

        $message = "Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}";
        throw new DependencyResolutionException($message);
    }

    /**
     * Resolve a class based dependency from the container.
     *
     * @param ReflectionParameter $parameter
     * @return mixed
     *
     * @throws DependencyResolutionException
     */
    protected function resolveClass(ReflectionParameter $parameter): mixed
    {
        try {
            return $this->make($this->getParameterClassName($parameter));
        }
            // If we can not resolve the class instance, we will check to see if the value
            // is optional, and if it is we will return the optional parameter value as
            // the value of the dependency, similarly to how we do this with scalars.
        catch (DependencyResolutionException $e) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            if ($parameter->isVariadic()) {
                return [];
            }

            throw $e;
        }
    }

    /**
     * Get the Closure to be used when building a type.
     *
     * @param string $class
     * @param array $arguments
     * @return Closure
     */
    protected function getFactory(\ReflectionClass $class, array $arguments)
    {
        return function () use ($class, $arguments) {
            return $class->newInstanceArgs($arguments);
        };
    }
}
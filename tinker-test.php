<?php


class A {
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName($instance) {
        echo "[$instance->name]\n";
    }
}

$a = new A("A");
$b = new A("B");

$a->getName($a);
$a->getName($b);
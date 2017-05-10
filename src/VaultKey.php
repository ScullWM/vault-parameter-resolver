<?php

namespace IED\VaultParameterResolver;

class VaultKey
{
    private $namespace;
    private $field;

    public function __construct($namespace, $field = null)
    {
        $this->namespace = $namespace;
        $this->field     = $field;
    }

    public static function createFromString($str)
    {
        $data = explode('#', $str);

        if (count($data) != 2) {
            throw new \InvalidArgumentException('Vault key is malformed, should be namespace#field.');
        }

        return new static($data[0], $data[1]);
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getField()
    {
        return $this->field;
    }
}

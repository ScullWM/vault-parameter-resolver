<?php

namespace IED\VaultParameterResolver\Configuration;

use IED\VaultParameterResolver\Auth\BackendInterface as AuthBackendInterface;

class Configuration
{
    private $authBackend;

    public function __construct(AuthBackendInterface $authBackend)
    {
        $this->authBackend = $authBackend;
    }

    public function getAuthBackend()
    {
        return $this->authBackend;
    }
}

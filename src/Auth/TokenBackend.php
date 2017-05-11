<?php

namespace IED\VaultParameterResolver\Auth;

class TokenBackend implements BackendInterface
{
    public function __construct($token)
    {
        $this->token = $token;
    }

    public function generateToken()
    {
        return $this->token;
    }
}

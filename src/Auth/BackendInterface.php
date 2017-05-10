<?php

namespace IED\VaultParameterResolver\Auth;

use IED\VaultParameterResolver\VaultKey;

interface BackendInterface
{
    public function resolve(VaultKey $key);
}

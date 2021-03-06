<?php

namespace IED\VaultParameterResolver\Gateway;

use IED\VaultParameterResolver\VaultKey;

interface GatewayInterface
{
    public function resolve(VaultKey $key);

    public function write(VaultKey $key, $value);
}

<?php

namespace IED\VaultParameterResolver\Configuration;

use IED\VaultParameterResolver\Gateway\GatewayInterface;

class Configuration
{
    private $gateway;

    public function __construct(GatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    public function getGateway()
    {
        return $this->gateway;
    }
}

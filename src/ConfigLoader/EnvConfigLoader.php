<?php

namespace IED\VaultParameterResolver\ConfigLoader;

use IED\VaultParameterResolver\Auth\TokenBackend;
use IED\VaultParameterResolver\Configuration\Configuration;
use IED\VaultParameterResolver\Gateway\HttpGateway;
use Symfony\Component\Config\Definition\Processor;

class EnvConfigLoader
{
    /**
     * Look at vault documentation: https://www.vaultproject.io/docs/commands/environment.html
     *
     * 2 environment variables are at this moment supported:
     * - VAULT_ADDR
     * - VAULT_TOKEN
     *
     * @return Configuration
     */
    public static function load()
    {
        $host  = getenv('VAULT_ADDR');
        $token = getenv('VAULT_TOKEN');

        if (false === $host || false === $token) {
            throw new \InvalidArgumentException('You need VAULT_ADDR & VAULT_TOKEN env variables to use EnvConfigLoader.');
        }

        return new Configuration(
            new HttpGateway($host, new TokenBackend($token))
        );
    }
}

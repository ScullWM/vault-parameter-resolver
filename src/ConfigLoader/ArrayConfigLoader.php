<?php

namespace IED\VaultParameterResolver\ConfigLoader;

use IED\VaultParameterResolver\Auth\AppRoleBackend;
use IED\VaultParameterResolver\Configuration\Configuration;
use IED\VaultParameterResolver\Gateway\HttpGateway;
use Symfony\Component\Config\Definition\Processor;

class ArrayConfigLoader
{
    public static function load($config)
    {
        if (false === is_array($config)) {
            throw new \InvalidArgumentException(sprintf('%s expects an array as 1st argument', __CLASS__));
        }

        $processor = new Processor();
        $config    = $processor->processConfiguration(new ConfigurationTreeBuilder(), $config);

        $host    = static::resolveParameter($config['host']);

        if (array_key_exists('app_role', $config['auth'])) {
            $backend = static::configureAppRoleBackend($config['auth']['app_role'], $host);
        }

        return new Configuration(
            new HttpGateway($config['host'], $backend)
        );
    }

    private static function configureAppRoleBackend(array $properties, $host)
    {
        return new AppRoleBackend(
            $host,
            static::resolveParameter($properties['role_id']),
            static::resolveParameter($properties['secret_id'])
        );
    }

    private static function resolveParameter($parameter)
    {
        if (0 === strpos($parameter, '%env(') && ')%' === substr($parameter, -2) && 'env()' !== $parameter) {
            $env         = substr($parameter, 5, -2);
            $envResolved = getenv($env);

            if (false === $envResolved) {
                throw new \InvalidArgumentException(sprintf('Environment variable "%s" unknown', $env));
            }

            return $envResolved;
        }
        return $parameter;
    }
}

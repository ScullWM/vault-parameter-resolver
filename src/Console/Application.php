<?php

namespace IED\VaultParameterResolver\Console;

use IED\VaultParameterResolver\VaultParameterResolver;
use Symfony\Component\Console\Application as BaseApplication;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Application extends BaseApplication
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        error_reporting(-1);

        parent::__construct('Vault Parameter Resolver', VaultParameterResolver::VERSION);

        $this->add(new Command\CompileCommand());
        $this->add(new Command\ResolverCommand());
    }
}

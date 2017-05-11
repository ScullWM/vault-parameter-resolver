<?php

namespace IED\VaultParameterResolver\Auth;

use IED\VaultParameterResolver\VaultKey;

/**
 * BackendInterface
 *
 * @author Stephane PY <s.py@xeonys.com>
 */
interface BackendInterface
{
    /**
     * @return string
     */
    public function generateToken();
}

<?php

namespace IED\VaultParameterResolver\Exception;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;

class VaultExceptionFactory
{
    public static function create(\Exception $e)
    {
        if (false === $e instanceof RequestException) {
            return $e;
        }

        if ($e instanceof ConnectException) {
            return new VaultServerConnectionRefusedException($e->getMessage(), 0, $e);
        }

        if ($e->getResponse()->getStatusCode() === 403) {
            throw new AccessDeniedException($e->getMessage(), 0, $e);
        }

        return new VaultException($e->getMessage(), 0, $e);
    }
}

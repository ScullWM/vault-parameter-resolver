<?php

namespace IED\VaultParameterResolver\Gateway;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use IED\VaultParameterResolver\Auth\BackendInterface;
use IED\VaultParameterResolver\Exception\NotFoundVaultKeyException;
use IED\VaultParameterResolver\Exception\UnexpectedVaultResponseException;
use IED\VaultParameterResolver\Exception\VaultException;
use IED\VaultParameterResolver\Exception\VaultExceptionFactory;
use IED\VaultParameterResolver\VaultKey;

class HttpGateway implements GatewayInterface
{
    private $host;
    private $backend;
    private $currentToken;
    private $keyPool = [];

    public function __construct($host, BackendInterface $backend)
    {
        $this->host = $host;
        $this->backend = $backend;
    }

    public function resolve(VaultKey $key)
    {
        if (null === $this->currentToken) {
            $this->generateToken();
        }

        if (false === array_key_exists($key->getNamespace(), $this->keyPool)) {
            $request = new Request(
                'GET',
                $this->host.'/v1/'.$key->getNamespace(),
                ['X-Vault-Token' => $this->currentToken]
            );

            try {
                $response = (new HttpClient())->send($request);
            } catch (\Exception $e) {
                throw VaultExceptionFactory::create($e);
            }

            $body = json_decode($response->getBody(), true);

            if (false === array_key_exists('data', $body)) {
                throw new UnexpectedVaultResponseException(sprintf('Cannot parse “data“ in body, %s', $response->getBody()));
            }

            $this->keyPool[$key->getNamespace()] = $body['data'];
        }

        if (false === array_key_exists($key->getField(), $this->keyPool[$key->getNamespace()])) {
            throw new NotFoundVaultKeyException(sprintf('Key %s not found in namespace %s', $key->getField(), $key->getNamespace()));
        }

        return $this->keyPool[$key->getNamespace()][$key->getField()];
    }

    public function write(VaultKey $key, $value)
    {
        if (null === $this->currentToken) {
            $this->generateToken();
        }

        $request = new Request(
            'POST',
            $this->host.'/v1/'.$key->getNamespace(),
            ['X-Vault-Token' => $this->currentToken],
            json_encode([$key->getField() => $value])
        );

        try {
            (new HttpClient())->send($request);
        } catch (\Exception $e) {
            throw VaultExceptionFactory::create($e);
        }
    }

    private function generateToken()
    {
        $this->currentToken = $this->backend->generateToken();

        if (null === $this->currentToken) {
            throw new VaultException('Vault Token cannot be generated.');
        }
    }
}

<?php

namespace IED\VaultParameterResolver\Auth;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use IED\VaultParameterResolver\Exception\NotFoundVaultKeyException;
use IED\VaultParameterResolver\Exception\UnexpectedVaultResponseException;
use IED\VaultParameterResolver\Exception\VaultExceptionFactory;
use IED\VaultParameterResolver\VaultKey;

class AppRoleBackend implements BackendInterface
{
    private $host;
    private $roleId;
    private $secretId;

    private $vaultToken;
    private $keyPool = [];

    public function __construct($host, $roleId, $secretId)
    {
        $this->host     = $host;
        $this->roleId   = $roleId;
        $this->secretId = $secretId;
    }

    public function resolve(VaultKey $key)
    {
        if (null === $this->vaultToken) {
            $this->initializeVaultToken();
        }

        if (false === array_key_exists($key->getNamespace(), $this->keyPool)) {
            $request = new Request(
                'GET',
                $this->host.'/v1/'.$key->getNamespace(),
                ["X-Vault-Token" => $this->vaultToken]
            );

            $response = $this->doRequest($request);
            // @todo if there is an exception, we could check if token has just expired and try to renew it.
            $body     = json_decode($response->getBody(), true);

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

    private function initializeVaultToken()
    {
        $request = new Request(
            'POST',
            $this->host.'/v1/auth/approle/login',
            [],
            json_encode(['role_id' => $this->roleId, 'secret_id' => $this->secretId])
        );

        $response = $this->doRequest($request);
        $body     = json_decode($response->getBody(), true);

        if (false === isset($body['auth']) || false === isset($body['auth']['client_token'])) {
            throw new UnexpectedVaultResponseException(sprintf('Cannot parse “auth->client_token“ in body, %s', $response->getBody()));
        }

        $this->vaultToken = $body['auth']['client_token'];
    }

    private function doRequest(Request $request)
    {
        $client = new HttpClient();

        try {
            return $client->send($request);
        } catch (\Exception $e) {
            throw VaultExceptionFactory::create($e);
        }
    }
}

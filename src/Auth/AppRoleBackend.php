<?php

namespace IED\VaultParameterResolver\Auth;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use IED\VaultParameterResolver\Exception\UnexpectedVaultResponseException;
use IED\VaultParameterResolver\Exception\VaultExceptionFactory;

class AppRoleBackend implements BackendInterface
{
    private $host;
    private $roleId;
    private $secretId;

    public function __construct($host, $roleId, $secretId)
    {
        $this->host = $host;
        $this->roleId = $roleId;
        $this->secretId = $secretId;
    }

    public function generateToken()
    {
        $client = new HttpClient();

        $request = new Request(
            'POST',
            $this->host.'/v1/auth/approle/login',
            [],
            json_encode(['role_id' => $this->roleId, 'secret_id' => $this->secretId])
        );

        try {
            $response = $client->send($request);
        } catch (\Exception $e) {
            throw VaultExceptionFactory::create($e);
        }
        $body = json_decode($response->getBody(), true);

        if (false === isset($body['auth']) || false === isset($body['auth']['client_token'])) {
            throw new UnexpectedVaultResponseException(sprintf('Cannot parse “auth->client_token“ in body, %s', $response->getBody()));
        }

        return $body['auth']['client_token'];
    }
}

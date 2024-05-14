<?php

namespace TestMonitor\DevOps;

use Psr\Http\Message\ResponseInterface;
use TestMonitor\DevOps\Exceptions\Exception;
use TheNetworg\OAuth2\Client\Provider\Azure;
use TestMonitor\DevOps\Exceptions\NotFoundException;
use TestMonitor\DevOps\Exceptions\ValidationException;
use TestMonitor\DevOps\Exceptions\FailedActionException;
use TestMonitor\DevOps\Exceptions\TokenExpiredException;
use TestMonitor\DevOps\Exceptions\UnauthorizedException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class Client
{
    use Actions\ManagesAccounts,
        Actions\ManagesAttachments,
        Actions\ManagesProjects,
        Actions\ManagesStates,
        Actions\ManagesTags,
        Actions\ManagesTeams,
        Actions\ManagesWebhooks,
        Actions\ManagesWorkItems,
        Actions\ManagesWorkItemTypes;

    /**
     * @var \TestMonitor\DevOps\AccessToken
     */
    protected $token;

    /**
     * @var string
     */
    protected $organization;

    /**
     * @var string
     */
    protected $baseUrl = 'https://dev.azure.com';

    /**
     * @var string
     */
    protected $apiVersion = '7.0';

    /**
     * @var string
     */
    protected $previewApiVersion = '7.0-preview.1';

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var \TheNetworg\OAuth2\Client\Provider\Azure
     */
    protected $provider;

    /**
     * Create a new client instance.
     *
     * @param array $credentials
     * @param \TestMonitor\DevOps\AccessToken $token
     * @param string $organization
     * @param \TheNetworg\OAuth2\Client\Provider\Azure $provider
     */
    public function __construct(
        array $credentials,
        string $organization = '',
        AccessToken $token = null,
        Azure $provider = null
    ) {
        $this->token = $token;
        $this->organization = $organization;

        $this->provider = $provider ?? new Azure([
            'clientId' => $credentials['clientId'],
            'clientSecret' => $credentials['clientSecret'],
            'redirectUri' => $credentials['redirectUrl'],
            'scopes' => [
                'offline_access',
                "{$credentials['appId']}/.default",
            ],
            'defaultEndPointVersion' => '2.0',
        ]);
    }

    /**
     * Create a new authorization URL for the given state.
     *
     * @param string $state
     * @return string
     */
    public function authorizationUrl($state)
    {
        return $this->provider->getAuthorizationUrl(['state' => $state]);
    }

    /**
     * Fetch the access and refresh token based on the authorization code.
     *
     * @param string $code
     * @return \TestMonitor\DevOps\AccessToken
     */
    public function fetchToken(string $code): AccessToken
    {
        $token = $this->provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);

        $this->token = AccessToken::fromDevOps($token);

        return $this->token;
    }

    /**
     * Refresh the current access token.
     *
     * @throws \TestMonitor\DevOps\Exceptions\UnauthorizedException
     *
     * @return \TestMonitor\DevOps\AccessToken
     */
    public function refreshToken(): ?AccessToken
    {
        if (empty($this->token)) {
            throw new UnauthorizedException();
        }

        try {
            $token = $this->provider->getAccessToken('refresh_token', [
                'refresh_token' => $this->token->refreshToken,
            ]);

            $this->token = AccessToken::fromDevOps($token);
        } catch (IdentityProviderException $exception) {
            throw new UnauthorizedException((string) $exception->getResponseBody(), $exception->getCode(), $exception);
        }

        return $this->token;
    }

    /**
     * Determines if the current access token has expired.
     *
     * @return bool
     */
    public function tokenExpired()
    {
        return $this->token->expired();
    }

    /**
     * Returns an Guzzle client instance.
     *
     * @throws \TestMonitor\DevOps\Exceptions\UnauthorizedException
     * @throws \TestMonitor\DevOps\Exceptions\TokenExpiredException
     *
     * @return \GuzzleHttp\Client
     */
    protected function client()
    {
        if (empty($this->token)) {
            throw new UnauthorizedException();
        }

        if ($this->token->expired()) {
            throw new TokenExpiredException();
        }

        return $this->client ?? new \GuzzleHttp\Client([
            'base_uri' => $this->baseUrl . '/' . $this->organization . '/',
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->accessToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'query' => [
                'api-version' => $this->apiVersion,
            ],
        ]);
    }

    /**
     * @param \GuzzleHttp\Client $client
     */
    public function setClient(\GuzzleHttp\Client $client)
    {
        $this->client = $client;
    }

    /**
     * Make a GET request to DevOps servers and return the response.
     *
     * @param string $uri
     * @param array $payload
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \TestMonitor\DevOps\Exceptions\FailedActionException
     * @throws \TestMonitor\DevOps\Exceptions\NotFoundException
     * @throws \TestMonitor\DevOps\Exceptions\TokenExpiredException
     * @throws \TestMonitor\DevOps\Exceptions\UnauthorizedException
     * @throws \TestMonitor\DevOps\Exceptions\ValidationException
     *
     * @return mixed
     */
    protected function get($uri, array $payload = [])
    {
        return $this->request('GET', $uri, $payload);
    }

    /**
     * Make a POST request to DevOps servers and return the response.
     *
     * @param string $uri
     * @param array $payload
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \TestMonitor\DevOps\Exceptions\FailedActionException
     * @throws \TestMonitor\DevOps\Exceptions\NotFoundException
     * @throws \TestMonitor\DevOps\Exceptions\TokenExpiredException
     * @throws \TestMonitor\DevOps\Exceptions\UnauthorizedException
     * @throws \TestMonitor\DevOps\Exceptions\ValidationException
     *
     * @return mixed
     */
    protected function post($uri, array $payload = [])
    {
        return $this->request('POST', $uri, $payload);
    }

    /**
     * Make a PUT request to DevOps servers and return the response.
     *
     * @param string $uri
     * @param array $payload
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \TestMonitor\DevOps\Exceptions\FailedActionException
     * @throws \TestMonitor\DevOps\Exceptions\NotFoundException
     * @throws \TestMonitor\DevOps\Exceptions\TokenExpiredException
     * @throws \TestMonitor\DevOps\Exceptions\UnauthorizedException
     * @throws \TestMonitor\DevOps\Exceptions\ValidationException
     *
     * @return mixed
     */
    protected function patch($uri, array $payload = [])
    {
        return $this->request('PATCH', $uri, $payload);
    }

    /**
     * Make a DELETE request to DevOps servers and return the response.
     *
     * @param string $uri
     * @param array $payload
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \TestMonitor\DevOps\Exceptions\FailedActionException
     * @throws \TestMonitor\DevOps\Exceptions\NotFoundException
     * @throws \TestMonitor\DevOps\Exceptions\TokenExpiredException
     * @throws \TestMonitor\DevOps\Exceptions\UnauthorizedException
     * @throws \TestMonitor\DevOps\Exceptions\ValidationException
     *
     * @return mixed
     */
    protected function delete($uri, array $payload = [])
    {
        return $this->request('DELETE', $uri, $payload);
    }

    /**
     * Make request to DevOps servers and return the response.
     *
     * @param string $verb
     * @param string $uri
     * @param array $payload
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \TestMonitor\DevOps\Exceptions\FailedActionException
     * @throws \TestMonitor\DevOps\Exceptions\NotFoundException
     * @throws \TestMonitor\DevOps\Exceptions\TokenExpiredException
     * @throws \TestMonitor\DevOps\Exceptions\UnauthorizedException
     * @throws \TestMonitor\DevOps\Exceptions\ValidationException
     *
     * @return mixed
     */
    protected function request($verb, $uri, array $payload = [])
    {
        $response = $this->client()->request(
            $verb,
            $uri,
            $payload
        );

        if (! in_array($response->getStatusCode(), [200, 201, 203, 204, 206])) {
            return $this->handleRequestError($response);
        }

        $responseBody = (string) $response->getBody();

        return json_decode($responseBody, true) ?: $responseBody;
    }

    /**
     * @param  \Psr\Http\Message\ResponseInterface $response
     *
     * @throws \TestMonitor\DevOps\Exceptions\ValidationException
     * @throws \TestMonitor\DevOps\Exceptions\NotFoundException
     * @throws \TestMonitor\DevOps\Exceptions\FailedActionException
     * @throws \Exception
     *
     * @return void
     */
    protected function handleRequestError(ResponseInterface $response)
    {
        if ($response->getStatusCode() == 422) {
            throw new ValidationException(json_decode((string) $response->getBody(), true));
        }

        if ($response->getStatusCode() == 404 ||
            $response->getStatusCode() == 400 ||
            $response->getStatusCode() == 409) {
            throw new NotFoundException((string) $response->getBody(), $response->getStatusCode());
        }

        if ($response->getStatusCode() == 401 || $response->getStatusCode() == 403) {
            throw new UnauthorizedException((string) $response->getBody(), $response->getStatusCode());
        }

        if ($response->getStatusCode() == 503) {
            throw new FailedActionException((string) $response->getBody(), $response->getStatusCode());
        }

        throw new Exception((string) $response->getStatusCode());
    }
}

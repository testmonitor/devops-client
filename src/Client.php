<?php

namespace TestMonitor\DevOps;

use Psr\Http\Message\ResponseInterface;
use TestMonitor\DevOps\Exceptions\Exception;
use Jeylabs\OAuth2\Client\Provider\VSTSProvider;
use TestMonitor\DevOps\Exceptions\NotFoundException;
use TestMonitor\DevOps\Exceptions\ValidationException;
use TestMonitor\DevOps\Exceptions\FailedActionException;
use TestMonitor\DevOps\Exceptions\TokenExpiredException;
use TestMonitor\DevOps\Exceptions\UnauthorizedException;

class Client
{
    use Actions\ManagesAttachments,
        Actions\ManagesWorkItems,
        Actions\ManagesProjects,
        Actions\ManagesAccounts,
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
    protected $apiVersion = '5.0';

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var VSTSProvider
     */
    protected $provider;

    /**
     * Create a new client instance.
     *
     * @param array $credentials
     * @param \TestMonitor\DevOps\AccessToken $token
     * @param string $organization
     * @param \Jeylabs\OAuth2\Client\Provider\VSTSProvider $provider
     */
    public function __construct(
        array $credentials,
        string $organization,
        AccessToken $token = null,
        VSTSProvider $provider = null
    ) {
        $this->token = $token;
        $this->organization = $organization;

        $this->provider = $provider ?? new VSTSProvider([
            'clientId' => $credentials['clientId'],
            'clientSecret' => $credentials['clientSecret'],
            'redirectUri' => $credentials['redirectUrl'],
            'urlAuthorize' => $credentials['authorizeUrl'] ?? 'https://app.vssps.visualstudio.com/oauth2/authorize',
            'urlAccessToken' => $credentials['accessTokenUrl'] ?? 'https://app.vssps.visualstudio.com/oauth2/token',
            'urlResourceOwnerDetails' => $credentials['resourceOwnerDetailsUrl'] ??
                'https://app.vssps.visualstudio.com/oauth2/token/resource',
            'responseType' => 'Assertion',
            'scopes' => 'vso.project vso.work_full',
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
        return $this->provider->getAuthorizationUrl(compact($state));
    }

    /**
     * Fetch the access and refresh token based on the authorization code.
     *
     * @param string $code
     *
     * @return \TestMonitor\DevOps\AccessToken
     */
    public function fetchToken(string $code): AccessToken
    {
        $token = $this->provider->getAccessToken('jwt_bearer', [
            'assertion' => $code,
        ]);

        $this->token = AccessToken::fromDevOps($token);

        return $this->token;
    }

    /**
     * Refresh the current access token.
     *
     * @throws \Exception
     * @return \TestMonitor\DevOps\AccessToken
     */
    public function refreshToken(): AccessToken
    {
        $token = $this->provider->getAccessToken('jwt_bearer', [
            'grant_type' => 'refresh_token',
            'assertion' => $this->token->refreshToken,
        ]);

        $this->token = AccessToken::fromDevOps($token);

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
     *@throws TokenExpiredException
     * @return \GuzzleHttp\Client
     */
    protected function client()
    {
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
     *
     * @param array $payload
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \TestMonitor\DevOps\Exceptions\FailedActionException
     * @throws \TestMonitor\DevOps\Exceptions\NotFoundException
     * @throws \TestMonitor\DevOps\Exceptions\TokenExpiredException
     * @throws \TestMonitor\DevOps\Exceptions\ValidationException
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
     * @throws \TestMonitor\DevOps\Exceptions\ValidationException
     * @return mixed
     */
    protected function post($uri, array $payload = [])
    {
        return $this->request('POST', $uri, $payload);
    }

    /**
     * Make a PUT request to Forge servers and return the response.
     *
     * @param string $uri
     * @param array $payload
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \TestMonitor\DevOps\Exceptions\FailedActionException
     * @throws \TestMonitor\DevOps\Exceptions\NotFoundException
     * @throws \TestMonitor\DevOps\Exceptions\TokenExpiredException
     * @throws \TestMonitor\DevOps\Exceptions\ValidationException
     * @return mixed
     */
    protected function patch($uri, array $payload = [])
    {
        return $this->request('PATCH', $uri, $payload);
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
     * @throws \TestMonitor\DevOps\Exceptions\ValidationException
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
     * @return void
     */
    protected function handleRequestError(ResponseInterface $response)
    {
        if ($response->getStatusCode() == 422) {
            throw new ValidationException(json_decode((string) $response->getBody(), true));
        }

        if ($response->getStatusCode() == 404) {
            throw new NotFoundException();
        }

        if ($response->getStatusCode() == 401 || $response->getStatusCode() == 403) {
            throw new UnauthorizedException();
        }

        if ($response->getStatusCode() == 400) {
            throw new FailedActionException((string) $response->getBody());
        }

        throw new Exception((string) $response->getStatusCode());
    }
}

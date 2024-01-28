<?php

namespace TestMonitor\DevOps\Tests;

use Mockery;
use TestMonitor\DevOps\Client;
use PHPUnit\Framework\TestCase;
use TestMonitor\DevOps\AccessToken;
use TestMonitor\DevOps\Exceptions\TokenExpiredException;
use TestMonitor\DevOps\Exceptions\UnauthorizedException;

class OauthTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_should_create_a_token()
    {
        // When
        $token = new AccessToken('12345', '67890', time() + 3600);

        // Then
        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertIsArray($token->toArray());
        $this->assertFalse($token->expired());
    }

    /** @test */
    public function it_should_detect_an_expired_token()
    {
        // Given
        $token = new AccessToken('12345', '67890', time() - 60);

        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        // When
        $expired = $devops->tokenExpired();

        // Then
        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertTrue($token->expired());
        $this->assertTrue($expired);
    }

    /** @test */
    public function it_should_not_provide_a_client_with_an_expired_token()
    {
        // Given
        $token = new AccessToken('12345', '67890', time() - 60);

        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $this->expectException(TokenExpiredException::class);

        // When
        $devops = $devops->accounts();
    }

    /** @test */
    public function it_should_provide_an_authorization_url()
    {
        // Given
        $dispatcher = Mockery::mock('\TheNetworg\OAuth2\Client\Provider\Azure');
        $state = 'somestate';

        $dispatcher->shouldReceive('getAuthorizationUrl')->with(['state' => $state])->andReturn('https://devops.authorization.url');

        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', new AccessToken(), $dispatcher);

        // When
        $url = $devops->authorizationUrl($state);

        // Then
        $this->assertEquals('https://devops.authorization.url', $url);
    }

    /** @test */
    public function it_should_fetch_a_token()
    {
        // Given
        $dispatcher = Mockery::mock('\TheNetworg\OAuth2\Client\Provider\Azure');

        $newToken = new AccessToken('12345', '567890', time() + 3600);

        $dispatcher->accessToken = $newToken->accessToken;
        $dispatcher->refreshToken = $newToken->refreshToken;
        $dispatcher->expiresIn = 3600;

        $code = 'somecode';

        $dispatcher->shouldReceive('getAccessToken')->once()->andReturn(new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => $newToken->accessToken,
            'refresh_token' => $newToken->refreshToken,
            'expires_in' => $newToken->expiresIn,
        ]));

        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', new AccessToken(), $dispatcher);

        // When
        $token = $devops->fetchToken($code);

        // Then
        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertFalse($token->expired());
        $this->assertEquals($token->accessToken, $newToken->accessToken);
        $this->assertEquals($token->refreshToken, $newToken->refreshToken);
    }

    /** @test */
    public function it_should_refresh_a_token()
    {
        // Given
        $dispatcher = Mockery::mock('\TheNetworg\OAuth2\Client\Provider\Azure');

        $oldToken = new AccessToken('12345', '567890', time() - 3600);

        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $oldToken, $dispatcher);

        $newToken = new AccessToken('23456', '678901', time() + 3600);

        $dispatcher->accessToken = $newToken->accessToken;
        $dispatcher->refreshToken = $newToken->refreshToken;
        $dispatcher->expiresIn = 3600;

        $dispatcher->shouldReceive('getAccessToken')->once()->andReturn(new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => $newToken->accessToken,
            'refresh_token' => $newToken->refreshToken,
            'expires_in' => $newToken->expiresIn,
        ]));

        // When
        $token = $devops->refreshToken();

        // Then
        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertFalse($token->expired());
        $this->assertEquals($token->accessToken, $newToken->accessToken);
        $this->assertEquals($token->refreshToken, $newToken->refreshToken);
    }

    /** @test */
    public function it_should_not_refresh_a_token_without_a_refresh_token()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg');

        $this->expectException(UnauthorizedException::class);

        // When
        $devops->refreshToken();
    }

    /** @test */
    public function it_should_not_provide_a_client_without_a_token()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg');

        $this->expectException(UnauthorizedException::class);

        // When
        $devops->accounts();
    }
}

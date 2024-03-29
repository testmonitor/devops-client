<?php

namespace TestMonitor\DevOps\Tests;

use Mockery;
use Exception;
use GuzzleHttp\Psr7\Response;
use TestMonitor\DevOps\Client;
use PHPUnit\Framework\TestCase;
use TestMonitor\DevOps\Resources\Tag;
use TestMonitor\DevOps\Exceptions\NotFoundException;
use TestMonitor\DevOps\Exceptions\ValidationException;
use TestMonitor\DevOps\Exceptions\FailedActionException;
use TestMonitor\DevOps\Exceptions\UnauthorizedException;

class TagsTest extends TestCase
{
    protected $token;

    protected $tag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->token = Mockery::mock('\TestMonitor\DevOps\AccessToken');
        $this->token->shouldReceive('expired')->andReturnFalse();

        $this->tag = ['id' => '1', 'name' => 'Tagged'];
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_should_return_a_list_of_tags()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode(['value' => [$this->tag]])));

        // When
        $tags = $devops->tags(1);

        // Then
        $this->assertIsArray($tags);
        $this->assertCount(1, $tags);
        $this->assertInstanceOf(Tag::class, $tags[0]);
        $this->assertEquals($this->tag['id'], $tags[0]->id);
        $this->assertIsArray($tags[0]->toArray());
    }

    /** @test */
    public function it_should_throw_a_failed_action_exception_when_client_receives_bad_request_while_getting_a_list_of_tags()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(400, ['Content-Type' => 'application/json'], null));

        $this->expectException(FailedActionException::class);

        // When
        $devops->tags(1);
    }

    /** @test */
    public function it_should_throw_a_notfound_exception_when_client_receives_not_found_while_getting_a_list_of_tags()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(404, ['Content-Type' => 'application/json'], null));

        $this->expectException(NotFoundException::class);

        // When
        $devops->tags(1);
    }

    /** @test */
    public function it_should_throw_a_unauthorized_exception_when_client_lacks_authorization_for_getting_a_list_of_tags()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(401, ['Content-Type' => 'application/json'], null));

        $this->expectException(UnauthorizedException::class);

        // When
        $devops->tags(1);
    }

    /** @test */
    public function it_should_throw_a_validation_exception_when_client_provides_invalid_data_while_getting_a_list_of_tags()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(422, ['Content-Type' => 'application/json'], json_encode(['message' => 'invalid'])));

        $this->expectException(ValidationException::class);

        // When
        $devops->tags(1);
    }

    /** @test */
    public function it_should_return_an_error_message_when_client_provides_invalid_data_while_getting_a_list_of_tags()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(422, ['Content-Type' => 'application/json'], json_encode(['errors' => ['invalid']])));

        // When
        try {
            $devops->tags(1);
        } catch (ValidationException $exception) {
            // Then
            $this->assertIsArray($exception->errors());
            $this->assertEquals('invalid', $exception->errors()['errors'][0]);
        }
    }

    /** @test */
    public function it_should_throw_a_generic_exception_when_client_suddenly_becomes_a_teapot_while_getting_a_list_of_tags()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(418, ['Content-Type' => 'application/json'], json_encode(['herbal_tea' => 'anyone?'])));

        $this->expectException(Exception::class);

        // When
        $devops->tags(1);
    }
}

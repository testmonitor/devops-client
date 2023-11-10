<?php

namespace TestMonitor\DevOps\Tests;

use Mockery;
use GuzzleHttp\Psr7\Response;
use TestMonitor\DevOps\Client;
use PHPUnit\Framework\TestCase;
use TestMonitor\DevOps\Resources\Webhook;
use TestMonitor\DevOps\Exceptions\NotFoundException;
use TestMonitor\DevOps\Exceptions\ValidationException;
use TestMonitor\DevOps\Exceptions\FailedActionException;
use TestMonitor\DevOps\Exceptions\UnauthorizedException;

class WebhooksTest extends TestCase
{
    protected $token;

    protected $project;

    protected $webhook;

    protected function setUp(): void
    {
        parent::setUp();

        $this->token = Mockery::mock('\TestMonitor\DevOps\AccessToken');
        $this->token->shouldReceive('expired')->andReturnFalse();

        $this->project = ['id' => '1', 'name' => 'Project'];

        $this->webhook = [
            'id' => 1,
            "publisherId" => "tfs",
            "eventType" => 'workitem.updated',
            "resourceVersion" => "1.0-preview.1",
            "consumerId" => "webHooks",
            "consumerActionId" => "httpRequest",
            "actionDescription" => 'Webhook',
            "publisherInputs" => [
                "projectId" => '12345',
            ],
            "consumerInputs" => [
                'basicAuthUsername' => 'username',
                'basicAuthPassword' => '****',
                "url" => 'https://hooks.testmonitor.com/',
            ],
        ];
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_should_return_a_list_of_webhooks()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode(['value' => [$this->webhook]])));

        // When
        $webhooks = $devops->webhooks('workitem.updated');

        // Then
        $this->assertIsArray($webhooks);
        $this->assertCount(1, $webhooks);
        $this->assertInstanceOf(Webhook::class, $webhooks[0]);
        $this->assertEquals($this->webhook['id'], $webhooks[0]->id);
        $this->assertIsArray($webhooks[0]->toArray());
    }

    /** @test */
    public function it_should_throw_an_failed_action_exception_when_client_receives_bad_request_while_getting_a_list_of_webhooks()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(400, ['Content-Type' => 'application/json'], null));

        $this->expectException(FailedActionException::class);

        // When
        $devops->webhooks('workitem.updated');
    }

    /** @test */
    public function it_should_throw_a_notfound_exception_when_client_receives_not_found_while_getting_a_list_of_webhooks()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(404, ['Content-Type' => 'application/json'], null));

        $this->expectException(NotFoundException::class);

        // When
        $devops->webhooks('workitem.updated');
    }

    /** @test */
    public function it_should_throw_a_unauthorized_exception_when_client_lacks_authorization_for_getting_a_list_of_webhooks()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(401, ['Content-Type' => 'application/json'], null));

        $this->expectException(UnauthorizedException::class);

        // When
        $devops->webhooks('workitem.updated');
    }

    /** @test */
    public function it_should_throw_a_validation_exception_when_client_provides_invalid_data_while_a_getting_list_of_webhooks()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(422, ['Content-Type' => 'application/json'], json_encode(['message' => 'invalid'])));

        $this->expectException(ValidationException::class);

        // When
        $devops->webhooks('workitem.updated');
    }

    /** @test */
    public function it_should_create_a_webhook()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(201, ['Content-Type' => 'application/json'], json_encode($this->webhook)));

        // When
        $webhook = $devops->createWebhook(new Webhook([
            'description' => $this->webhook['actionDescription'],
            'eventType' => $this->webhook['eventType'],
            'projectId' => $this->webhook['publisherInputs']['projectId'],
            'url' => $this->webhook['consumerInputs']['url'],
            'username' => $this->webhook['consumerInputs']['basicAuthUsername'],
            'password' => $this->webhook['consumerInputs']['basicAuthPassword'],
        ]));

        // Then
        $this->assertInstanceOf(Webhook::class, $webhook);
        $this->assertEquals($this->webhook['id'], $webhook->id);
    }

    /** @test */
    public function it_should_throw_a_validation_exception_when_client_provides_invalid_data_while_creating_an_invalid_webhook()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(422, ['Content-Type' => 'application/json'], json_encode(['message' => 'invalid'])));

        $this->expectException(ValidationException::class);

        // When
        $devops->createWebhook(new Webhook([
            'url' => $this->webhook['consumerInputs']['url'],
            'eventType' => $this->webhook['eventType'],
            'username' => $this->webhook['consumerInputs']['basicAuthUsername'],
            'password' => $this->webhook['consumerInputs']['basicAuthPassword'],
        ]));
    }

    /** @test */
    public function it_should_delete_a_webhook()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(200, ['Content-Type' => 'application/json'], ''));

        // When
        $response = $devops->deleteWebhook(1);

        // Then
        $this->assertTrue($response);
    }

    /** @test */
    public function it_should_throw_a_notfound_exception_when_client_receives_not_found_while_deleting_an_unknown_webhook()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(404, ['Content-Type' => 'application/json'], null));

        $this->expectException(NotFoundException::class);

        // When
        $devops->deleteWebhook(42);
    }
}

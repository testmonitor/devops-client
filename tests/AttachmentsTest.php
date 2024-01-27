<?php

namespace TestMonitor\DevOps\Tests;

use Mockery;
use GuzzleHttp\Psr7\Response;
use TestMonitor\DevOps\Client;
use PHPUnit\Framework\TestCase;
use TestMonitor\DevOps\Resources\Attachment;
use TestMonitor\DevOps\Exceptions\NotFoundException;
use TestMonitor\DevOps\Exceptions\ValidationException;
use TestMonitor\DevOps\Exceptions\FailedActionException;
use TestMonitor\DevOps\Exceptions\UnauthorizedException;

class AttachmentsTest extends TestCase
{
    protected $token;

    protected $project;

    protected $workItem;

    protected $attachment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->token = Mockery::mock('\TestMonitor\DevOps\AccessToken');
        $this->token->shouldReceive('expired')->andReturnFalse();

        $this->project = ['id' => '1', 'name' => 'Project'];
        $this->workItem = ['id' => 1, 'fields' => []];
        $this->attachment = ['id' => 1, 'url' => 'https://attachment.url/'];
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_should_add_an_attachment_to_a_work_item()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        // First, adding an attachment...
        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($this->attachment)));

        // Second, adding the attachment to the work item
        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($this->workItem)));

        // When
        $attachment = $devops->addAttachment(__DIR__ . '/files/logo.png', $this->workItem['id'], $this->project['id']);

        // Then
        $this->assertInstanceOf(Attachment::class, $attachment);
        $this->assertEquals($this->attachment['url'], $attachment->url);
        $this->assertIsArray($attachment->toArray());
    }

    /** @test */
    public function it_should_throw_a_failed_action_exception_when_client_receives_bad_request_while_adding_an_attachment_to_a_work_item()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(400, ['Content-Type' => 'application/json'], null));

        $this->expectException(FailedActionException::class);

        // When
        $devops->addAttachment(__DIR__ . '/files/logo.png', $this->workItem['id'], $this->project['id']);
    }

    /** @test */
    public function it_should_throw_a_notfound_exception_when_client_receives_not_found_while_adding_an_attachment_to_a_work_item()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(404, ['Content-Type' => 'application/json'], null));

        $this->expectException(NotFoundException::class);

        // When
        $devops->addAttachment(__DIR__ . '/files/logo.png', $this->workItem['id'], $this->project['id']);
    }

    /** @test */
    public function it_should_throw_a_unauthorized_exception_when_client_lacks_authorization_for_adding_an_attachment_to_a_work_item()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(401, ['Content-Type' => 'application/json'], null));

        $this->expectException(UnauthorizedException::class);

        // When
        $devops->addAttachment(__DIR__ . '/files/logo.png', $this->workItem['id'], $this->project['id']);
    }

    /** @test */
    public function it_should_throw_a_validation_exception_when_client_provides_invalid_data_while_adding_an_attachment_to_a_work_item()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(422, ['Content-Type' => 'application/json'], json_encode(['message' => 'invalid'])));

        $this->expectException(ValidationException::class);

        // When
        $devops->addAttachment(__DIR__ . '/files/logo.png', $this->workItem['id'], $this->project['id']);
    }
}

<?php

namespace TestMonitor\DevOps\Tests;

use Mockery;
use GuzzleHttp\Psr7\Response;
use TestMonitor\DevOps\Client;
use PHPUnit\Framework\TestCase;
use TestMonitor\DevOps\Resources\Project;
use TestMonitor\DevOps\Exceptions\Exception;
use TestMonitor\DevOps\Exceptions\NotFoundException;
use TestMonitor\DevOps\Exceptions\ValidationException;
use TestMonitor\DevOps\Exceptions\FailedActionException;
use TestMonitor\DevOps\Exceptions\UnauthorizedException;

class ProjectsTest extends TestCase
{
    protected $token;

    protected $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->token = Mockery::mock('\TestMonitor\DevOps\AccessToken');
        $this->token->shouldReceive('expired')->andReturnFalse();

        $this->project = ['id' => '1', 'name' => 'Project'];
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_should_return_a_project()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($this->project)));

        // When
        $project = $devops->project($this->project['id']);

        // Then
        $this->assertInstanceOf(Project::class, $project);
        $this->assertEquals($this->project['id'], $project->id);
        $this->assertIsArray($project->toArray());
    }

    /** @test */
    public function it_should_throw_a_failed_action_exception_when_client_receives_bad_request_while_getting_a_project()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(400, ['Content-Type' => 'application/json'], null));

        $this->expectException(FailedActionException::class);

        // When
        $devops->project($this->project['id']);
    }

    /** @test */
    public function it_should_throw_a_notfound_exception_when_client_receives_not_found_while_getting_a_project()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(404, ['Content-Type' => 'application/json'], null));

        $this->expectException(NotFoundException::class);

        // When
        $devops->project($this->project['id']);
    }

    /** @test */
    public function it_should_throw_a_unauthorized_exception_when_client_lacks_authorization_for_getting_a_project()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(401, ['Content-Type' => 'application/json'], null));

        $this->expectException(UnauthorizedException::class);

        // When
        $devops->project($this->project['id']);
    }

    /** @test */
    public function it_should_return_a_list_of_projects()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode(['value' => [$this->project]])));

        // When
        $projects = $devops->projects();

        // Then
        $this->assertIsArray($projects);
        $this->assertCount(1, $projects);
        $this->assertInstanceOf(Project::class, $projects[0]);
        $this->assertEquals($this->project['id'], $projects[0]->id);
        $this->assertIsArray($projects[0]->toArray());
    }

    /** @test */
    public function it_should_throw_a_failed_action_exception_when_client_receives_bad_request_while_getting_a_list_of_projects()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(400, ['Content-Type' => 'application/json'], null));

        $this->expectException(FailedActionException::class);

        // When
        $devops->projects();
    }

    /** @test */
    public function it_should_throw_a_notfound_exception_when_client_receives_not_found_while_getting_a_list_of_projects()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(404, ['Content-Type' => 'application/json'], null));

        $this->expectException(NotFoundException::class);

        // When
        $devops->projects();
    }

    /** @test */
    public function it_should_throw_a_unauthorized_exception_when_client_lacks_authorization_for_getting_a_list_of_projects()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(401, ['Content-Type' => 'application/json'], null));

        $this->expectException(UnauthorizedException::class);

        // When
        $devops->projects();
    }

    /** @test */
    public function it_should_throw_a_validation_exception_when_client_provides_invalid_data_while_getting_a_list_of_projects()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(422, ['Content-Type' => 'application/json'], json_encode(['message' => 'invalid'])));

        $this->expectException(ValidationException::class);

        // When
        $devops->projects();
    }

    /** @test */
    public function it_should_return_an_error_message_when_client_provides_invalid_data_while_getting_a_list_of_projects()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(422, ['Content-Type' => 'application/json'], json_encode(['errors' => ['invalid']])));

        // When
        try {
            $devops->projects();
        } catch (ValidationException $exception) {
            // Then
            $this->assertIsArray($exception->errors());
            $this->assertEquals('invalid', $exception->errors()['errors'][0]);
        }
    }

    /** @test */
    public function it_should_throw_a_generic_exception_when_client_suddenly_becomes_a_teapot_while_getting_a_list_of_projects()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'appId' => 1, 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(418, ['Content-Type' => 'application/json'], json_encode(['rooibos' => 'anyone?'])));

        $this->expectException(Exception::class);

        // When
        $devops->projects();
    }
}

<?php

namespace TestMonitor\DevOps\Tests;

use Mockery;
use TestMonitor\DevOps\Client;
use PHPUnit\Framework\TestCase;
use TestMonitor\DevOps\Resources\WorkItemType;
use TestMonitor\DevOps\Exceptions\NotFoundException;
use TestMonitor\DevOps\Exceptions\ValidationException;
use TestMonitor\DevOps\Exceptions\FailedActionException;
use TestMonitor\DevOps\Exceptions\UnauthorizedException;

class WorkItemTypesTest extends TestCase
{
    protected $token;

    protected $project;

    protected $workItemType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->token = Mockery::mock('\TestMonitor\DevOps\AccessToken');
        $this->token->shouldReceive('expired')->andReturnFalse();

        $this->project = ['id' => '1', 'name' => 'Project'];

        $this->workItemType = ['name' => 'Type', 'description' => 'Description'];
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_should_return_a_list_of_work_item_types()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $response = Mockery::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getBody')->andReturn(json_encode(['value' => [$this->workItemType]]));

        $service->shouldReceive('request')->once()->andReturn($response);

        // When
        $workItemTypes = $devops->workItemTypes($this->project['id']);

        // Then
        $this->assertIsArray($workItemTypes);
        $this->assertCount(1, $workItemTypes);
        $this->assertInstanceOf(WorkItemType::class, $workItemTypes[0]);
        $this->assertEquals($this->workItemType['name'], $workItemTypes[0]->name);
        $this->assertIsArray($workItemTypes[0]->toArray());
    }

    /** @test */
    public function it_should_throw_an_failed_action_exception_when_client_receives_bad_request_while_getting_a_list_of_work_item_types()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andReturn($response = Mockery::mock('Psr\Http\Message\ResponseInterface'));
        $response->shouldReceive('getStatusCode')->andReturn(400);
        $response->shouldReceive('getBody')->andReturnNull();

        $this->expectException(FailedActionException::class);

        // When
        $devops->projects();
    }

    /** @test */
    public function it_should_throw_a_notfound_exception_when_client_receives_not_found_while_getting_a_list_of_work_item_types()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andReturn($response = Mockery::mock('Psr\Http\Message\ResponseInterface'));
        $response->shouldReceive('getStatusCode')->andReturn(404);
        $response->shouldReceive('getBody')->andReturnNull();

        $this->expectException(NotFoundException::class);

        // When
        $devops->projects();
    }

    /** @test */
    public function it_should_throw_a_unauthorized_exception_when_client_lacks_authorization_for_getting_a_list_of_work_item_types()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andReturn($response = Mockery::mock('Psr\Http\Message\ResponseInterface'));
        $response->shouldReceive('getStatusCode')->andReturn(401);
        $response->shouldReceive('getBody')->andReturnNull();

        $this->expectException(UnauthorizedException::class);

        // When
        $devops->projects();
    }

    /** @test */
    public function it_should_throw_a_validation_exception_when_client_provides_invalid_data_while_a_getting_list_of_work_item_types()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andReturn($response = Mockery::mock('Psr\Http\Message\ResponseInterface'));
        $response->shouldReceive('getStatusCode')->andReturn(422);
        $response->shouldReceive('getBody')->andReturn(json_encode(['message' => 'invalid']));

        $this->expectException(ValidationException::class);

        // When
        $devops->projects();
    }
}

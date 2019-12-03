<?php

namespace TestMonitor\DevOps\Tests;

use Mockery;
use TestMonitor\DevOps\Client;
use PHPUnit\Framework\TestCase;
use TestMonitor\DevOps\Resources\WorkItem;
use TestMonitor\DevOps\Exceptions\NotFoundException;
use TestMonitor\DevOps\Exceptions\ValidationException;
use TestMonitor\DevOps\Exceptions\FailedActionException;
use TestMonitor\DevOps\Exceptions\UnauthorizedException;

class WorkItemsTest extends TestCase
{
    protected $token;

    protected $project;

    protected $workItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->token = Mockery::mock('\TestMonitor\DevOps\AccessToken');
        $this->token->shouldReceive('expired')->andReturnFalse();

        $this->project = ['id' => '1', 'name' => 'Project'];

        $this->workItem = [
            'id' => 1,
            'fields' => [
                'System.Title' => 'Title',
                'System.Description' => 'Description',
                'System.WorkItemType' => 'Bug',
                'Microsoft.VSTS.TCM.ReproSteps' => 'Repro',
            ],
        ];
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_should_return_a_work_item()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $response = Mockery::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getBody')->andReturn(json_encode($this->workItem));

        $service->shouldReceive('request')->once()->andReturn($response);

        // When
        $workItem = $devops->workitem($this->workItem['id'], $this->project['id']);

        // Then
        $this->assertInstanceOf(WorkItem::class, $workItem);
        $this->assertEquals($this->workItem['fields']['System.Title'], $workItem->title);
        $this->assertIsArray($workItem->toArray());
    }

    /** @test */
    public function it_should_throw_an_failed_action_exception_when_client_receives_bad_request_while_getting_a_work_item()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andReturn($response = Mockery::mock('Psr\Http\Message\ResponseInterface'));
        $response->shouldReceive('getStatusCode')->andReturn(400);
        $response->shouldReceive('getBody')->andReturnNull();

        $this->expectException(FailedActionException::class);

        // When
        $devops->workitem($this->workItem['id'], $this->project['id']);
    }

    /** @test */
    public function it_should_throw_a_notfound_exception_when_client_receives_not_found_while_getting_a_work_item()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andReturn($response = Mockery::mock('Psr\Http\Message\ResponseInterface'));
        $response->shouldReceive('getStatusCode')->andReturn(404);
        $response->shouldReceive('getBody')->andReturnNull();

        $this->expectException(NotFoundException::class);

        // When
        $devops->workitem($this->workItem['id'], $this->project['id']);
    }

    /** @test */
    public function it_should_throw_a_unauthorized_exception_when_client_lacks_authorization_for_getting_a_work_item()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andReturn($response = Mockery::mock('Psr\Http\Message\ResponseInterface'));
        $response->shouldReceive('getStatusCode')->andReturn(401);
        $response->shouldReceive('getBody')->andReturnNull();

        $this->expectException(UnauthorizedException::class);

        // When
        $devops->workitem($this->workItem['id'], $this->project['id']);
    }

    /** @test */
    public function it_should_create_a_work_item()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andReturn($response = Mockery::mock('Psr\Http\Message\ResponseInterface'));
        $response->shouldReceive('getStatusCode')->andReturn(201);
        $response->shouldReceive('getBody')->andReturn(json_encode($this->workItem));

        // When
        $workItem = $devops->createWorkItem(new WorkItem([
            'title' => $this->workItem['fields']['System.Title'],
            'description' => $this->workItem['fields']['System.Description'],
            'workItemType' => $this->workItem['fields']['System.WorkItemType'],
            'stepsToReproduce' => $this->workItem['fields']['Microsoft.VSTS.TCM.ReproSteps'],
        ]), $this->project['id']);

        // Then
        $this->assertInstanceOf(WorkItem::class, $workItem);
        $this->assertEquals($this->workItem['id'], $workItem->id);
    }

    /** @test */
    public function it_should_throw_a_validation_exception_when_client_provides_invalid_data_while_creating_an_invalid_work_item()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')->once()->andReturn($response = Mockery::mock('Psr\Http\Message\ResponseInterface'));
        $response->shouldReceive('getStatusCode')->andReturn(422);
        $response->shouldReceive('getBody')->andReturn(json_encode(['message' => 'invalid']));

        $this->expectException(ValidationException::class);

        // When
        $devops->createWorkItem(new WorkItem([
            'title' => $this->workItem['fields']['System.Title'],
            'description' => $this->workItem['fields']['System.Description'],
            'workItemType' => $this->workItem['fields']['System.WorkItemType'],
            'stepsToReproduce' => $this->workItem['fields']['Microsoft.VSTS.TCM.ReproSteps'],
        ]), 'bogus');
    }
}

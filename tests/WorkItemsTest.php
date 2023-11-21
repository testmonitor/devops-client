<?php

namespace TestMonitor\DevOps\Tests;

use Mockery;
use GuzzleHttp\Psr7\Response;
use TestMonitor\DevOps\Client;
use PHPUnit\Framework\TestCase;
use TestMonitor\DevOps\Builders\WIQL\WIQL;
use TestMonitor\DevOps\Resources\WorkItem;
use TestMonitor\DevOps\Builders\WIQL\Field;
use TestMonitor\DevOps\Builders\WIQL\Operator;
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
                'System.TeamProject' => 'Project',
                'System.AreaPath' => 'Project',
                'System.WorkItemType' => 'Bug',
                'System.State' => 'New',
                'Microsoft.VSTS.TCM.ReproSteps' => 'Repro',
            ],
            '_links' => [
                'html' => [
                    'href' => 'https://devops.link',
                ],
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

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($this->workItem)));

        // When
        $workItem = $devops->workitem($this->workItem['id'], $this->project['id']);

        // Then
        $this->assertInstanceOf(WorkItem::class, $workItem);
        $this->assertEquals($this->workItem['fields']['System.Title'], $workItem->title);
        $this->assertIsArray($workItem->toArray());
    }

    /** @test */
    public function it_should_throw_a_failed_action_exception_when_client_receives_bad_request_while_getting_a_work_item()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(400, ['Content-Type' => 'application/json'], null));

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

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(404, ['Content-Type' => 'application/json'], null));

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

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(401, ['Content-Type' => 'application/json'], null));

        $this->expectException(UnauthorizedException::class);

        // When
        $devops->workitem($this->workItem['id'], $this->project['id']);
    }

    /** @test */
    public function it_should_return_a_list_of_work_items()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode(['workItems' => ['id' => $this->workItem['id']]])));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode(['value' => [$this->workItem]])));

        // When
        $workItems = $devops->workitems($this->project['id']);

        // Then
        $this->assertIsArray($workItems);
        $this->assertCount(1, $workItems);
        $this->assertInstanceOf(WorkItem::class, $workItems[0]);
        $this->assertEquals($this->workItem['id'], $workItems[0]->id);
        $this->assertIsArray($workItems[0]->toArray());
    }

    /** @test */
    public function it_should_search_through_a_list_of_work_items_using_wiql()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->withArgs(function ($verb, $url, $options) {
                return $options['json'] === [
                    'query' => (new WIQL)->where(Field::STATE, Operator::EQUALS, 'New')->getQuery(),
                    '$top' => 50,
                ];
            })
            ->andReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode(['workItems' => ['id' => $this->workItem['id']]])));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode(['value' => [$this->workItem]])));

        // When
        $workItems = $devops->workitems($this->project['id'], (new WIQL)->where(Field::STATE, Operator::EQUALS, 'New'));

        // Then
        $this->assertIsArray($workItems);
        $this->assertCount(1, $workItems);
        $this->assertInstanceOf(WorkItem::class, $workItems[0]);
        $this->assertEquals($this->workItem['id'], $workItems[0]->id);
        $this->assertIsArray($workItems[0]->toArray());
    }

    /** @test */
    public function it_should_return_an_empty_work_item_list_when_there_are_no_results()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode(['workItems' => []])));

        // When
        $workItems = $devops->workitems($this->project['id'], (new WIQL)->where(Field::STATE, Operator::EQUALS, 'Closed'));

        // Then
        $this->assertIsArray($workItems);
        $this->assertCount(0, $workItems);
    }

    /** @test */
    public function it_should_throw_a_failed_action_exception_when_client_receives_bad_request_while_getting_a_list_of_work_items()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(400, ['Content-Type' => 'application/json'], null));

        $this->expectException(FailedActionException::class);

        // When
        $devops->workitems($this->project['id']);
    }

    /** @test */
    public function it_should_throw_a_notfound_exception_when_client_receives_not_found_while_getting_a_list_of_work_items()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(404, ['Content-Type' => 'application/json'], null));

        $this->expectException(NotFoundException::class);

        // When
        $devops->workitems($this->project['id']);
    }

    /** @test */
    public function it_should_throw_a_unauthorized_exception_when_client_lacks_authorization_for_getting_a_list_of_work_items()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(401, ['Content-Type' => 'application/json'], null));

        $this->expectException(UnauthorizedException::class);

        // When
        $devops->workitems($this->project['id']);
    }

    /** @test */
    public function it_should_create_a_work_item()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(201, ['Content-Type' => 'application/json'], json_encode($this->workItem)));

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

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(422, ['Content-Type' => 'application/json'], json_encode(['message' => 'invalid'])));

        $this->expectException(ValidationException::class);

        // When
        $devops->createWorkItem(new WorkItem([
            'title' => $this->workItem['fields']['System.Title'],
            'description' => $this->workItem['fields']['System.Description'],
            'workItemType' => $this->workItem['fields']['System.WorkItemType'],
            'stepsToReproduce' => $this->workItem['fields']['Microsoft.VSTS.TCM.ReproSteps'],
        ]), 'bogus');
    }

    /** @test */
    public function it_should_update_a_work_item()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(201, ['Content-Type' => 'application/json'], json_encode($this->workItem)));

        // When
        $workItem = $devops->updateWorkItem($this->workItem['id'], $this->project['id'], [
            'title' => 'New Title',
        ]);

        // Then
        $this->assertInstanceOf(WorkItem::class, $workItem);
        $this->assertEquals($this->workItem['id'], $workItem->id);
    }

    /** @test */
    public function it_should_throw_a_validation_exception_when_client_provides_invalid_data_while_updating_an_invalid_work_item()
    {
        // Given
        $devops = new Client(['clientId' => 1, 'clientSecret' => 'secret', 'redirectUrl' => 'none'], 'myorg', $this->token);

        $devops->setClient($service = Mockery::mock('\GuzzleHttp\Client'));

        $service->shouldReceive('request')
            ->once()
            ->andReturn(new Response(422, ['Content-Type' => 'application/json'], json_encode(['message' => 'invalid'])));

        $this->expectException(ValidationException::class);

        // When
        $devops->updateWorkItem($this->workItem['id'], $this->project['id'], [
            'title' => 'New Title',
        ]);
    }
}

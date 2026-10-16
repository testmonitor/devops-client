<?php

namespace TestMonitor\DevOps\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use TestMonitor\DevOps\Builders\WIQL\WIQL;
use TestMonitor\DevOps\Builders\WIQL\Field;
use TestMonitor\DevOps\Builders\WIQL\Macro;
use TestMonitor\DevOps\Builders\WIQL\Operator;

class WIQLTest extends TestCase
{
    #[Test]
    public function it_should_return_a_wiql_builder()
    {
        // Given

        // When
        $builder = (new WIQL);

        // Then
        $this->assertInstanceOf(WIQL::class, $builder);
    }

    #[Test]
    public function it_should_generate_a_wiql_query()
    {
        // Given

        // When
        $query = (new WIQL)->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals('SELECT [System.Id] FROM WorkItems', $query);
    }

    #[Test]
    public function it_should_generate_a_wiql_query_with_a_specified_select()
    {
        // Given

        // When
        $query = (new WIQL)->select([Field::ID, Field::TITLE])->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals('SELECT [System.Id], [System.Title] FROM WorkItems', $query);
    }

    #[Test]
    public function it_should_generate_a_wiql_query_with_a_specified_source()
    {
        // Given

        // When
        $query = (new WIQL)->from('workItemLinks')->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals('SELECT [System.Id] FROM workItemLinks', $query);
    }

    #[Test]
    public function it_should_generate_a_wiql_query_with_a_single_condition()
    {
        // Given

        // When
        $query = (new WIQL)->where(Field::WORK_ITEM_TYPE, Operator::EQUALS, 'Issue')->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals('SELECT [System.Id] FROM WorkItems WHERE [System.WorkItemType] = \'Issue\'', $query);
    }

    #[Test]
    public function it_should_generate_a_wiql_query_with_an_or_condition()
    {
        // Given

        // When
        $query = (new WIQL)
            ->where(Field::ASSIGNED_TO, Operator::EQUALS, 'tkok@testmonitor.com')
            ->orWhere(Field::CREATED_BY, Operator::EQUALS, 'rceelen@testmonitor.com')
            ->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals(
            'SELECT [System.Id] FROM WorkItems WHERE [System.AssignedTo] = \'tkok@testmonitor.com\' ' .
            'OR [System.CreatedBy] = \'rceelen@testmonitor.com\'',
            $query
        );
    }

    #[Test]
    public function it_should_generate_a_wiql_query_with_multiple_conditions()
    {
        // Given

        // When
        $query = (new WIQL)
            ->where(Field::WORK_ITEM_TYPE, Operator::EQUALS, 'Issue')
            ->where(Field::TAGS, Operator::CONTAINS, 'tag')
            ->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals(
            'SELECT [System.Id] FROM WorkItems WHERE [System.WorkItemType] = \'Issue\' AND [System.Tags] Contains \'tag\'',
            $query
        );
    }

    #[Test]
    public function it_should_generate_a_wiql_query_with_a_condition_that_uses_multiple_values()
    {
        // Given

        // When
        $query = (new WIQL)->where(Field::STATE, Operator::IN, ['New', 'Active'])->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals('SELECT [System.Id] FROM WorkItems WHERE [System.State] In (\'New\', \'Active\')', $query);
    }

    #[Test]
    public function it_should_generate_a_wiql_query_with_nested_conditions()
    {
        // Given

        // When
        $query = (new WIQL)
            ->where(function (WIQL $query) {
                return $query
                    ->where(Field::WORK_ITEM_TYPE, Operator::EQUALS, 'Issue')
                    ->orWhere(Field::TAGS, Operator::CONTAINS, 'tag');
            })
            ->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals(
            'SELECT [System.Id] FROM WorkItems WHERE ([System.WorkItemType] = \'Issue\' OR [System.Tags] Contains \'tag\')',
            $query
        );
    }

    #[Test]
    public function it_should_generate_a_wiql_query_with_plain_and_nested_conditions()
    {
        // Given

        // When
        $query = (new WIQL)
            ->where(Field::TAGS, Operator::CONTAINS, 'tag')
            ->where(function (WIQL $query) {
                return $query
                    ->where(Field::WORK_ITEM_TYPE, Operator::EQUALS, 'Issue')
                    ->orWhere(Field::WORK_ITEM_TYPE, Operator::EQUALS, 'Task');
            })
            ->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals(
            'SELECT [System.Id] FROM WorkItems WHERE [System.Tags] Contains \'tag\' AND ([System.WorkItemType] = \'Issue\' OR [System.WorkItemType] = \'Task\')',
            $query
        );
    }

    #[Test]
    public function it_should_generate_a_wiql_query_using_the_project_macro()
    {
        // Given

        // When
        $query = (new WIQL)->where(Field::TEAM_PROJECT, Operator::EQUALS, Macro::currentProject())->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals('SELECT [System.Id] FROM WorkItems WHERE [System.TeamProject] = @Project', $query);
    }

    #[Test]
    public function it_should_generate_a_wiql_query_using_the_user_macro()
    {
        // Given

        // When
        $query = (new WIQL)->where(Field::ASSIGNED_TO, Operator::EQUALS, Macro::currentUser())->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals('SELECT [System.Id] FROM WorkItems WHERE [System.AssignedTo] = @Me', $query);
    }

    #[Test]
    public function it_should_generate_a_wiql_query_with_sorting_criteria()
    {
        // Given

        // When
        $query = (new WIQL)->orderBy(Field::STATE, 'DESC')->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals('SELECT [System.Id] FROM WorkItems  ORDER BY [System.State] DESC', $query);
    }

    #[Test]
    public function it_should_generate_a_wiql_query_with_multiple_sorting_criteria()
    {
        // Given

        // When
        $query = (new WIQL)
            ->orderBy(Field::STATE, 'DESC')
            ->orderBy(Field::CREATED_DATE)
            ->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals('SELECT [System.Id] FROM WorkItems  ORDER BY [System.State] DESC, [System.CreatedDate] ASC', $query);
    }

    #[Test]
    public function it_should_generate_a_wiql_query_using_a_conditionable_query_that_evaluates_as_true()
    {
        // Given

        // When
        $query = (new WIQL)->when(true, function (WIQL $query) {
            return $query->where(Field::PRIORITY, Operator::EQUALS, 1);
        })->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals('SELECT [System.Id] FROM WorkItems WHERE [Microsoft.VSTS.Common.Priority] = \'1\'', $query);
    }

    #[Test]
    public function it_should_generate_a_wiql_query_using_a_conditionable_query_that_evaluates_as_false()
    {
        // Given

        // When
        $query = (new WIQL)->when(false, function (WIQL $query) {
            return $query->where(Field::PRIORITY, Operator::EQUALS, 1);
        })->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals('SELECT [System.Id] FROM WorkItems', $query);
    }

    #[Test]
    public function it_should_generate_a_wiql_query_with_multiple_conditions_and_sorting_criteria()
    {
        // Given

        // When
        $query = (new WIQL)
            ->where(Field::PATH, Operator::NOT_EQUALS, 'tester')
            ->where(Field::DESCRIPTION, Operator::CONTAINS_WORDS, 'test')
            ->orWhere(Field::REPRO_STEPS, Operator::CONTAINS, 'test')
            ->orderBy(Field::TITLE)
            ->orderBy(Field::CHANGED_DATE, 'DESC')
            ->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals(
            'SELECT [System.Id] FROM WorkItems WHERE [System.AreaPath] != \'tester\' AND [System.Description] Contains Words \'test\'' .
            ' OR [Microsoft.VSTS.TCM.ReproSteps] Contains \'test\' ORDER BY [System.Title] ASC, [System.ChangedDate] DESC',
            $query
        );
    }
}

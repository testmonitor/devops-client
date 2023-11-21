<?php

namespace TestMonitor\DevOps\Tests;

use PHPUnit\Framework\TestCase;
use TestMonitor\DevOps\Builders\WIQL\WIQL;
use TestMonitor\DevOps\Builders\WIQL\Field;
use TestMonitor\DevOps\Builders\WIQL\Operator;

class WIQLTest extends TestCase
{
    /** @test */
    public function it_should_return_a_wiql_builder()
    {
        // Given

        // When
        $builder = (new WIQL);

        // Then
        $this->assertInstanceOf(WIQL::class, $builder);
    }

    /** @test */
    public function it_should_generate_a_wiql_query()
    {
        // Given

        // When
        $query = (new WIQL)->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals('SELECT [System.Id] FROM WorkItems', $query);
    }

    /** @test */
    public function it_should_generate_a_wiql_query_with_a_specified_select()
    {
        // Given

        // When
        $query = (new WIQL)->select([Field::ID, Field::TITLE])->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals('SELECT [System.Id], [System.Title] FROM WorkItems', $query);
    }

    /** @test */
    public function it_should_generate_a_wiql_query_with_a_specified_source()
    {
        // Given

        // When
        $query = (new WIQL)->from('workItemLinks')->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals('SELECT [System.Id] FROM workItemLinks', $query);
    }

    /** @test */
    public function it_should_generate_a_wiql_query_with_a_single_condition()
    {
        // Given

        // When
        $query = (new WIQL)->where(Field::WORK_ITEM_TYPE, Operator::EQUALS, 'Issue')->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals('SELECT [System.Id] FROM WorkItems WHERE [System.WorkItemType] = \'Issue\'', $query);
    }

    /** @test */
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

    /** @test */
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

    /** @test */
    public function it_should_generate_a_wiql_query_with_a_condition_that_uses_multiple_values()
    {
        // Given

        // When
        $query = (new WIQL)->where(Field::STATE, Operator::IN, ['New', 'Active'])->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals('SELECT [System.Id] FROM WorkItems WHERE [System.State] In (\'New\', \'Active\')', $query);
    }

    /** @test */
    public function it_should_generate_a_wiql_query_with_sorting_criteria()
    {
        // Given

        // When
        $query = (new WIQL)->orderBy(Field::STATE, 'DESC')->getQuery();

        // Then
        $this->assertIsString($query);
        $this->assertEquals('SELECT [System.Id] FROM WorkItems  ORDER BY [System.State] DESC', $query);
    }

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

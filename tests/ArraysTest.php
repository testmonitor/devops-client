<?php

namespace TestMonitor\DevOps\Tests;

use PHPUnit\Framework\TestCase;
use TestMonitor\DevOps\Support\Arrays;

class ArraysTest extends TestCase
{
    /** @test */
    public function it_should_flatten_an_associative_array()
    {
        // Given
        $array = [
            [
                ['id' => 1, 'name' => 'One'],
                ['id' => 2, 'name' => 'Two'],
            ],
            [
                ['id' => 3, 'name' => 'Three'],
            ],
        ];

        // When
        $result = Arrays::flatten($array);

        // Then
        $this->assertIsArray($result);
        $this->assertEquals([
            ['id' => 1, 'name' => 'One'],
            ['id' => 2, 'name' => 'Two'],
            ['id' => 3, 'name' => 'Three'],
        ], $result);
    }

    /** @test */
    public function it_should_flatten_an_non_associative_array()
    {
        // Given
        $array = [[1, 2], [3]];

        // When
        $result = Arrays::flatten($array);

        // Then
        $this->assertIsArray($result);
        $this->assertEquals([1,2,3], $result);
    }

    /** @test */
    public function it_should_return_unique_items_for_an_associative_array()
    {
        // Given
        $array = [
            ['id' => 1, 'name' => 'One'],
            ['id' => 2, 'name' => 'Two'],
            ['id' => 3, 'name' => 'Three'],
            ['id' => 1, 'name' => 'One'],
        ];

        // When
        $result = Arrays::unique($array, 'id');

        // Then
        $this->assertIsArray($result);
        $this->assertEquals([
            ['id' => 1, 'name' => 'One'],
            ['id' => 2, 'name' => 'Two'],
            ['id' => 3, 'name' => 'Three'],
        ], $result);
    }
}

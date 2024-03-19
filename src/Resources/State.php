<?php

namespace TestMonitor\DevOps\Resources;

class State extends Resource
{
    /**
     * The name of the state.
     *
     * @var string
     */
    public $name;

    /**
     * The color of the state.
     *
     * @var string
     */
    public $color;

    /**
     * The category for the state.
     *
     * @var string
     */
    public $category;

    /**
     * Create a new resource instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->name = $attributes['name'];
        $this->color = $attributes['color'] ?? '';
        $this->category = $attributes['category'] ?? '';
    }
}

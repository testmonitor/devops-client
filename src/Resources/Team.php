<?php

namespace TestMonitor\DevOps\Resources;

class Team extends Resource
{
    /**
     * The id of the team.
     *
     * @var string
     */
    public $id;

    /**
     * The name of the team.
     *
     * @var string
     */
    public $name;

    /**
     * The description of the team.
     *
     * @var string
     */
    public $description;

    /**
     * Create a new resource instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->id = $attributes['id'];
        $this->name = $attributes['name'];
        $this->description = $attributes['description'] ?? '';
    }
}

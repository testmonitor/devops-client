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
     * The area path for the team.
     *
     * @var string
     */
    public $path;

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
        $this->path = $attributes['path'];
    }
}

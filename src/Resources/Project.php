<?php

namespace TestMonitor\DevOps\Resources;

class Project extends Resource
{
    /**
     * The id of the project.
     *
     * @var string
     */
    public $id;

    /**
     * The name of the project.
     *
     * @var string
     */
    public $name;

    /**
     * Create a new resource instance.
     *
     * @param string $id
     * @param string $name
     */
    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}

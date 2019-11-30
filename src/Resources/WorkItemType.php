<?php

namespace TestMonitor\DevOps\Resources;

class WorkItemType extends Resource
{
    /**
     * The name of the work item type.
     *
     * @var string
     */
    public $name;

    /**
     * The name of the work item type.
     *
     * @var string
     */
    public $description;

    /**
     * Create a new resource instance.
     *
     * @param string $name
     * @param string $description
     */
    public function __construct(string $name, string $description)
    {
        $this->name = $name;
        $this->description = $description;
    }
}

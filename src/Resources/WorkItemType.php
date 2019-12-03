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
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->name = $attributes['name'];
        $this->description = $attributes['description'];
    }
}

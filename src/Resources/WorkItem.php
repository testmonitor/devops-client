<?php

namespace TestMonitor\DevOps\Resources;

class WorkItem extends Resource
{
    /**
     * The id of the work item.
     *
     * @var string
     */
    public $id;

    /**
     * The area path for the work item.
     *
     * @var string
     */
    public $path;

    /**
     * The title of the work item.
     *
     * @var string
     */
    public $title;

    /**
     * The description for the work item.
     *
     * @var string
     */
    public $description;

    /**
     * The state of the work item.
     *
     * @var string
     */
    public $state;

    /**
     * The type of the work item.
     *
     * @var string
     */
    public $workItemType;

    /**
     * The repro steps for the work item.
     *
     * @var string
     */
    public $stepsToReproduce;

    /**
     * The url for the work item.
     *
     * @var string
     */
    public $url;

    /**
     * Create a new resource instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->id = $attributes['id'] ?? null;
        $this->title = $attributes['title'];
        $this->description = $attributes['description'] ?? '';
        $this->state = $attributes['state'] ?? '';
        $this->workItemType = $attributes['workItemType'];
        $this->stepsToReproduce = $attributes['stepsToReproduce'] ?? '';
        $this->path = $attributes['path'] ?? null;
        $this->url = $attributes['url'] ?? '';
    }
}

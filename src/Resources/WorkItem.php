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
     * Create a new resource instance.
     *
     * @param string $title
     * @param string $description
     * @param string $workItemType
     * @param string $stepsToReproduce
     * @param string|null $id
     */
    public function __construct(
        string $title,
        string $description,
        string $workItemType,
        string $stepsToReproduce,
        ?string $id = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->workItemType = $workItemType;
        $this->stepsToReproduce = $stepsToReproduce;
    }
}

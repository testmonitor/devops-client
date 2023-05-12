<?php

namespace TestMonitor\DevOps\Actions;

use TestMonitor\DevOps\Resources\WorkItem;
use TestMonitor\DevOps\Transforms\TransformsWorkItems;

trait ManagesWorkItems
{
    use TransformsWorkItems;

    /**
     * Get a single work item.
     *
     * @param string $id
     * @param string $projectId
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return \TestMonitor\DevOps\Resources\WorkItem
     */
    public function workitem(string $id, string $projectId): WorkItem
    {
        $response = $this->get("{$projectId}/_apis/wit/workitems/{$id}");

        return $this->fromDevOpsWorkItem($response);
    }

    /**
     * Create a new work item.
     *
     * @param \TestMonitor\DevOps\Resources\WorkItem $workItem
     * @param string $projectId
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return WorkItem
     */
    public function createWorkItem(WorkItem $workItem, string $projectId): WorkItem
    {
        $response = $this->post(
            "{$projectId}/_apis/wit/workitems/\${$workItem->workItemType}",
            [
                'headers' => ['Content-Type' => 'application/json-patch+json'],
                'json' => $this->toDevOpsWorkItem($workItem, $projectId),
            ]
        );

        return $this->fromDevOpsWorkItem($response);
    }
}

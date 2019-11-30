<?php

namespace TestMonitor\DevOps\Actions;

use TestMonitor\DevOps\Transforms\TransformsWorkItemTypes;

trait ManagesWorkItemTypes
{
    use TransformsWorkItemTypes;

    /**
     * Get the collection of work item types.
     *
     * @param string $projectId
     *
     * @return \TestMonitor\DevOps\Resources\WorkItemType[]
     */
    public function workItemTypes($projectId)
    {
        $response = $this->get("{$projectId}/_apis/wit/workitemtypes");

        return array_map(function ($workItemType) {
            return $this->fromDevOpsWorkItemType($workItemType);
        }, $response['value']);
    }
}

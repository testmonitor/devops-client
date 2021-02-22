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
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     */
    public function workItemTypes($projectId)
    {
        $response = $this->get("{$projectId}/_apis/wit/workitemtypes");

        return $this->fromDevOpsWorkItemTypes($response['value']);
    }
}

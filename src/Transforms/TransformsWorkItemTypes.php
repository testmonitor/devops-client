<?php

namespace TestMonitor\DevOps\Transforms;

use TestMonitor\DevOps\Resources\WorkItemType;

trait TransformsWorkItemTypes
{
    /**
     * @param array $workItemType
     * @return \TestMonitor\DevOps\Resources\WorkItemType
     */
    protected function fromDevOpsWorkItemType(array $workItemType): WorkItemType
    {
        return new WorkItemType([
            'name' => $workItemType['name'],
            'description' => $workItemType['description'],
        ]);
    }
}

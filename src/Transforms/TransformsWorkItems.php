<?php

namespace TestMonitor\DevOps\Transforms;

use TestMonitor\DevOps\Resources\WorkItem;

trait TransformsWorkItems
{
    /**
     * @param \TestMonitor\DevOps\Resources\WorkItem $workItem
     *
     * @return array
     */
    protected function toDevOpsWorkItem(WorkItem $workItem): array
    {
        return [
            [
                'op' => 'add',
                'path' => '/fields/System.Title',
                'from' => null,
                'value' => $workItem->title,
            ],
            [
                'op' => 'add',
                'path' => '/fields/System.Description',
                'from' => null,
                'value' => $workItem->description,
            ],
            [
                'op' => 'add',
                'path' => '/fields/Microsoft.VSTS.TCM.ReproSteps',
                'from' => null,
                'value' => $workItem->stepsToReproduce,
            ],
        ];
    }

    /**
     * @param array $workItem
     *
     * @return \TestMonitor\DevOps\Resources\WorkItem
     */
    protected function fromDevOpsWorkItem(array $workItem): WorkItem
    {
        return new WorkItem([
            'id' => $workItem['id'] ?? '',
            'title' => $workItem['fields']['System.Title'],
            'description' => $workItem['fields']['System.Description'] ?? '',
            'workItemType' => $workItem['fields']['System.WorkItemType'],
            'stepsToReproduce' => $workItem['fields']['Microsoft.VSTS.TCM.ReproSteps'] ?? '',
            'url' => $workItem['_links']['html']['href'] ?? '',
        ]);
    }
}

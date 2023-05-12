<?php

namespace TestMonitor\DevOps\Transforms;

use TestMonitor\DevOps\Validator;
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
            ...($workItem->path ? [
                [
                    'op' => 'add',
                    'path' => '/fields/System.AreaPath',
                    'from' => null,
                    'value' => $workItem->path,
                ],
            ] : []),
        ];
    }

    /**
     * @param array $workItem
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return \TestMonitor\DevOps\Resources\WorkItem
     */
    protected function fromDevOpsWorkItem(array $workItem): WorkItem
    {
        Validator::keyExists($workItem, 'fields');

        return new WorkItem([
            'id' => $workItem['id'] ?? '',
            'path' => $workItem['fields']['System.AreaPath'],
            'title' => $workItem['fields']['System.Title'],
            'description' => $workItem['fields']['System.Description'] ?? '',
            'workItemType' => $workItem['fields']['System.WorkItemType'],
            'stepsToReproduce' => $workItem['fields']['Microsoft.VSTS.TCM.ReproSteps'] ?? '',
            'url' => $workItem['_links']['html']['href'] ?? '',
        ]);
    }
}

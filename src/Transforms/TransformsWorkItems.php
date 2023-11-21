<?php

namespace TestMonitor\DevOps\Transforms;

use TestMonitor\DevOps\Validator;
use TestMonitor\DevOps\Resources\WorkItem;

trait TransformsWorkItems
{
    /**
     * @param \TestMonitor\DevOps\Resources\WorkItem $workItem
     * @return array
     */
    protected function toNewDevOpsWorkItem(WorkItem $workItem): array
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
     * @param array{
     *      title: string,
     *      description: string
     *      state: string
     *      stepsToReproduce: string
     *      path: string
     * } $attributes
     * @return array
     */
    protected function toUpdateDevOpsWorkItem(array $attributes): array
    {
        return [
            array_filter([
                ...(isset($attributes['title']) ? [
                    'op' => 'add',
                    'path' => '/fields/System.Title',
                    'value' => $attributes['title'],
                ] : []),
                ...(isset($attributes['description']) ? [
                    'op' => 'add',
                    'path' => '/fields/System.Description',
                    'value' => $attributes['description'],
                ] : []),
                ...(isset($attributes['state']) ? [
                    'op' => 'add',
                    'path' => '/fields/System.State',
                    'value' => $attributes['state'],
                ] : []),
                ...(isset($attributes['stepsToReproduce']) ? [
                    'op' => 'add',
                    'path' => '/fields/Microsoft.VSTS.TCM.ReproSteps',
                    'value' => $attributes['stepsToReproduce'],
                ] : []),
                ...(isset($attributes['path']) ? [
                    [
                        'op' => 'add',
                        'path' => '/fields/System.AreaPath',
                        'value' => $attributes['path'],
                    ],
                ] : []),
            ]),
        ];
    }

    /**
     * @param array $workitems
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return \TestMonitor\DevOps\Resources\Team[]
     */
    protected function fromDevOpsWorkItems($workitems): array
    {
        Validator::isArray($workitems);

        return array_map(function ($workItem) {
            return $this->fromDevOpsWorkItem($workItem);
        }, $workitems);
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
            'title' => $workItem['fields']['System.Title'],
            'description' => $workItem['fields']['System.Description'] ?? '',
            'state' => $workItem['fields']['System.State'],
            'workItemType' => $workItem['fields']['System.WorkItemType'],
            'stepsToReproduce' => $workItem['fields']['Microsoft.VSTS.TCM.ReproSteps'] ?? '',
            'path' => $workItem['fields']['System.AreaPath'] ?? '',
            'url' => $workItem['_links']['html']['href'] ?? '',
        ]);
    }
}

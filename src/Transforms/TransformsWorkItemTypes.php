<?php

namespace TestMonitor\DevOps\Transforms;

use TestMonitor\DevOps\Validator;
use TestMonitor\DevOps\Resources\WorkItemType;

trait TransformsWorkItemTypes
{
    /**
     * @param array $workItemType
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     * @return \TestMonitor\DevOps\Resources\WorkItemType
     */
    protected function fromDevOpsWorkItemType(array $workItemType): WorkItemType
    {
        Validator::keysExists($workItemType, ['name', 'description']);

        return new WorkItemType([
            'name' => $workItemType['name'],
            'description' => $workItemType['description'],
        ]);
    }

    /**
     * @param $workItemTypes
     *
     * @return array
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     */
    protected function fromDevOpsWorkItemTypes($workItemTypes): array
    {
        Validator::isArray($workItemTypes);

        return array_map(function ($workItemType) {
            return $this->fromDevOpsWorkItemType($workItemType);
        }, $workItemTypes);
    }
}

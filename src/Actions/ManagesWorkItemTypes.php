<?php

namespace TestMonitor\DevOps\Actions;

use TestMonitor\DevOps\Resources\WorkItemType;
use TestMonitor\DevOps\Transforms\TransformsWorkItemTypes;

trait ManagesWorkItemTypes
{
    use TransformsWorkItemTypes;

    /**
     * Get the collection of work item types (excluding hidden types).
     *
     * @param string $projectId
     * @param array $excluded
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return \TestMonitor\DevOps\Resources\WorkItemType[]
     */
    public function workItemTypes($projectId, $excluded = ['Microsoft.HiddenCategory'])
    {
        $response = $this->get("{$projectId}/_apis/wit/workitemtypes");

        $workItemTypes = $this->fromDevOpsWorkItemTypes($response['value']);

        if (empty($excluded)) {
            return $workItemTypes;
        }

        return array_udiff(
            $workItemTypes,
            $this->workItemTypesInCategory($projectId, $excluded),
            fn (WorkItemType $type, WorkItemType $exclude) => $type->name <=> $exclude->name
        );
    }

    /**
     * Get the collection of work item types listed in the specified categories.
     *
     * @param string $projectId
     * @param array $categories
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return \TestMonitor\DevOps\Resources\WorkItemType[]
     */
    public function workItemTypesInCategory($projectId, $categories = [])
    {
        $response = $this->get("{$projectId}/_apis/wit/workitemtypecategories");

        $filtered = array_merge(
            ...array_column(
                array_filter(
                    $response['value'],
                    fn ($category) => in_array($category['referenceName'], $categories)
                ),
                'workItemTypes'
            )
        );

        return $this->fromDevOpsWorkItemTypes($filtered);
    }
}

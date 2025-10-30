<?php

namespace TestMonitor\DevOps\Actions;

use TestMonitor\DevOps\Builders\WIQL\WIQL;
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
     * Get a list of work items.
     *
     * @param string $projectId
     * @param \TestMonitor\DevOps\Builders\WIQL $query
     * @param int $limit
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return \TestMonitor\DevOps\Resources\WorkItem[]
     */
    public function workitems(string $projectId, ?WIQL $query = null, int $limit = 50): array
    {
        // Retrieve work items using WIQL
        $results = $this->post("{$projectId}/_apis/wit/wiql", [
            'json' => [
                'query' => $query instanceof WIQL ? $query->getQuery() : (new WIQL)->getQuery(),
                '$top' => $limit,
            ],
        ]);

        // Return an empty array when there are no results
        if (empty($results['workItems'])) {
            return [];
        }

        // Gather work item ID's
        $ids = array_column($results['workItems'], 'id');

        // Fetch work items by their ID's
        $response = $this->get("{$projectId}/_apis/wit/workitems/", [
            'query' => [
                'ids' => implode(',', $ids),
                'api-version' => $this->apiVersion,
                '$expand' => 'Links',
            ],
        ]);

        return $this->fromDevOpsWorkItems($response['value']);
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
                'json' => $this->toNewDevOpsWorkItem($workItem),
            ]
        );

        return $this->fromDevOpsWorkItem($response);
    }

    /**
     * Updates an existing work item.
     *
     * @param string $id
     * @param string $projectId
     * @param array $attributes
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return WorkItem
     */
    public function updateWorkItem(string $id, string $projectId, array $attributes): WorkItem
    {
        $response = $this->patch(
            "{$projectId}/_apis/wit/workitems/{$id}",
            [
                'headers' => ['Content-Type' => 'application/json-patch+json'],
                'json' => $this->toUpdateDevOpsWorkItem($attributes),
            ]
        );

        return $this->fromDevOpsWorkItem($response);
    }
}

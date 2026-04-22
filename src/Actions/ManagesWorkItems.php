<?php

namespace TestMonitor\DevOps\Actions;

use TestMonitor\DevOps\Builders\WIQL\WIQL;
use TestMonitor\DevOps\Resources\WorkItem;
use TestMonitor\DevOps\Transforms\TransformsWorkItems;
use TestMonitor\DevOps\Responses\LengthAwarePaginatedResponse;

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
     * Get a paginated list of work items.
     *
     * @param string $projectId
     * @param \TestMonitor\DevOps\Builders\WIQL\WIQL|null $query
     * @param int $limit
     * @param int $offset
     * @param int $wiqlLimit Keep this under 20.000 to avoid API errors.
     *
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     * @return \TestMonitor\DevOps\Responses\LengthAwarePaginatedResponse
     */
    public function workitems(
        string $projectId,
        ?WIQL $query = null,
        int $limit = 50,
        int $offset = 0,
        int $wiqlLimit = 10000
    ): LengthAwarePaginatedResponse {
        // Retrieve all matching work item IDs via WIQL
        $results = $this->post("{$projectId}/_apis/wit/wiql", [
            'query' => [
                '$top' => $wiqlLimit,
                'api-version' => $this->apiVersion,
            ],
            'json' => [
                'query' => $query instanceof WIQL ? $query->getQuery() : (new WIQL)->getQuery(),
            ],
        ]);

        // Extract the IDs from the results
        $ids = array_column($results['workItems'] ?? [], 'id');

        // Slice the IDs for the requested page
        $pageIds = array_slice($ids, $offset, $limit);

        if (empty($pageIds)) {
            return new LengthAwarePaginatedResponse([], count($ids), $limit, $offset);
        }

        // Fetch full work item details for this page only
        $response = $this->get("{$projectId}/_apis/wit/workitems/", [
            'query' => [
                'ids' => implode(',', $pageIds),
                'api-version' => $this->apiVersion,
                '$expand' => 'Links',
            ],
        ]);

        return new LengthAwarePaginatedResponse(
            $this->fromDevOpsWorkItems($response['value']),
            count($ids),
            $limit,
            $offset
        );
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

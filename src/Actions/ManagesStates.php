<?php

namespace TestMonitor\DevOps\Actions;

use TestMonitor\DevOps\Support\Arrays;
use TestMonitor\DevOps\Transforms\TransformsStates;

trait ManagesStates
{
    use TransformsStates;

    /**
     * Get a list of states for a project.
     *
     * @param string $projectId
     * @param string $workItemType
     * @return \TestMonitor\DevOps\Resources\States[]
     */
    public function states($projectId)
    {
        $process = $this->findCurrentProjectProcess($projectId);

        $response = $this->get("_apis/work/processes/{$process}/workitemtypes", [
            'query' => ['$expand' => 'states'],
        ]);

        $states = Arrays::unique(
            Arrays::flatten(
                array_column($response['value'], 'states')
            ),
            'id'
        );

        return $this->fromDevOpsStates($states);
    }

    /**
     * Determine the process ID for the provided project.
     *
     * @param string $projectId
     * @return string
     */
    protected function findCurrentProjectProcess($projectId)
    {
        $response = $this->get("_apis/projects/{$projectId}/properties", [
            'query' => ['api-version' => '5.0-preview.1'],
        ]);

        $property = current(
            array_filter($response['value'], fn ($property) => $property['name'] === 'System.CurrentProcessTemplateId'),
        );

        return $property['value'];
    }

    /**
     * Get a list of states for a project and work item type.
     *
     * @param string $projectId
     * @param string $workItemType
     * @return \TestMonitor\DevOps\Resources\States[]
     */
    public function statesForWorkItem($projectId, $workItemType)
    {
        $response = $this->get("{$projectId}/_apis/wit/workitemtypes/{$workItemType}/states");

        return $this->fromDevOpsStates($response['value']);
    }
}

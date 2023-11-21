<?php

namespace TestMonitor\DevOps\Actions;

use TestMonitor\DevOps\Transforms\TransformsStates;

trait ManagesStates
{
    use TransformsStates;

    /**
     * Get a list of states for a project and work item type.
     *
     * @param string $projectId
     * @param string $workItemType
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return \TestMonitor\DevOps\Resources\States[]
     */
    public function states($projectId, $workItemType)
    {
        $response = $this->get("{$projectId}/_apis/wit/workitemtypes/{$workItemType}/states");

        return $this->fromDevOpsStates($response['value']);
    }
}

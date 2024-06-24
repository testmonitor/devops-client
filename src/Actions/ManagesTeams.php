<?php

namespace TestMonitor\DevOps\Actions;

use TestMonitor\DevOps\Transforms\TransformsTeams;

trait ManagesTeams
{
    use TransformsTeams;

    /**
     * Get a list of teams for a project.
     *
     * @param string $projectId
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return \TestMonitor\DevOps\Resources\Team[]
     */
    public function teams($projectId)
    {
        $response = $this->get("_apis/projects/{$projectId}/teams");

        return $this->fromDevOpsTeams($response['value']);
    }
}

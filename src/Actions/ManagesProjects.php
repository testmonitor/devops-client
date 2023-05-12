<?php

namespace TestMonitor\DevOps\Actions;

use TestMonitor\DevOps\Resources\Project;
use TestMonitor\DevOps\Transforms\TransformsProjects;

trait ManagesProjects
{
    use TransformsProjects;

    /**
     * Get a single project.
     *
     * @param string $id
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return \TestMonitor\DevOps\Resources\Project
     */
    public function project(string $id): Project
    {
        $response = $this->get("_apis/projects/{$id}");

        return $this->fromDevOpsProject($response);
    }

    /**
     * Get a list of of projects.
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return \TestMonitor\DevOps\Resources\Project[]
     */
    public function projects()
    {
        $response = $this->get('_apis/projects');

        return $this->fromDevOpsProjects($response['value']);
    }
}

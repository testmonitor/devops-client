<?php

namespace TestMonitor\DevOps\Actions;

use TestMonitor\DevOps\Transforms\TransformsProjects;

trait ManagesProjects
{
    use TransformsProjects;

    /**
     * Get a list of of projects.
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     * @return \TestMonitor\DevOps\Resources\Project[]
     */
    public function projects()
    {
        $response = $this->get('_apis/projects');

        return $this->fromDevOpsProjects($response['value']);
    }
}

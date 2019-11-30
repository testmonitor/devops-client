<?php

namespace TestMonitor\DevOps\Actions;

use TestMonitor\DevOps\Transforms\TransformsProjects;

trait ManagesProjects
{
    use TransformsProjects;

    /**
     * Get a list of of projects.
     *
     * @return \TestMonitor\DevOps\Resources\Project[]
     */
    public function projects()
    {
        $response = $this->get("_apis/projects");

        return array_map(function ($project) {
            return $this->fromDevOpsProject($project);
        }, $response['value']);
    }
}

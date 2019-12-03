<?php

namespace TestMonitor\DevOps\Transforms;

use TestMonitor\DevOps\Resources\Project;

trait TransformsProjects
{
    /**
     * @param array $project
     *
     * @return \TestMonitor\DevOps\Resources\Project
     */
    protected function fromDevOpsProject(array $project): Project
    {
        return new Project([
            'id' => $project['id'],
            'name' => $project['name'],
        ]);
    }
}

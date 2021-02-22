<?php

namespace TestMonitor\DevOps\Transforms;

use TestMonitor\DevOps\Validator;
use TestMonitor\DevOps\Resources\Project;

trait TransformsProjects
{
    /**
     * @param array $projects
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     * @return \TestMonitor\DevOps\Resources\Project[]
     */
    protected function fromDevOpsProjects($projects): array
    {
        Validator::isArray($projects);

        return array_map(function ($project) {
            return $this->fromDevOpsProject($project);
        }, $projects);
    }

    /**
     * @param array $project
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     * @return \TestMonitor\DevOps\Resources\Project
     */
    protected function fromDevOpsProject($project): Project
    {
        Validator::keysExists($project, ['id', 'name']);

        return new Project([
            'id' => $project['id'],
            'name' => $project['name'],
        ]);
    }
}

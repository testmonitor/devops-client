<?php

namespace TestMonitor\DevOps\Transforms;

use TestMonitor\DevOps\Validator;
use TestMonitor\DevOps\Resources\Project;
use TestMonitor\DevOps\Resources\Team;

trait TransformsTeams
{
    /**
     * @param array $teams
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return \TestMonitor\DevOps\Resources\Team[]
     */
    protected function fromDevOpsTeams($teams): array
    {
        Validator::isArray($teams);

        return array_map(function ($team) {
            return $this->fromDevOpsTeam($team);
        }, $teams);
    }

    /**
     * @param array $team
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return \TestMonitor\DevOps\Resources\Team
     */
    protected function fromDevOpsTeam($team): Team
    {
        Validator::keysExists($team, ['id', 'name']);

        return new Team([
            'id' => $team['id'],
            'name' => $team['name'],
            'description' => $team['description'],
        ]);
    }
}

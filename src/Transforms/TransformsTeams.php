<?php

namespace TestMonitor\DevOps\Transforms;

use TestMonitor\DevOps\Validator;
use TestMonitor\DevOps\Resources\Team;

trait TransformsTeams
{
    /**
     * @param array $teams
     * @param string $projectId
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return \TestMonitor\DevOps\Resources\Team[]
     */
    protected function fromDevOpsTeams($teams, string $projectId): array
    {
        Validator::isArray($teams);

        return array_map(function ($team) use ($projectId) {
            return $this->fromDevOpsTeam($team, $projectId);
        }, $teams);
    }

    /**
     * @param array $team
     * @param string $projectId
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return \TestMonitor\DevOps\Resources\Team
     */
    protected function fromDevOpsTeam($team, string $projectId): Team
    {
        Validator::keysExists($team, ['id', 'name']);

        return new Team([
            'id' => $team['id'],
            'name' => $team['name'],
            'description' => $team['description'],
            'path' => "{$projectId}\\{$team['name']}",
        ]);
    }
}

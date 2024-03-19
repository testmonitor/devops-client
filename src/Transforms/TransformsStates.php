<?php

namespace TestMonitor\DevOps\Transforms;

use TestMonitor\DevOps\Validator;
use TestMonitor\DevOps\Resources\State;

trait TransformsStates
{
    /**
     * @param array $states
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return \TestMonitor\DevOps\Resources\State[]
     */
    protected function fromDevOpsStates($states): array
    {
        Validator::isArray($states);

        return array_map(function ($state) {
            return $this->fromDevOpsState($state);
        }, $states);
    }

    /**
     * @param array $state
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return \TestMonitor\DevOps\Resources\State
     */
    protected function fromDevOpsState($state): State
    {
        Validator::keysExists($state, ['name']);

        return new State([
            'name' => $state['name'],
            'color' => $state['color'],
            'category' => $state['category'] ?? '',
        ]);
    }
}

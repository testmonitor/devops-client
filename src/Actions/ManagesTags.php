<?php

namespace TestMonitor\DevOps\Actions;

use TestMonitor\DevOps\Transforms\TransformsTags;

trait ManagesTags
{
    use TransformsTags;

    /**
     * Get a list of tags for a project.
     *
     * @param string $projectId
     *
     * @return \TestMonitor\DevOps\Resources\Tags[]
     */
    public function tags($projectId)
    {
        $response = $this->get("{$projectId}/_apis/wit/tags");

        return $this->fromDevOpsTags($response['value']);
    }
}

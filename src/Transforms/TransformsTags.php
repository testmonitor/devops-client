<?php

namespace TestMonitor\DevOps\Transforms;

use TestMonitor\DevOps\Validator;
use TestMonitor\DevOps\Resources\Tag;

trait TransformsTags
{
    /**
     * @param array $tags
     * @return \TestMonitor\DevOps\Resources\Tag[]
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     */
    protected function fromDevOpsTags($tags): array
    {
        Validator::isArray($tags);

        return array_map(function ($tag) {
            return $this->fromDevOpsTag($tag);
        }, $tags);
    }

    /**
     * @param array $tag
     * @return \TestMonitor\DevOps\Resources\Tag
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     */
    protected function fromDevOpsTag($tag): Tag
    {
        Validator::keysExists($tag, ['id', 'name']);

        return new Tag([
            'id' => $tag['id'],
            'name' => $tag['name'],
        ]);
    }
}

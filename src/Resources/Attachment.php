<?php

namespace TestMonitor\DevOps\Resources;

class Attachment extends Resource
{
    /**
     * The id of the attachment.
     *
     * @var string
     */
    public $id;

    /**
     * The url of the attachment.
     *
     * @var string
     */
    public $url;

    /**
     * Create a new resource instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->id = $attributes['id'] ?? null;
        $this->url = $attributes['url'];
    }
}

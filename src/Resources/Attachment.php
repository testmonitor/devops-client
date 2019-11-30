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
     * @param string $url
     * @param string|null $id
     */
    public function __construct(string $url, ?string $id)
    {
        $this->url = $url;
        $this->id = $id;
    }
}

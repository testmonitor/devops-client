<?php

namespace TestMonitor\DevOps\Resources;

class Webhook extends Resource
{
    /**
     * The id of the webhook.
     *
     * @var string
     */
    public $id;

    /**
     * The URL of the webhook.
     *
     * @var string
     */
    public $url;

    /**
     * The description of the webhook.
     *
     * @var string
     */
    public $description;

    /**
     * The webhook trigger event.
     *
     * @var string
     */
    public $eventType;

    /**
     * The webhook project.
     *
     * @var string
     */
    public $projectId;

    /**
     * The basic authentication username.
     *
     * @var string
     */
    public $username;

    /**
     * The basic authentication password.
     *
     * @var string
     */
    public $password;

    /**
     * Create a new resource instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->id = $attributes['id'] ?? '';
        $this->url = $attributes['url'] ?? '';
        $this->description = $attributes['description'] ?? '';
        $this->eventType = $attributes['eventType'];
        $this->projectId = $attributes['projectId'] ?? '';
        $this->username = $attributes['username'] ?? '';
        $this->password = $attributes['password'] ?? '';
    }
}

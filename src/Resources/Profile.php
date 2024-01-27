<?php

namespace TestMonitor\DevOps\Resources;

class Profile extends Resource
{
    /**
     * The id of the profile.
     *
     * @var string
     */
    public $id;

    /**
     * The name of the profile.
     *
     * @var string
     */
    public $name;

    /**
     * The email address of the profile.
     *
     * @var string
     */
    public $email;

    /**
     * Create a new resource instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->id = $attributes['id'];
        $this->name = $attributes['name'];
        $this->email = $attributes['email'];
    }
}

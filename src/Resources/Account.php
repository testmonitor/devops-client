<?php

namespace TestMonitor\DevOps\Resources;

class Account extends Resource
{
    /**
     * The id of the account.
     *
     * @var string
     */
    public $id;

    /**
     * The name of the account.
     *
     * @var string
     */
    public $name;

    /**
     * Create a new resource instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->id = $attributes['id'];
        $this->name = $attributes['name'];
    }
}

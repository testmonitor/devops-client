<?php

namespace TestMonitor\DevOps\Builders\WIQL;

class Macro
{
    /**
     * The macro's expression value.
     *
     * @var string
     */
    protected string $value;

    /**
     * Construct a new macro.
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Use with an identity or user account field to automatically search for items associated with your user name.
     *
     * @return static
     */
    public static function currentUser(): static
    {
        return new self('@Me');
    }

    /**
     * Use with the Team Project field to filter for work items in other projects.
     *
     * @return static
     */
    public static function currentProject(): static
    {
        return new self('@Project');
    }

    /**
     * Returns the macro's expression value.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}

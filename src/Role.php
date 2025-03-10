<?php

namespace ArtisanBuild\Verbstream;

use JsonSerializable;
use ReturnTypeWillChange;

class Role implements JsonSerializable
{
    /**
     * The key identifier for the role.
     *
     * @var string
     */
    public $key;

    /**
     * The name of the role.
     *
     * @var string
     */
    public $name;

    /**
     * The role's permissions.
     *
     * @var array
     */
    public $permissions;

    /**
     * The role's description.
     *
     * @var string
     */
    public $description;

    /**
     * Create a new role instance.
     *
     * @return void
     */
    public function __construct(string $key, string $name, array $permissions)
    {
        $this->key = $key;
        $this->name = $name;
        $this->permissions = $permissions;
    }

    /**
     * Describe the role.
     *
     * @return $this
     */
    public function description(string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the JSON serializable representation of the object.
     *
     * @return array
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'key' => $this->key,
            'name' => __($this->name),
            'description' => __($this->description),
            'permissions' => $this->permissions,
        ];
    }
}

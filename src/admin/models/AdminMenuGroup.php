<?php declare(strict_types=1);

namespace VitesseCms\Admin\Models;

use VitesseCms\Core\Models\Datagroup;

class AdminMenuGroup
{
    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var array
     */
    protected $datagroups;

    public function __construct(string $label, string $key, array $datagroups)
    {
        $this->label = $label;
        $this->key = $key;
        $this->datagroups = $datagroups;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return Datagroup[]
     */
    public function getDatagroups(): array
    {
        return $this->datagroups;
    }
}

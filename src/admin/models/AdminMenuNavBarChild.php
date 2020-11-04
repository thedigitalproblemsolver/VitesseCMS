<?php declare(strict_types=1);

namespace VitesseCms\Admin\Models;

class AdminMenuNavBarChild
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var string
     */
    protected $target;

    public function __construct(string $name, string $slug, string $target = '') {
        $this->name = $name;
        $this->slug = $slug;
        $this->target = $target;
    }

    public function toArray(): array
    {
        return [
            'name'     => $this->name,
            'slug'     => $this->slug,
            'target'    => $this->target
        ];
    }
}

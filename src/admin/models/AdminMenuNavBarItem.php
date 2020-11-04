<?php declare(strict_types=1);

namespace VitesseCms\Admin\Models;

class AdminMenuNavBarItem
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
    protected $class;

    /**
     * @var string
     */
    protected $extra;

    /**
     * @var array
     */
    protected $children;

    public function __construct(
        string $name,
        string $slug,
        string $class,
        string $extra,
        array $children
    ) {
        $this->name = $name;
        $this->slug = $slug;
        $this->class = $class;
        $this->extra = $extra;
        $this->children = $children;
    }

    public function toArray(): array
    {
        return [
            'name'     => $this->name,
            'slug'     => $this->slug,
            'class'    => $this->class,
            'extra'    => $this->extra,
            'children' => $this->children,
        ];
    }
}

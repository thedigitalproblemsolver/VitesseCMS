<?php declare(strict_types=1);

namespace VitesseCms\Admin\Models;

class AdminMenuNavBarChildren
{
    /**
     * @var array
     */
    protected $items;

    public function addChild(string $name, string $slug, string $target = ''): AdminMenuNavBarChildren
    {
        $this->items[] = (new AdminMenuNavBarChild($name, $slug, $target))->toArray();

        return $this;
    }

    public function addLine(): AdminMenuNavBarChildren
    {
        $this->items[] = (new AdminMenuNavBarChild('<hr/>', '#'))->toArray();

        return $this;
    }

    public function getItems(): array
    {
        return $this->items ?? [];
    }
}

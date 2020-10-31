<?php declare(strict_types=1);

namespace VitesseCms\Admin\Models;

class AdminMenuGroupIterator extends \ArrayIterator
{
    protected $index;

    public function __construct(array $groups = [])
    {
        $this->index = [];
        parent::__construct($groups);
    }

    public function add(AdminMenuGroup $adminMenuGroup): AdminMenuGroupIterator
    {
        $this->index[$adminMenuGroup->getKey()] = count($this->index);
        $this->append($adminMenuGroup);

        return $this;
    }

    public function current(): AdminMenuGroup
    {
        return parent::current();
    }

    public function getByKey(string $key): AdminMenuGroup
    {
        $this->seek($this->index[$key]);

        return $this->current();
    }
}

<?php declare(strict_types=1);

namespace VitesseCms\Core\Models;

use \ArrayIterator;

class DatafieldIterator extends ArrayIterator
{
    public function __construct(array $datafields = [])
    {
        parent::__construct($datafields);
    }

    public function current(): Datafield
    {
        return parent::current();
    }

    public function add(Datafield $value): void
    {
        $this->append($value);
    }
}


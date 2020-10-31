<?php declare(strict_types=1);

namespace VitesseCms\Import\Models;

use \ArrayIterator;

class ImportDatafieldIterator extends ArrayIterator
{
    public function __construct(array $datafields = [])
    {
        parent::__construct($datafields);
    }

    public function current(): ImportDatafield
    {
        return parent::current();
    }

    public function add(ImportDatafield $value): void
    {
        $this->append($value);
    }
}



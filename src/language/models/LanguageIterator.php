<?php declare(strict_types=1);

namespace VitesseCms\Language\Models;

class LanguageIterator extends \ArrayIterator
{
    public function __construct(array $languages)
    {
        parent::__construct($languages);
    }

    public function current(): Language
    {
        return parent::current();
    }
}

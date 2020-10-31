<?php declare(strict_types=1);

namespace VitesseCms\Communication\Models;

class NewsletterIterator extends \ArrayIterator
{
    public function __construct(array $newsletters)
    {
        parent::__construct($newsletters);
    }

    public function current(): Newsletter
    {
        return parent::current();
    }
}

<?php declare(strict_types=1);

namespace VitesseCms\Communication\Models;

use VitesseCms\Database\AbstractCollection;

class Email extends AbstractCollection
{
    public function getAdminlistName(): string
    {
        return $this->_('subject');
    }
}

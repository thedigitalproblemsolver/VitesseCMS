<?php declare(strict_types=1);

namespace VitesseCms\Form\Models;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\Models\Datafield;

class Submission extends AbstractCollection
{
    /**
     * @var array
     */
    public $fieldNames = [];

    /**
     * @var ?string
     */
    public $email;

    public function addFieldname(string $key): bool
    {
        Datafield::setFindValue('calling_name', $key);
        $datafield = Datafield::findFirst();
        if ($datafield) :
            $this->fieldNames[$key] = $datafield->_('name');

            return true;
        endif;

        return false;
    }

    public function hasEmail(): bool
    {
        return !empty($this->email);
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}

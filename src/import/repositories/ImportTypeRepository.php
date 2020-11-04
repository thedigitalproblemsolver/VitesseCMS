<?php declare(strict_types=1);

namespace VitesseCms\Import\Repositories;

use VitesseCms\Import\Models\ImportType;

class ImportTypeRepository
{
    public function getById(string $id): ?ImportType
    {
        /** @var ImportType $item */
        $item = ImportType::findById($id);
        if (is_object($item)):
            return $item;
        endif;

        return null;
    }
}

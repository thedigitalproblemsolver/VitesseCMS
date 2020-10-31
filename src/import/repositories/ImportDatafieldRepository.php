<?php declare(strict_types=1);

namespace VitesseCms\Import\Repositories;

use VitesseCms\Import\Models\ImportDatafield;

class ImportDatafieldRepository
{
    public function getById(string $id): ?ImportDatafield
    {
        /** @var ImportDatafield $item */
        $item = ImportDatafield::findById($id);
        if (is_object($item)):
            return $item;
        endif;

        return null;
    }
}

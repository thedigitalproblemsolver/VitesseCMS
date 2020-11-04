<?php declare(strict_types=1);

namespace VitesseCms\Communication\Helpers;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Export\Helpers\CsvExportHelper;

class ExportHelper extends CsvExportHelper
{
    protected $fields = ['members', 'subscribed', 'GdprEmail'];

    protected function getItemValue(AbstractCollection $item, string $fieldName): string
    {
        if ($fieldName === 'members') :
            foreach ((array)$item->_($fieldName) as $member) :
                if ($member['unSubscribeDate'] === null) :
                    fputcsv($this->output, [
                        $member['email'],
                        $member['subscribeDate'],
                        true,
                    ]);
                endif;
            endforeach;
        endif;

        return '';
    }
}

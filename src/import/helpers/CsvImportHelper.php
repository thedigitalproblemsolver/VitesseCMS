<?php

namespace VitesseCms\Import\Helpers;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Import\Models\ImportType;
use Phalcon\Forms\Element\Check;
use VitesseCms\Shop\Models\TaxRate;

/**
 * Class ImportHelper
 */
class CsvImportHelper extends AbstractImportHelper
{

    /**
     * {@inheritdoc}
     */
    public function processImport(?ImportType $importType = null): void
    {
        $row = 1;
        $mappedFields = [];

        if (($handle = fopen($this->file->getTempName(), "r")) !== false) :
            while (($data = fgetcsv($handle, 1000, ",")) !== false) :
                if($row === 1 ) :
                    $mappedFields = array_flip($data);
                else :
                    $new = false;
                    if( isset($data[$mappedFields['id']]) && !empty($data[$mappedFields['id']]) ) :
                        $this->class::setFindPublished(false);
                        $item = $this->class::findById($data[$mappedFields['id']]);
                    else :
                        $new = true;
                        $item = new $this->class();
                    endif;

                    foreach ($this->fields as $key => $field) :
                        if(
                            isset($mappedFields[$field['name']])
                        ) :
                            if(isset($field['datafield']) && $field['datafield']) :
                                switch ($field['datafield']->_('type')) :
                                    case 'FieldModel':
                                        $name = 'name';
                                        $value = $data[$mappedFields[$field['name']]];
                                        if( $field['datafield']->_('model') === TaxRate::class) :
                                            $name = 'taxrate';
                                            $value = (int)$value;
                                        endif;
                                        /** @var AbstractCollection $modelClass */
                                        $modelClass = $field['datafield']->_('model');
                                        $modelClass::setFindPublished(false);
                                        $modelClass::setFindValue($name , $value );
                                        $model = $modelClass::findFirst();
                                        $data[$mappedFields[$field['name']]] = (string)$model->getId();
                                        break;
                                    case 'FieldPrice':
                                        $item->set('price_sale', $data[$mappedFields['price_sale']]);
                                        $item->set('price_purchase', $data[$mappedFields['price_purchase']]);
                                        break;
                                endswitch;
                            endif;

                            if( $field['multilang'] === true ) :
                                if($new) :
                                    $item->set(
                                        $field['name'],
                                        $data[$mappedFields[$field['name']]],
                                        true
                                    );
                                else :
                                    $item->set(
                                        $field['name'],
                                        $data[$mappedFields[$field['name']]],
                                        true,
                                        $this->language->_('short')
                                    );
                                endif;
                            else :
                                switch ($field['element']) :
                                    case Check::class :
                                        $item->set($field['name'], (bool)$data[$mappedFields[$field['name']]]);
                                        break;
                                    default:
                                        $item->set($field['name'], $data[$mappedFields[$field['name']]]);
                                        break;
                                endswitch;

                            endif;
                        endif;
                    endforeach;
                    $item->save();
                endif;
                $row++;
            endwhile;
            fclose($handle);
        endif;
    }
}

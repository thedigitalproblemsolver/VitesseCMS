<?php declare(strict_types=1);

namespace VitesseCms\Import\Helpers;

use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Helpers\DatagroupHelper;
use VitesseCms\Core\Models\Datafield;
use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Import\Forms\ImportTypeForm;
use VitesseCms\Import\Models\ImportType;
use VitesseCms\Language\Models\Language;

class UrlImportHelper extends AbstractImportHelper
{
    public function processImport(?ImportType $importType = null): void
    {
        die('processImport');
    }

    public static function buildAdminForm(ImportTypeForm $form, ImportType $item): void
    {
        $form->addUrl('Url', 'url',(new Attributes())->setRequired(true))
            ->addDropdown(
                'Language',
                'language',
                (new Attributes())->setRequired(true)
                    ->setOptions(ElementHelper::arrayToSelectOptions(Language::findAll()))
        );

        if ($item->_('url')) :
            $form->addHtml('<h2>Datafield mapping</h2>');

            $httpHeaders = get_headers($item->_('url'),1);

            $header = [];
            switch ($httpHeaders['Content-Type'][1]) :
                case 'text/csv':
                case 'text/csv; charset=utf-8':
                    if (($handle = fopen($item->_('url'), 'r')) !== false) {
                        while (($header = fgetcsv($handle, 1000, ',')) !== false) {
                            break;
                        }
                        fclose($handle);
                    }
                    break;
                case 'application/json':
                case 'application/json; charset=UTF-8':
                    $json = json_decode(file_get_contents($item->_('url')),true);
                    foreach ($json[0] as $name => $value) :
                        $header[] = $name;
                    endforeach;
                    break;
            endswitch;

            if(count($header) > 0 ) :
                $header = array_combine($header, $header);
                /** @var Datagroup $datagroup */
                $datagroup = Datagroup::findById($item->_('datagroup'));
                foreach ($datagroup->_('datafields') as $datafieldValue) :
                    $datafield = Datafield::findById($datafieldValue['id']);
                    $form->addDropdown(
                        $datafield->_('name'),
                        'importField_' . $datafield->_('calling_name'),
                        (new Attributes())->setRequired((bool)$datafieldValue['required'])
                            ->setOptions(ElementHelper::arrayToSelectOptions($header))
                    )->addText(
                        $datafield->_('name').' - core',
                        'importField_empty_' . $datafield->_('calling_name')
                    )->addToggle(
                        $datafield->_('name').' - update',
                        'importField_update_' . $datafield->_('calling_name')
                    )->addToggle(
                        $datafield->_('name').' - unique',
                        'importField_unique_' . $datafield->_('calling_name')
                    );
                endforeach;

                $form->addHtml('<h2>Categorie mapping</h2>');

                $categoryGroups = DatagroupHelper::getPathFromRoot($datagroup);
                /** @var  Datagroup $categoryGroup */
                foreach ($categoryGroups as $key => $categoryGroup) :
                    if((string)$categoryGroup->getId() !== $item->_('datagroup')) :
                        Item::setFindValue('datagroup', (string)$categoryGroup->getId());
                        $categories = Item::findAll();
                        if($categories) :
                            foreach ($categories as $category) :
                                $header[(string)$category->getId()] = 'Item : '.$category->_('name');
                            endforeach;
                        endif;

                        $form->addDropdown(
                            'Categorie: '.$categoryGroup->_('name'),
                            'category_'.$key,
                            (new Attributes())->setRequired(true)
                                ->setOptions(ElementHelper::arrayToSelectOptions($header))
                        );
                    endif;
                endforeach;
            endif;
        endif;
    }
}

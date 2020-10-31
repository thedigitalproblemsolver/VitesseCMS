<?php declare(strict_types=1);

namespace VitesseCms\Field\Models;

use VitesseCms\Core\Models\Datafield;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Field\AbstractField;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;

class FieldModel extends AbstractField
{
    public function buildItemFormElement(AbstractForm $form, Datafield $datafield, AbstractCollection $data = null)
    {
        $model = $datafield->getModel();
        $model::addFindOrder('name');
        if ($datafield->_('displayLimit') && $datafield->_('displayLimit') > 0):
            $model::setFindLimit($datafield->_('displayLimit'));
        endif;

        if ($datafield->getDatagroup() !== null):
            $model::setFindValue('datagroup', ['$in' => $datafield->getDatagroup()]);
        endif;

        $this->setOption('options', ElementHelper::arrayToSelectOptions($model::findAll()));

        if ($datafield->_('useSelect2')) :
            $this->setOption('inputClass', 'select2');
        endif;

        if ($datafield->_('multiple')) :
            $this->setOption('multiple', 'multiple');
        endif;

        $form->_(
            'select',
            $datafield->getNameField(),
            $datafield->getCallingName(),
            $this->getOptions()
        );
    }

    public static function beforeSave(AbstractCollection $item, Datafield $datafield)
    {
        $value = $item->_($datafield->getCallingName());
        if ($value) :
            if (!is_array($value)) :
                $object = $datafield->_('model');
                /** @var AbstractCollection $datafieldItem */
                $datafieldItem = $object::findById($value);
                $item->set(
                    $datafield->getCallingName() . 'Name',
                    $datafieldItem->getNameField()
                );
            endif;
        else :
            $item->set($datafield->getCallingName() . 'Name', '');
        endif;
    }

    public function renderSlugPart(
        AbstractCollection $item,
        string $languageShort,
        Datafield $datafield
    ): string
    {
        return 'page:' . $item->_($datafield->getCallingName());
    }
}

<?php declare(strict_types=1);

namespace VitesseCms\Field\Forms;

use VitesseCms\Content\Models\Item;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Field\Interfaces\AdminformInterface;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;

class AdminFieldDatagroup implements AdminformInterface
{
    public static function buildForm(AbstractForm $form, AbstractCollection $item): void
    {
        $form->addDropdown(
            'Datagroup',
            'datagroup',
            (new Attributes())->setOptions(ElementHelper::arrayToSelectOptions(Datagroup::findAll()))
        );

        if ($item->_('datagroup')) :
            $datagroup = Datagroup::findById($item->_('datagroup'));
            if ($datagroup && $datagroup->_('parentId')) :
                Item::setFindValue('datagroup', $datagroup->_('parentId'));
                $items = ElementHelper::arrayToSelectOptions(Item::findAll());
                $form->addDropdown(
                    'parentItem',
                    'itemParent',
                    (new Attributes())->setOptions($items)
                );
            endif;
        endif;

        $form->addToggle('Multiselect','multiple');
    }
}

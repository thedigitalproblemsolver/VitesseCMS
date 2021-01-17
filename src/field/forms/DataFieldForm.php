<?php declare(strict_types=1);

namespace VitesseCms\Field\Forms;

use VitesseCms\Form\AbstractForm;
use VitesseCms\Core\Models\Datafield;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;

class DataFieldForm extends AbstractForm
{
    public function initialize(Datafield $item = null): void
    {
        $this->addText(
            '%CORE_NAME%',
            'name',
            (new Attributes())->setRequired()->setMultilang()
        )->addText(
            '%ADMIN_CALLING_NAME%',
            'calling_name',
            (new Attributes())->setRequired()
        )->addToggle('%ADMIN_MULTILINGUAL%', 'multilang');

        if( $item !== null && $item->getFieldType() !== null ) :
            $object = $item->getClass();
            (new $object())->buildAdminForm($this, $item);
        endif;

        $this->addDropdown(
            '%ADMIN_DATAFIELD_TYPE%',
            'type',
            (new Attributes())
                ->setOptions(ElementHelper::arrayToSelectOptions((new Datafield)->getTypes()))
                ->setRequired()
        )->addSubmitButton('%CORE_SAVE%');
    }
}

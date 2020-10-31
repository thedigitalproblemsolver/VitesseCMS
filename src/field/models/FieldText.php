<?php declare(strict_types=1);

namespace VitesseCms\Field\Models;

use VitesseCms\Core\Models\Datafield;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Field\AbstractField;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Interfaces\AbstractFormInterface;
use VitesseCms\Media\Enums\AssetsEnum;

class FieldText extends AbstractField
{
    public function buildItemFormElement(
        AbstractForm $form,
        Datafield $datafield,
        AbstractCollection $data = null
    ) {
        $inputType = 'text';
        if ($datafield->_('inputType')) :
            $inputType = $datafield->_('inputType');
        endif;

        $form->_(
            $inputType,
            $datafield->getNameField(),
            $datafield->getCallingName(),
            $this->getOptions()
        );
    }

    public function renderFilter(
        AbstractFormInterface $filter,
        Datafield $datafield,
        AbstractCollection $data = null
    ): void {
        $fieldName = 'filter['.$datafield->getCallingName().']';
        switch ($datafield->_('inputType')) :
            case 'number':
                $this->di->assets->load(AssetsEnum::SLIDER);
                $fieldName = str_replace('filter[', 'filter[range][', $fieldName);
                $filter->_(
                    'text',
                    $datafield->getNameField(),
                    $fieldName,
                    [
                        'data-slider-id'    => 'silder-'.$datafield->_('calling_name'),
                        'data-slider-min'   => '0',
                        'data-slider-max'   => '40',
                        'data-slider-step'  => '1',
                        'data-slider-value' => '[1,40]',
                        'inputClass'        => 'slider',
                    ]
                );
                break;
            default:
                $filter->_(
                    'hidden',
                    null,
                    'filter[textFields]['.uniqid('', true).']',
                    [
                        'value' => $datafield->getCallingName(),
                    ]
                );
                break;
        endswitch;
    }

    public function renderAdminlistFilter(AbstractFormInterface $filter, Datafield $datafield): void
    {
        $filter->addText(
            $datafield->getNameField(),
            $this->getFieldname($datafield)
        );
    }
}

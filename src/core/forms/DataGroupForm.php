<?php declare(strict_types=1);

namespace VitesseCms\Core\Forms;

use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Core\Enum\SystemEnum;
use VitesseCms\Core\Utils\SystemUtil;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Core\Models\Datafield;
use VitesseCms\Form\Helpers\ElementHelper;

class DataGroupForm extends AbstractForm
{
    public function initialize(Datagroup $item)
    {
        $this->_(
            'text',
            '%CORE_NAME%',
            'name',
            [
                'required'  => 'required',
                'multilang' => true,
            ]
        );

        $files = SystemUtil::getTemplateFiles('MainContent');
        $options = [];
        foreach ($files as $key => $label) :
            $selected = false;
            if ($item->_('template') === $key) :
                $selected = true;
            endif;
            $options[] = [
                'value'    => $key,
                'label'    => $label,
                'selected' => $selected,
            ];
        endforeach;
        $this->_(
            'select',
            '%ADMIN_CHOOSE_A_TEMPLATE%',
            'template',
            [
                'required' => 'required',
                'options'  => $options,
            ]
        )->_(
            'select',
            '%ADMIN_CMS_COMPONENT%',
            'component',
            [
                'required' => 'required',
                'options'  => ElementHelper::arrayToSelectOptions(SystemEnum::COMPONENTS),
            ]
        )->_(
            'number',
            '%ADMIN_ORDERING%',
            'ordering'
        )->_(
            'select',
            '%ADMIN_DATAGROUP_ITEM_ORDER%',
            'itemOrdering',
            [
                'options' => ElementHelper::arrayToSelectOptions(
                    [
                        ''          => '%ADMIN_ITEM_ORDER_NAME%',
                        'ordering'  => '%ADMIN_ITEM_ORDER_ORDERING%',
                        'createdAt' => 'Created date',
                    ]
                ),
            ]
        )->_(
            'select',
            '%ADMIN_DATAFIELD%',
            'datafield',
            [
                'options'    => ElementHelper::arrayToSelectOptions(Datafield::findAll()),
                'inputClass' => 'select2',
            ]
        )->_(
            'html',
            'dataHtml',
            null,
            [
                'html' => $item->_('dataHtml'),
            ]
        )->_(
            'text',
            'Category slug delimiter',
            'slugCategoryDelimiter',
            [
                'required'   => 'required',
                'value'      => '/',
                'inputClass' => 'noLengthCheck',
            ]
        )->_(
            'text',
            '%ADMIN_SLUG_DELIMITER%',
            'slugDelimiter',
            [
                'required'   => 'required',
                'value'      => '-',
                'inputClass' => 'noLengthCheck',
            ]
        )->_(
            'checkbox',
            '%ADMIN_SITEMAP_INCLUDE%',
            'sitemap'
        );

        if (empty($item->_('parentId'))) :
            $this->_(
                'checkbox',
                '%ADMIN_SORTABLE_LIST%',
                'sortable'
            );
        endif;

        $this->addSubmitButton('%CORE_SAVE%');
    }
}

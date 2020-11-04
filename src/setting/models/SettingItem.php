<?php

namespace VitesseCms\Setting\Models;

use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Setting\AbstractSetting;
use VitesseCms\Setting\Forms\SettingForm;

/**
 * Class SettingImage
 */
class SettingItem extends AbstractSetting
{

    /**
     * {@inheritdoc}
     */
    public function buildAdminForm(SettingForm $form, Setting $item) {
        $this->setBaseOptions();
        $this->setOption('multilang', false);
        $this->setOption('inputClass', 'select2-ajax');
        $this->setOption('data-url', '/content/index/search/');

        if($item->_('value')) :
            $selectedItem = Item::findById($item->_('value'));
            if($selectedItem) :
                $itemPath = ItemHelper::getPathFromRoot($selectedItem);
                $options[] = [
                    'value'    => (string)$selectedItem->getId(),
                    'label'    => implode(' - ', $itemPath),
                    'selected' => true,
                ];
                $this->setOption('options', $options);
            endif;
        endif;

        $form->_(
            'select',
            '%ADMIN_VALUE%',
            'value',
            $this->getOptions()
        );
    }
}

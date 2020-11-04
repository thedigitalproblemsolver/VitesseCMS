<?php declare(strict_types=1);

namespace VitesseCms\Shop\Forms;

use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Shop\Models\Ean;
use Phalcon\Tag;

class EanForm extends AbstractForm
{
    public function initialize(Ean $ean): void
    {
        $html = '';
        $options = [
            [
                'value'    => '',
                'label'    => '%ADMIN_TYPE_TO_SEARCH%',
                'selected' => false,
            ],
        ];
        if ($ean->_('parentItem')) :
            /** @var Item $selectedItem */
            $selectedItem = Item::findById($ean->_('parentItem'));
            $itemPath = ItemHelper::getPathFromRoot($selectedItem);
            $options[] = [
                'value'    => (string)$selectedItem->getId(),
                'label'    => implode(' - ', $itemPath),
                'selected' => true,
            ];
            $html = Tag::linkTo([
                'action' => $selectedItem->_('slug'),
                'text'   => 'View item',
                'target' => '_new'
            ]);
        endif;
        $this->_(
            'text',
            '%CORE_NAME%',
            'name',
            ['required' => 'required']
        )->_(
            'select',
            'Parent item',
            'parentItem',
            [
                'options'    => $options,
                'inputClass' => 'select2-ajax',
                'data-url'   => '/admin/shop/adminean/search/',
            ]
        )->_(
            'html',
            'html',
            'html',
            [
                'html' => $html
            ]
        )->_(
            'text',
            'SKU',
            'sku',
            ['required' => 'required']
        )->_(
            'submit',
            '%CORE_SAVE%'
        );
    }
}

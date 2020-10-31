<?php declare(strict_types=1);

namespace VitesseCms\Shop\Controllers;

use VitesseCms\Content\Models\Item;
use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Shop\Factories\EanFactory;
use VitesseCms\Shop\Forms\EanForm;
use VitesseCms\Shop\Models\Ean;

class AdmineanController extends AbstractAdminController
{
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = Ean::class;
        $this->classForm = EanForm::class;
    }

    public function searchAction(): void
    {
        $result = ['items' => []];

        if ($this->request->isAjax() && \strlen($this->request->get('search')) > 1) :
            Item::setFindValue(
                'name.'.$this->configuration->getLanguageShort(),
                $this->request->get('search'),
                'like'
            );
            Item::setFindValue('ean', ['$nin' => [null, '']]);
            $items = Item::findAll();

            if ($items) :
                foreach ($items as $item) :
                    /** @var Item $item */
                    $path = ItemHelper::getPathFromRoot($item);
                    $tmp = [
                        'id'   => (string)$item->getId(),
                        'name' => implode(' - ', $path),
                    ];
                    $result['items'][] = $tmp;
                endforeach;
            endif;
        endif;

        $this->prepareJson($result);
    }

    /**
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function reloadAction(): void
    {
        Item::setFindValue('ean', ['$nin' => [null, '']]);
        $items = Item::findAll();
        foreach ($items as $item):
            Ean::setFindValue('name', (string)$item->_('ean'));
            Ean::setFindPublished(false);
            if (Ean::count() === 0):
                EanFactory::create(
                    (string)$item->_('ean'),
                    (string)$item->getId(),
                    '',
                    true
                )->save();
            endif;

            if (\is_array($item->_('variations'))) :
                foreach ($item->_('variations') as $variation) :
                    if (!empty($variation['ean']) && !empty($variation['sku'])) :
                        Ean::setFindValue('name', $variation['ean']);
                        Ean::setFindPublished(false);
                        if (Ean::count() === 0):
                            EanFactory::create(
                                $variation['ean'],
                                (string)$item->getId(),
                                $variation['sku'],
                                true
                            )->save();
                        endif;
                    endif;
                endforeach;
            endif;
        endforeach;

        $this->redirect();
    }
}

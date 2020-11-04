<?php declare(strict_types=1);

namespace VitesseCms\Install\Forms;

use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;

class MenuForm extends AbstractForm
{
    public function build(ItemRepository $itemRepository): MenuForm
    {
        $this->addDropdown(
            'Menuitems',
            'items',
            (new Attributes())
                ->setRequired(true)
                ->setOptions(ElementHelper::modelIteratorToOptions($itemRepository->findAll(null, false)))
                ->setInputClass('select2-sortable')
                ->setMultiple(true)

        )
            ->addSubmitButton('create');

        return $this;
    }
}

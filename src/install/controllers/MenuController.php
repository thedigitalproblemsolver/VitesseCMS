<?php declare(strict_types=1);

namespace VitesseCms\Install\Controllers;

use VitesseCms\Block\Models\BlockItemlist;
use VitesseCms\Install\AbstractCreatorController;
use VitesseCms\Install\Forms\MenuForm;
use VitesseCms\Install\Interfaces\AdminRepositoriesInterface;

class MenuController extends AbstractCreatorController implements AdminRepositoriesInterface
{
    public function createAction(): void
    {
        $this->view->setVar(
            'content',
            (new MenuForm())
                ->build($this->repositories->item)
                ->renderForm('admin/install/menu/parseCreateForm')
        );
        $this->prepareView();
    }

    public function parseCreateFormAction()
    {
        $blocks = [
            'MainMenu' => [
                'block'         => BlockItemlist::class,
                'template'      => 'template/core/views/blocks/Itemlist/vertical_menu',
                'position'      => 'menu',
                'datagroup'     => 'all',
                'blockSettings' => [
                    'listMode' => ['value' => 'handpicked'],
                    'items'    => ['value' => $this->request->get('items')],
                ],
            ],
        ];

        $this->createBlocks(
            $blocks,
            'name.'.$this->configuration->getLanguageShort()
        );

        $this->flash->success('Menu created');

        $this->redirect('admin/install/sitecreator/index');

    }
}

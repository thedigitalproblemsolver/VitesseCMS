<?php declare(strict_types=1);

namespace VitesseCms\Install\Controllers;

use VitesseCms\Block\Models\BlockFilter;
use VitesseCms\Block\Models\BlockFilterResult;
use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Install\AbstractCreatorController;

class FilterController extends AbstractCreatorController
{
    public function createAction()
    {
        /**
         * create filter pages
         */
        //TODO create pages in contentController?
        Datagroup::setFindValue('name.' . $this->configuration->getLanguageShort(), 'Pagina');
        Datagroup::setFindValue('component', 'content');
        /** @var Datagroup $datagroup */
        $datagroup = Datagroup::findFirst();

        $pages = [
            'Filter resultaten',
        ];
        $pages = $this->_createItems(
            $pages,
            'name.' . $this->configuration->getLanguageShort(),
            $datagroup,
            0
        );

        /**
         * filter result block
         */
        $blocks = [
            'Filter - resultaten' => [
                'block'         => BlockFilterResult::class,
                'template'      => '../../../../../template/core/views/blocks/FilterResult/core',
                'blockSettings' => [
                    'class' => 'container-filter-result',
                ],
                'position'      => 'maincontent',
                'datagroup'     => 'page:' . $pages['pages']['Filter resultaten'],
            ],
        ];
        $this->_createBlocks($blocks, 'name.' . $this->configuration->getLanguageShort());
        /**
         * create filter
         */
        $blocks = [
            'Filter' => [
                'block'         => BlockFilter::class,
                'template'      => '../../../../../template/core/views/blocks/Filter/core',
                'blockSettings' => [
                    'class' => 'container-filter',
                    'targetPage' => $pages['pages']['Filter resultaten']
                ],
                'position'      => 'left',
                'datagroup'     => 'all',
            ],
        ];
        $this->_createBlocks($blocks, 'name.' . $this->configuration->getLanguageShort());

        $this->flash->setSucces('Filter items created');
        $this->redirect();
    }
}

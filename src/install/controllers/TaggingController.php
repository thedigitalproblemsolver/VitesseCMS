<?php declare(strict_types=1);

namespace VitesseCms\Install\Controllers;

use VitesseCms\Block\Enum\ItemListEnum;
use VitesseCms\Block\Models\BlockDatagroup;
use VitesseCms\Block\Models\BlockItemlist;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Datafield\Enums\FieldEnum;
use VitesseCms\Install\AbstractCreatorController;

class TaggingController extends AbstractCreatorController
{
    public function createAction(): void
    {
        $taggingDatagroups = $this->createTaggingDatagroups();

        $contentDatagroup = $this->createContentDatagroup([
            'Tags' => [
                'calling_name'      => 'tags',
                'type'              => FieldEnum::TYPE_DATAGROUP,
                'datafieldSettings' => [
                    'datagroup' => (string)$taggingDatagroups['child']->getId(),
                    'multiple'  => true,
                ],
            ],
        ]);

        $this->createItems(
            [
                'Tagging preview' => [],
            ],
            'name.'.$this->configuration->getLanguageShort(),
            $contentDatagroup
        );


        $taggingItems = $this->createTaggingItems($taggingDatagroups);
        $this->createTaggingBlocks(
            (new ItemRepository())->getHomePage()->getDatagroup(),
            (string)$contentDatagroup->getId(),
            $taggingItems,
            (string)$taggingDatagroups['parent']->getId(),
            (string)$taggingDatagroups['child']->getId()
        );

        $this->flash->success('Tagging system created');

        $this->redirect('admin/install/sitecreator/index');

    }

    protected function createTaggingDatagroups(): array
    {
        $fields = [
            'Item naam' => [
                'calling_name'      => 'name',
                'type'              => FieldEnum::TYPE_TEXT,
                'datafieldSettings' => [
                    'inputType' => 'text',
                    'multilang' => true,
                ],
                'required'          => true,
                'slug'              => true,
                'slugCategory'      => true,
                'seoTitle'          => true,
            ],
        ];
        $fieldIds = $this->createDatafields($fields, 'calling_name');

        $return = [];
        $return['parent'] = $this->createDatagroup(
            'Tagging',
            'name.'.$this->configuration->getLanguageShort(),
            'template/core/views/blocks/MainContent/default_full_width',
            'content',
            $fieldIds,
            true
        );

        $return['child'] = $this->createDatagroup(
            'Tag',
            'name.'.$this->configuration->getLanguageShort(),
            'template/core/views/blocks/MainContent/default_full_width',
            'content',
            $fieldIds,
            true,
            (string)$return['parent']->getId()
        );

        return $return;
    }

    protected function createTaggingItems(array $taggingDatagroups): array
    {
        $taggingItems = [];
        $taggingItems['parent'] = $this->createItems(
            ['Tags' => []],
            'name.'.$this->configuration->getLanguageShort(),
            $taggingDatagroups['parent']
        );

        $taggingItems['child'] = $this->createItems(
            [
                'First tag'  => [],
                'Second tag' => [],
            ],
            'name.'.$this->configuration->getLanguageShort(),
            $taggingDatagroups['child'],
            (string)$taggingItems['parent']['ids'][0]
        );

        return $taggingItems;
    }

    protected function createTaggingBlocks(
        string $homepageDatagroup,
        string $contentDatagroupId,
        array $taggingItems,
        string $tagParentDatagroupId,
        string $tagChildDatagroupId
    ): void {
        $datafieldTag = $this->repositories->datafield->findFirst(
            (new FindValueIterator([new FindValue('calling_name', 'tags')]))
        );

        $blocks = [
            'Tagging - labels of all tags'    => [
                'block'         => BlockItemlist::class,
                'template'      => 'template/core/views/blocks/Itemlist/badges',
                'position'      => 'right',
                'datagroup'     => $homepageDatagroup,
                'blockSettings' => [
                    'listMode'   => ['value' => 'childrenOfItem'],
                    'submitText' => ['value' => 'name'],
                    'item'       => ['value' => (string)$taggingItems['parent']['ids'][0]],
                ],
            ],
            'Tagging - introtext of all tags' => [
                'block'         => BlockItemlist::class,
                'template'      => 'template/core/views/blocks/Itemlist/introtext',
                'position'      => 'belowMaincontent',
                'datagroup'     => $tagParentDatagroupId,
                'blockSettings' => [
                    'listMode'            => ['value' => 'currentChildren'],
                    'readmoreText'        => '%CORE_READ_MORE%',
                    'readmoreShowPerItem' => ['value' => true],
                ],
            ],
            'Tagging - items of active tag'   => [
                'block'         => BlockItemlist::class,
                'template'      => 'template/core/views/blocks/Itemlist/introtext',
                'position'      => 'belowMaincontent',
                'datagroup'     => $tagChildDatagroupId,
                'blockSettings' => [
                    'listMode'                 => ['value' => 'datagroups'],
                    'displayOrdering'          => ['value' => 'createdAt'],
                    'displayOrderingDirection' => ['value' => 'newest'],
                    'submitText'               => ['value' => 'name'],
                    'readmoreText'             => '%CORE_READ_MORE%',
                    'readmoreShowPerItem'      => ['value' => true],
                    'items'                    => ['value' => [$contentDatagroupId]],
                    'datafieldValue'           => [
                        'value' => [
                            'tags' => ItemListEnum::OPTION_CURRENT_ITEM,
                        ],
                    ],
                ],
            ],
            'Tagging - tags of current item ' => [
                'block'         => BlockDatagroup::class,
                'template'      => 'template/core/views/blocks/Datagroup/badges',
                'position'      => 'belowMaincontent',
                'datagroup'     => $contentDatagroupId,
                'blockSettings' => [
                    'datagroup' => ['value' => $contentDatagroupId],
                    'datafield' => ['value' => (string)$datafieldTag->getId()],
                ],
            ],
        ];

        $this->createBlocks($blocks, 'name.'.$this->configuration->getLanguageShort());
    }
}

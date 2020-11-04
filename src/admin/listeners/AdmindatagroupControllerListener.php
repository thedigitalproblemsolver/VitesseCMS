<?php declare(strict_types=1);

namespace VitesseCms\Admin\Listeners;

use VitesseCms\Core\Controllers\AdmindatagroupController;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Core\Models\Datagroup;
use Phalcon\Events\Event;
use \stdClass;

class AdmindatagroupControllerListener
{
    public function beforeEdit(Event $event, AdmindatagroupController $controller, Datagroup $datagroup): void
    {
        $rows = $slugRows = $seoTitleRows = [];

        $link = $dataHtml = '';
        if ($controller->dispatcher->getParam(0) !== null) :
            $link = $controller->url->getBaseUri() .
                'admin/' .
                $controller->router->getModuleName() .
                '/' . $controller->router->getControllerName();

            $slugDatafields = $datagroup->getSlugDatafields();
            $seoTitleDatafields = $datagroup->getSeoTitleDatafields();

            foreach ($datagroup->getDatafields() as $key => $field) :
                $model = $controller->repositories->datafield->getById($field['id']);
                if ($model !== null) :
                    if (!isset($field['required'])) :
                        $field['required'] = false;
                    endif;

                    if (!isset($field['filterable'])) :
                        $field['filterable'] = false;
                    endif;

                    if (!isset($field['exportable'])) :
                        $field['exportable'] = false;
                    endif;

                    if (!isset($field['slugPublished'])) {
                        $field['slugPublished'] = false;
                    }

                    if (!isset($field['seoTitlePublished'])) {
                        $field['seoTitlePublished'] = false;
                    }

                    $row = new stdClass();
                    $row->rowState = ItemHelper::getRowStateClass($field['published']);
                    $row->slugRowState = ItemHelper::getRowStateClass($field['slugPublished']);
                    $row->seotitleRowState = ItemHelper::getRowStateClass($field['seoTitlePublished']);
                    $row->name = $model->_('name');
                    $row->fieldId = $field['id'];
                    $row->key = $key;
                    $row->buttons = [
                        [
                            'text'   => ItemHelper::getPublishText($field['published']),
                            'icon'   => ItemHelper::getPublishIcon($field['published']),
                            'action' => 'togglePublishDatafield',
                            'rowId'  => 'publish_' . $field['id'],
                        ],
                        [
                            'text'   => 'Filterable',
                            'icon'   => ItemHelper::getIcon($field['filterable'], 'filter '),
                            'action' => 'toggleFilterableDatafield',
                        ],
                        [
                            'text'   => 'Exportable',
                            'icon'   => ItemHelper::getIcon($field['exportable'], 'table '),
                            'action' => 'toggleExportableDatafield',
                        ],
                        [
                            'text'   => ItemHelper::getRequiredText($field['required']),
                            'icon'   => ItemHelper::getRequiredIcon($field['required']),
                            'action' => 'toggleRequiredDatafield',
                        ],

                    ];
                    $row->slugButtons = [
                        [
                            'text'   => ItemHelper::getPublishText($field['slugPublished']),
                            'icon'   => ItemHelper::getPublishIcon($field['slugPublished']),
                            'action' => 'togglePublishSlug',
                        ],
                    ];

                    $row->seoTitleButtons = [
                        [
                            'text'   => ItemHelper::getPublishText($field['seoTitlePublished']),
                            'icon'   => ItemHelper::getPublishIcon($field['seoTitlePublished']),
                            'action' => 'togglePublishSeoTitle',
                        ],
                    ];

                    $rows[] = $row;

                    if (!isset($seoTitleDatafields[$field['id']])) :
                        $seoTitleDatafields[$field['id']] = new stdClass();
                        $seoTitleDatafields[$field['id']]->id = $field['id'];
                        $seoTitleDatafields[$field['id']]->published = false;
                    endif;

                    if (!isset($slugDatafields[$field['id']])) :
                        $slugDatafields[$field['id']] = new stdClass();
                        $slugDatafields[$field['id']]->id = $field['id'];
                        $slugDatafields[$field['id']]->published = false;
                    endif;
                endif;
            endforeach;

            foreach ($seoTitleDatafields as $key => $field) :
                $field = (array)$field;
                $model = $controller->repositories->datafield->getById($field['id']);
                if ($model !== null) :
                    if (!isset($field['published'])) :
                        $field['published'] = false;
                    endif;

                    $row = new stdClass();
                    $row->rowState = ItemHelper::getRowStateClass($field['published']);
                    $row->name = $model->_('name');
                    $row->fieldId = 'seoTitle' . $field['id'];
                    $row->key = $key;
                    $row->buttons = [
                        [
                            'text'   => ItemHelper::getPublishText($field['published']),
                            'icon'   => ItemHelper::getPublishIcon($field['published']),
                            'action' => 'togglePublishSeoTitle',
                            'rowId'  => 'publish_seoTitle' . $field['id'],
                        ],
                    ];

                    $seoTitleRows[] = $row;
                endif;
            endforeach;

            foreach ($slugDatafields as $key => $field) :
                $field = (array)$field;
                $model = $controller->repositories->datafield->getById($field['id']);
                if ($model !== null) :
                    if (!isset($field['published'])) :
                        $field['published'] = false;
                    endif;

                    $row = new stdClass();
                    $row->rowState = ItemHelper::getRowStateClass($field['published']);
                    $row->name = $model->_('name');
                    $row->fieldId = 'slug' . $field['id'];
                    $row->key = $key;
                    $row->buttons = [
                        [
                            'text'   => ItemHelper::getPublishText($field['published']),
                            'icon'   => ItemHelper::getPublishIcon($field['published']),
                            'action' => 'togglePublishSlug',
                            'rowId'  => 'publish_slug' . $field['id'],
                        ],
                    ];

                    $slugRows[] = $row;
                endif;
            endforeach;
        endif;

        $dataHtml = $controller->view->renderTemplate(
            'adminDatagroupFieldlist',
            $controller->configuration->getRootDir() . 'src/core/resources/views/',
            [
                'id' => $datagroup->getId(),
                'tableId' => uniqid('',false),
                'rows' => $rows,
                'baseLink' => $link,
            ]
        );

        $dataHtml .= $controller->view->renderTemplate(
            'adminDatagroupSluglist',
            $controller->configuration->getRootDir() . 'src/core/resources/views/',
            [
                'rows' => $slugRows,
                'categories' => $controller->getSlugCategories($datagroup),
                'tableId' => uniqid('',false)
            ]
        );

        $dataHtml .=$controller->view->renderTemplate(
            'adminDatagroupSeoTitlelist',
            $controller->configuration->getRootDir() . 'src/core/resources/views/',
            [
                'rows' => $seoTitleRows,
                'categories' => $controller->getSeoTitleCategories($datagroup),
                'tableId' => uniqid('',false)
            ]
        );

        $datagroup->set('dataHtml', $dataHtml);
    }
}

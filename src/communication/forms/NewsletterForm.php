<?php

namespace VitesseCms\Communication\Forms;

use VitesseCms\Communication\Models\Newsletter;
use VitesseCms\Communication\Models\NewsletterList;
use VitesseCms\Communication\Models\NewsletterTemplate;
use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Core\Utils\SystemUtil;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Language\Models\Language;

/**
 * Class NewsletterForm
 */
class NewsletterForm extends AbstractForm
{

    /**
     * initialize
     *
     * @param Newsletter|null $item
     */
    public function initialize(?Newsletter $item = null): void
    {
        if ($item === null || empty($item->_('parentId'))) {
            $this->buildParentForm($item);
        } else {
            $this->buildChildForm($item);
        }
        if ($this->request->get('parentId', null)) :
            $this->_(
                'hidden',
                null,
                'parentId',
                [
                    'value' => $this->request->get('parentId'),
                ]
            );
        endif;
    }

    /**
     * @param Newsletter|null $item
     */
    protected function buildParentForm(?Newsletter $item = null): void
    {
        $showSendNewsletterList = false;

        $this->_(
            'text',
            '%CORE_NAME%',
            'name',
            ['required' => 'required']
        )->_(
            'select',
            '%ADMIN_LANGUAGE%',
            'language',
            [
                'required' => 'required',
                'options'  => ElementHelper::arrayToSelectOptions(Language::findAll()),
            ]
        );

        if ($item->_('language')) :
            NewsletterList::setFindValue('language', $item->_('language'));
            $this->_(
                'select',
                'Newsletter list',
                'list',
                [
                    'options' => ElementHelper::arrayToSelectOptions(NewsletterList::findAll()),
                ]
            );
            if (!$item->_('hasChildren')) :
                $this->setBodyText($item);
            endif;
            $showSendNewsletterList = true;
        endif;

        $this->_(
            'checkbox',
            'Start sending after subscribtion',
            'sendAfterSubscribtion'
        )->_(
            'submit',
            '%CORE_SAVE%'
        )->_(
            'html',
            '',
            '',
            [
                'html' => '<span id="newsletterId" style="display:none">' . $item->getId() . '</span>',
            ]
        );

        if ($showSendNewsletterList) :
            if (!$item->_('hasChildren')) :
                $this->_(
                    'text',
                    'Senddate',
                    'sendDate',
                    ['inputType' => 'date']
                )->_(
                    'text',
                    'Sendtime',
                    'sendTime',
                    ['inputType' => 'time']
                );
            endif;
            if ($item->_('published')) :
                $this->_(
                    'button',
                    'Place newsletter in  queue',
                    'queueNewsletter'
                );
            endif;
        endif;
    }

    /**
     * @param Newsletter|null $item
     */
    protected function buildChildForm(?Newsletter $item = null): void
    {
        Newsletter::setFindPublished(false);
        $parentNewsletter = Newsletter::findById($item->_('parentId'));
        $item->set('language', $parentNewsletter->_('language'));
        $item->set('list', $parentNewsletter->_('list'));

        $this->_(
            'text',
            '%CORE_NAME%',
            'name',
            ['required' => 'required']
        )->_(
            'hidden',
            '',
            'language',
            ['value' => $parentNewsletter->_('language')]
        )->_(
            'hidden',
            '',
            'list',
            ['value' => $parentNewsletter->_('list')]
        );

        if ($item !== null && $item->_('language')) :
            $this->setBodyText($item);
        endif;

        $this->_(
            'number',
            'days after previous mail',
            'days'
        )->_(
            'text',
            'Sendtime',
            'sendTime',
            ['inputType' => 'time']
        );

        $this->_(
            'submit',
            '%CORE_SAVE%'
        )->_(
            'html',
            '',
            '',
            [
                'html' => '<span id="newsletterId" style="display:none">' . $item->getId() . '</span>',
            ]
        );
    }

    /**
     * @param Newsletter $item
     */
    protected function setBodyText(Newsletter $item): void
    {
        NewsletterTemplate::setFindValue('language', $item->_('language'));
        $this->_(
            'select',
            '%ADMIN_CHOOSE_A_TEMPLATE%',
            'template',
            [
                'options' => ElementHelper::arrayToSelectOptions(NewsletterTemplate::findAll()),
            ]
        );
        $files = SystemUtil::getTemplateFiles('../emails');
        $options = [];
        foreach ($files as $key => $label) :
            $selected = false;
            if ($item->_('emailType') === $key) :
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
            'Email type',
            'emailType',
            [
                'required' => 'required',
                'options'  => $options,
            ]
        )->_(
            'text',
            'Subject',
            'subject',
            ['required' => 'required']
        )->_(
            'text',
            'Motto',
            'motto',
            ['required' => 'required']
        );

        if ($item->_('template')) :
            $options = [
                [
                    'value'    => '',
                    'label'    => '%ADMIN_TYPE_TO_SEARCH%',
                    'selected' => false,
                ],
            ];

            if ($item->_('emailHeaderTargetPage')) :
                /** @var Item $selectedItem */
                $selectedItem = Item::findById($item->_('emailHeaderTargetPage'));
                $itemPath = ItemHelper::getPathFromRoot($selectedItem);
                $options[] = [
                    'value'    => (string)$selectedItem->getId(),
                    'label'    => implode(' - ', $itemPath),
                    'selected' => true,

                ];
            endif;

            $this->_(
                'textarea',
                'Body',
                'body',
                [
                    'required'   => 'required',
                    'inputClass' => 'editor',
                ]
            )->_(
                'file',
                'Header Image',
                'emailHeaderImage',
                [
                    'template' => 'filemanager',
                ]
            )->_(
                'select',
                'Header Target page',
                'emailHeaderTargetPage',
                [
                    'options'    => $options,
                    'inputClass' => 'select2-ajax',
                    'data-url'   => '/content/index/search/',
                ]
            );
        endif;

        $this->_(
            'text',
            'Send preview to',
            'previewEmail',
            [
                'type' => 'email',
            ]
        )->_(
            'button',
            'Send preview',
            'sendPreviewEmail'
        );
    }
}

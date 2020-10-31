<?php

namespace VitesseCms\Communication\Forms;

use VitesseCms\Communication\Models\Newsletter;
use VitesseCms\Communication\Models\NewsletterList;
use VitesseCms\Communication\Models\NewsletterQueue;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\User\Models\User;

/**
 * Class NewsletterQueueForm
 */
class NewsletterQueueForm extends AbstractForm
{

    /**
     * initialize
     *
     * @param NewsletterQueue|null $item
     */
    public function initialize(NewsletterQueue $item = null)
    {
        $this->_(
            'text',
            '%CORE_EMAIL%',
            'email',
            [
                'required'  => 'required',
                'type' => 'email',
                'readonly' => 'readonly'
            ]
        )->_(
            'select',
            'User',
            'userId',
            [
                'required'  => 'required',
                'options' => ElementHelper::arrayToSelectOptions(User::findAll()),
                'disabled' => 'disabled'
            ]
        )->_(
            'select',
            'Newsletter',
            'newsletterId',
            [
                'required'  => 'required',
                'options' => ElementHelper::arrayToSelectOptions(Newsletter::findAll()),
                'disabled' => 'disabled'
            ]
        )->_(
            'select',
            'Newsletter list',
            'newsletterListId',
            [
                'required'  => 'required',
                'options' => ElementHelper::arrayToSelectOptions(NewsletterList::findAll()),
                'disabled' => 'disabled'
            ]
        )->_(
            'text',
            'Date sending',
            'dateSending',
            [
                'type' => 'date',
                'readonly' => 'readonly'
            ]
        )->_(
            'text',
            'Date sent',
            'dateSent',
            [
                'type' => 'date',
                'readonly' => 'readonly'
            ]
        )->_(
            'text',
            'Date opened',
            'dateOpened',
            [
                'type' => 'date',
                'readonly' => 'readonly'
            ]
        )->_(
            'text',
            'Subject',
            'subject',
            ['readonly' => 'readonly']
        )->_(
            'text',
            'Job Queue id',
            'jobId',
            ['readonly' => 'readonly']
        );
    }
}

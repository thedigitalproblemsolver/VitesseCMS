<?php declare(strict_types=1);

namespace VitesseCms\Install\Controllers;

use VitesseCms\Block\Models\BlockFormBuilder;
use VitesseCms\Block\Models\BlockNewsletterSubscriptions;
use VitesseCms\Communication\Factories\NewsletterFactory;
use VitesseCms\Communication\Factories\NewsletterListFactory;
use VitesseCms\Communication\Factories\NewsletterTemplateFactory;
use VitesseCms\Communication\Models\Newsletter;
use VitesseCms\Communication\Models\NewsletterList;
use VitesseCms\Communication\Models\NewsletterTemplate;
use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Setting\Models\Setting;
use VitesseCms\Datafield\Models\FieldText;
use VitesseCms\Install\AbstractCreatorController;
use VitesseCms\Install\Forms\ContactForm;
use VitesseCms\Language\Models\Language;

class NewsletterController extends AbstractCreatorController
{
    /**
     * create elements
     */
    public function createAction(): void
    {
        if (
            !$this->setting->has('WEBSITE_CONTACT_EMAIL')
            || !$this->setting->has('WEBSITE_DEFAULT_NAME')
        ) :
            $this->view->setVar('content', (new ContactForm())->renderForm('admin/install/newsletter/parseCreateForm'));
            $this->prepareView();
        else :
            parent::redirect('admin/install/newsletter/doCreate');
        endif;
    }

    /**
     * parseCreateFormAction
     */
    public function parseCreateFormAction(): void
    {
        if ((new ContactForm())->validate($this)) :
            if (!$this->setting->has('WEBSITE_DEFAULT_NAME')) :
                Setting::setFindPublished(false);
                Setting::setFindValue('calling_name', 'WEBSITE_DEFAULT_NAME');
                $setting = Setting::findFirst();
                $setting->set('value', $this->request->getPost('WEBSITE_DEFAULT_NAME'), true);
                $setting->save();
            endif;
            if (!$this->setting->has('WEBSITE_CONTACT_EMAIL')) :
                Setting::setFindPublished(false);
                Setting::setFindValue('calling_name', 'WEBSITE_CONTACT_EMAIL');
                $setting = Setting::findFirst();
                $setting->set('value', $this->request->getPost('WEBSITE_CONTACT_EMAIL'), true);
                $setting->save();
            endif;

            parent::redirect('admin/install/newsletter/doCreate');
        else :
            $this->flash->error('Error in form');
            parent::redirect('admin/install/sitecreator/index');
        endif;
    }

    /**
     * doCreateAction
     */
    public function doCreateAction(): void
    {
        $this->createNewsletterList();
        $this->createNewsletterTemplate();
        $this->createNewsletter();

        $datagroup = $this->createNewsletterForm();
        $this->createNewsletterBlock($datagroup);

        $this->flash->success('Newsletter items created');
        parent::redirect('admin/install/sitecreator/index');
    }

    /**
     * createNewsletterTemplate
     */
    protected function createNewsletterTemplate(): void
    {
        /** @var Language $language */
        foreach (Language::findAll() as $language) :
            NewsletterTemplate::setFindPublished(false);
            NewsletterList::setFindValue('language', (string)$language->getId());
            if(NewsletterTemplate::count() === 0 ) :
                NewsletterTemplateFactory::create(
                    'Demo template - '.$language->_('short'),
                    $language,
                    '<p>Dit is een demo template</p><p>{UNSUBSCRIBE}Uitschrijven{/UNSUBSCRIBE}</p>',
                    true
                )->save();
            endif;
        endforeach;
    }

    /**
     * createNewsletterList
     */
    protected function createNewsletterList(): void
    {
        /** @var Language $language */
        foreach (Language::findAll() as $language) :
            NewsletterList::setFindPublished(false);
            NewsletterList::setFindValue('language', (string)$language->getId());
            if(NewsletterList::count() === 0) :
                NewsletterListFactory::create(
                    'Demo lijst - '.$language->_('short'),
                    $language,
                    true
                )->save();
            endif;
        endforeach;
    }
    
    protected function createNewsletter(): void
    {
        /** @var Language $language */
        foreach (Language::findAll() as $language) :
            Newsletter::setFindPublished(false);
            Newsletter::setFindValue('language', (string)$language->getId());
            if(Newsletter::count() === 0) :
                NewsletterList::setFindPublished(false);
                NewsletterList::setFindValue('language', (string)$language->getId());
                /** @var NewsletterList $newsletterList */
                $newsletterList = NewsletterList::findFirst();

                NewsletterTemplate::setFindPublished(false);
                NewsletterTemplate::setFindValue('language', (string)$language->getId());
                /** @var NewsletterTemplate $newsletterTemplate */
                $newsletterTemplate = NewsletterTemplate::findFirst();

                NewsletterFactory::create(
                    'Demo nieuwsbrief - '.$language->_('short'),
                    $language,
                    $newsletterList,
                    $newsletterTemplate,
                    'Demo nieuwsbrief - '.$language->_('short'),
                    '',
                    true
                )->save();
            endif;
        endforeach;
    }

    /**
     * createNewsletterForm
     */
    protected function createNewsletterForm(): Datagroup
    {
        $lists = [];
        /** @var Language $language */
        foreach (Language::findAll() as $language) :
            NewsletterList::setFindPublished(false);
            NewsletterList::setFindValue('language', (string)$language->getId());
            $newsletterList = NewsletterList::findFirst();
            $lists[$language->_('short')] = (string)$newsletterList->getId();
        endforeach;

        $fields = [
            'E-mail'  => [
                'calling_name'      => 'email',
                'type'              => FieldText::class,
                'datafieldSettings' => [
                    'inputType' => 'email',
                ],
                'required'          => true,
            ],
            'Nieuwsbrief lijst' => [
                'calling_name'      => 'newsletterList',
                'type'              => FieldText::class,
                'datafieldSettings' => [
                    'inputType' => 'hidden',
                    'multilang' => true,
                    'defaultValue' => $lists
                ],
                'required'          => true,
            ],
        ];
        $fieldIds = $this->createDatafields($fields, 'calling_name');

        return $this->createDatagroup(
            'Inschrijven nieuwsbrief',
            'name.' . $this->configuration->getLanguageShort(),
            'template/core/views/blocks/MainContent/core',
            'form',
            $fieldIds
        );
    }

    /**
     * @param Datagroup $datagroup
     */
    protected function createNewsletterBlock(Datagroup $datagroup): void
    {
        $blocks = [
            'Formulier - inschrijven nieuwsbrief' => [
                'block'         => BlockFormBuilder::class,
                'template'      => 'template/core/views/blocks/FormBuilder/main_content',
                'position'      => 'belowMaincontent',
                'datagroup'     => 'all',
                'blockSettings' => [
                    'pageThankyou' => [
                        'value'     => '<p>Bedankt voor je inschrijving op onze nieuwsbrief.</p>',
                        'multilang' => true,
                    ],
                    'submitText'   => [
                        'value'     => 'Schrijf mij in op de nieuwsbrief',
                        'multilang' => true,
                    ],
                    'datagroup'    => [
                        'value' => (string)$datagroup->getId(),
                    ],
                    'postUrl'   => [
                        'value' => 'communication/newsletterlist/addmember/',
                    ]
                ],
            ],
            'Mijn nieuwsbrieven' => [
                'block'     => BlockNewsletterSubscriptions::class,
                'template'  => 'template/core/views/blocks/NewsletterSubscriptions/core',
                'position'  => 'myaccount',
                'datagroup' => [],
            ]
        ];
        $this->createBlocks(
            $blocks,
            'name.' . $this->configuration->getLanguageShort()
        );
    }
}

<?php declare(strict_types=1);

namespace VitesseCms\Install\Controllers;

use VitesseCms\Block\Models\BlockFormBuilder;
use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Setting\Models\Setting;
use VitesseCms\Install\AbstractCreatorController;
use VitesseCms\Install\Forms\ContactForm;

class ContactController extends AbstractCreatorController
{
    public function createAction(): void
    {
        if (
            !$this->setting->has('WEBSITE_CONTACT_EMAIL')
            || !$this->setting->has('WEBSITE_DEFAULT_NAME')
        ) :
            $this->view->setVar('content', (new ContactForm())->renderForm('admin/install/contact/parseCreateForm'));
            $this->prepareView();
        else :
            $this->redirect('admin/install/contact/doCreate');
        endif;
    }

    public function parseCreateFormAction(): void
    {
        if ((new ContactForm())->validate($this)) :
            if ($this->setting->has('WEBSITE_DEFAULT_NAME')) :
                Setting::setFindPublished(false);
                Setting::setFindValue('calling_name', 'WEBSITE_DEFAULT_NAME');
                $setting = Setting::findFirst();
                $setting->set('value', $this->request->getPost('WEBSITE_DEFAULT_NAME'), true);
                $setting->save();
            endif;
            if ($this->setting->has('WEBSITE_CONTACT_EMAIL')) :
                Setting::setFindPublished(false);
                Setting::setFindValue('calling_name', 'WEBSITE_CONTACT_EMAIL');
                $setting = Setting::findFirst();
                $setting->set('value', $this->request->getPost('WEBSITE_CONTACT_EMAIL'), true);
                $setting->save();
            endif;

            $this->redirect('admin/install/contact/doCreate');
        else :
            $this->flash->error('Error in form');
            $this->redirect('admin/install/sitecreator/index');
        endif;
    }

    public function doCreateAction(): void
    {
        $page = $this->createContentPage();
        $datagroup = $this->createContactForm();
        $this->createContactBlock($datagroup, $page);
        $this->createContactSystemEmails();

        $this->flash->success('Contact items created');

        $this->redirect('admin/install/sitecreator/index');
    }

    protected function createContentPage(): array
    {
        $datagroup = $this->createContentDatagroup();

        return $this->createItems(
            [
                'Contact' => [],
            ],
            'name.'.$this->configuration->getLanguageShort(),
            $datagroup
        );
    }

    protected function createContactForm(): Datagroup
    {
        $fields = [
            'Uw naam' => [
                'calling_name'      => 'fullName',
                'type'              => 'FieldText',
                'datafieldSettings' => [
                    'inputType' => 'text',
                ],
                'required'          => true,
            ],
            'E-mail'  => [
                'calling_name'      => 'email',
                'type'              => 'FieldText',
                'datafieldSettings' => [
                    'inputType' => 'email',
                ],
                'required'          => true,
            ],
            'Bericht' => [
                'calling_name' => 'message',
                'type'         => 'FieldTextarea',
                'required'     => true,
            ],
        ];
        $fieldIds = $this->createDatafields($fields, 'calling_name');

        return $this->createDatagroup(
            'Contact',
            'name.'.$this->configuration->getLanguageShort(),
            'template/core/views/blocks/MainContent/core',
            'form',
            $fieldIds
        );
    }

    protected function createContactBlock(Datagroup $datagroup, array $pages): void
    {
        $blocks = [
            'Formulier - contact' => [
                'block'         => BlockFormBuilder::class,
                'template'      => 'template/core/views/blocks/FormBuilder/main_content',
                'position'      => 'maincontent',
                'datagroup'     => 'page:'.$pages['ids'][0],
                'blockSettings' => [
                    'pageThankyou' => [
                        'value'     => '<p>Bedankt voor uw vraag. We nemen zo spoedig mogelijk contact met je op.</p>',
                        'multilang' => true,
                    ],
                    'submitText'   => [
                        'value'     => 'Verstuur het formulier',
                        'multilang' => true,
                    ],
                    'datagroup'    => [
                        'value' => (string)$datagroup->getId(),
                    ],
                ],
            ],
        ];
        $this->createBlocks(
            $blocks,
            'name.'.$this->configuration->getLanguageShort()
        );
    }

    protected function createContactSystemEmails(): void
    {
        $emails = [
            'Ingezonden formulier op {{BASE_URI}}'    => [
                'body'         => '<p>Een kopie van uw verzonden gegevens.<br></p><p>{{formData}}</p>',
                'systemAction' => 'formindexsubmit',
                'triggerState' => 'success',
                'published'    => true,
            ],
            'Ingezonden formulier vanaf {{BASE_URI}}' => [
                'body'                 => '<p>Er is een formulier ingezonden.</p><p>{{formAdminData}}</p>',
                'systemAction'         => 'formindexsubmit',
                'triggerState'         => 'success',
                'published'            => true,
                'alternativeRecipient' => $this->user->_('email'),
            ],
        ];

        $this->createSystemEmails(
            $emails,
            'subject.'.$this->configuration->getLanguageShort()
        );
    }
}

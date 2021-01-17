<?php declare(strict_types=1);

namespace VitesseCms\Install\Controllers;

use VitesseCms\Block\Models\BlockAffiliateInitialize;
use VitesseCms\Block\Models\BlockAffiliateOrderOverview;
use VitesseCms\Datafield\Models\Datafield;
use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Setting\Models\Setting;
use VitesseCms\Datafield\Models\FieldModel;
use VitesseCms\Install\AbstractCreatorController;
use VitesseCms\Install\Forms\AffiliateForm;
use VitesseCms\User\Models\User;

class AffiliateController extends AbstractCreatorController
{
    public function createAction(): void
    {
        if (empty($this->setting->get('SHOP_DATAGROUP_AFFILIATE'))) :
            $this->view->setVar('content',
                (new AffiliateForm())->renderForm('admin/install/affiliate/parseCreateForm'));
            $this->prepareView();
        else :
            parent::redirect('admin/install/affiliate/doCreate');
        endif;
    }

    public function parseCreateFormAction(): void
    {
        if ((new AffiliateForm())->validate($this)) :
            if (empty($this->setting->get('SHOP_DATAGROUP_AFFILIATE'))) :
                Setting::setFindPublished(false);
                Setting::setFindValue('calling_name', 'SHOP_DATAGROUP_AFFILIATE');
                Setting::findFirst()
                    ->set('value', $this->request->getPost('SHOP_DATAGROUP_AFFILIATE'), true)
                    ->set('published', true)
                    ->set('type', 'SettingDatagroup')
                    ->save();
            endif;
            parent::redirect('admin/install/affiliate/doCreate');
        else :
            $this->flash->error('Error in form');
            parent::redirect('admin/install/sitecreator/index');
        endif;
    }

    public function doCreateAction(): void
    {
        $datagroup = $this->createContent();
        $this->createAffiliateBlocks($datagroup);

        $this->flash->success('Affiliate is activated');
        parent::redirect('admin/install/sitecreator/index');
    }

    protected function createContent(): Datagroup
    {
        $fields = [
            'Affiliate gebruiker' => [
                'calling_name'      => 'affiliateUser',
                'type'              => FieldModel::class,
                'datafieldSettings' => [
                    'inputType'    => 'text',
                    'useSelect2'   => true,
                    'displayLimit' => 999,
                    'model'        => User::class,
                ],
                'required'          => true,
            ],
        ];
        $fieldIds = $this->createDatafields($fields, 'calling_name');

        /** @var Datagroup $datagroup */
        $datagroup = Datagroup::findById($this->setting->get('SHOP_DATAGROUP_AFFILIATE'));
        foreach ($fieldIds as $fieldId) :
            /** @var Datafield $datafield */
            $datafield = Datafield::findById($fieldId['id']);
            $datagroup->addDatafield($datafield);
        endforeach;
        $datagroup->save();

        return $datagroup;
    }

    protected function createAffiliateBlocks(Datagroup $datagroup): void
    {
        $blocks = [
            'Mijn wederverkopen'    => [
                'block'     => BlockAffiliateOrderOverview::class,
                'template'  => 'template/core/views/blocks/AffiliateOrderOverview/core',
                'position'  => 'myaccount',
                'datagroup' => [],
            ],
            'Affiliate initializer' => [
                'block'         => BlockAffiliateInitialize::class,
                'template'      => 'template/core/views/blocks/AffiliateInitialize/core',
                'position'      => 'footer',
                'datagroup'     => [(string)$datagroup->getId()],
                'blockSettings' => [
                    'datagroups'     => ['value' => [(string)$datagroup->getId()]],
                    'cookieLifetime' => ['value' => 30],
                ],
            ],
        ];
        $this->createBlocks(
            $blocks,
            'name.'.$this->configuration->getLanguageShort()
        );
    }
}

<?php declare(strict_types=1);

namespace VitesseCms\Install;

use VitesseCms\Block\Factories\BlockFactory;
use VitesseCms\Block\Factories\BlockPositionFactory;
use VitesseCms\Communication\Factories\EmailFactory;
use VitesseCms\Communication\Models\Email;
use VitesseCms\Content\Factories\ItemFactory;
use VitesseCms\Core\AbstractController;
use VitesseCms\Block\Models\Block;
use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Factories\DatagroupFactory;
use VitesseCms\Datafield\Models\Datafield;
use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Install\Interfaces\AdminRepositoriesInterface;
use VitesseCms\Setting\Models\Setting;
use VitesseCms\Datafield\Factories\DatafieldFactory;
use VitesseCms\Setting\Factory\SettingFactory;
use VitesseCms\User\Factories\PermissionRoleFactory;
use VitesseCms\User\Models\PermissionRole;
use VitesseCms\User\Utils\PermissionUtils;
use \stdClass;

abstract class AbstractCreatorController extends AbstractController implements AdminRepositoriesInterface
{
    /**
     * @var array
     */
    protected $settingsRepository;

    public function onConstruct()
    {
        parent::onConstruct();

        $this->settingsRepository = [];
    }

    public function initialize()
    {
        if (!PermissionUtils::check(
            $this->user,
            $this->router->getModulePrefix().$this->router->getModuleName(),
            $this->router->getControllerName(),
            $this->router->getActionName()
        )) :
            $this->flash->_('USER_NO_ACCESS', 'error');
            $this->response->redirect($this->url->getBaseUri());

            $this->disableView();
        endif;
    }

    protected function createSettings(array $settings): void
    {
        foreach ($settings as $settingKey => $params) :
            Setting::setFindValue('calling_name', $settingKey);
            Setting::setFindPublished(false);
            $settingItems = Setting::findAll();
            if (\count($settingItems) === 0) :
                $setting = SettingFactory::create(
                    $settingKey,
                    isset($params['type']) ? $params['type'] : 'SettingText',
                    isset($params['value']) ? $params['value'] : '',
                    isset($params['name']) ? $params['name'] : '',
                    true
                );
                $setting->save();
            endif;
        endforeach;
    }

    protected function createDatagroup(
        string $title,
        string $titleField,
        string $template,
        string $component,
        array $datafields = [],
        bool $includeInSitemap = false,
        string $parentId = null,
        string $itemOrdering = ''
    ): Datagroup {
        $datagroup = $this->repositories->datagroup->findFirst(
            new FindValueIterator([
                new FindValue($titleField, $title),
                new FindValue('component', $component),
            ]),
            false
        );

        $slugDatafields = [];
        $slugCategoryDatafields = [];
        $seoTitleDatafields = [];
        if ($datagroup === null) :
            $datagroup = DatagroupFactory::create(
                $title,
                $template,
                $component,
                $datafields,
                true,
                $includeInSitemap,
                $parentId,
                $itemOrdering
            );
        else :
            $slugDatafields = $datagroup->getSlugDatafields();
            $slugCategoryDatafields = $datagroup->getSlugCategories();
            $seoTitleDatafields = $datagroup->getSeoTitleDatafields();
            $datagroup->set('datafields', $datafields);
        endif;

        /** @var Datafield $datafield */
        foreach ($datafields as $datafield) :
            if (isset($datafield['slug']) && $datafield['slug']) :
                $slugDatafields[$datafield['id']] = new stdClass();
                $slugDatafields[$datafield['id']]->id = $datafield['id'];
                $slugDatafields[$datafield['id']]->published = true;
            endif;
            if (isset($datafield['slugCategory']) && $datafield['slugCategory'] && $parentId !== null) :
                $slugCategoryDatafields[$parentId] = new stdClass();
                $slugCategoryDatafields[$parentId]->id = $datafield['id'];
                $slugCategoryDatafields[$parentId]->published = true;
            endif;
            if (isset($datafield['seoTitle']) && $datafield['seoTitle']) :
                $seoTitleDatafields[$datafield['id']] = new stdClass();
                $seoTitleDatafields[$datafield['id']]->id = $datafield['id'];
                $seoTitleDatafields[$datafield['id']]->published = true;
            endif;
        endforeach;

        $datagroup->setSlugDatafields($slugDatafields);
        $datagroup->setSlugCategories($slugCategoryDatafields);
        $datagroup->setSeoTitleDatafields($seoTitleDatafields);
        $datagroup->save();

        return $datagroup;
    }

    protected function createItems(
        array $pages,
        string $titleField,
        Datagroup $datagroup,
        string $parentId = null,
        int $startOrder = 0
    ): array {
        $return = [
            'pages' => [],
            'ids'   => [],
        ];
        foreach ($pages as $title => $params) :
            $item = $this->repositories->item->findFirst(
                new FindValueIterator([
                    new FindValue($titleField, $title),
                    new FindValue('datagroup', (string)$datagroup->getId()),
                ]),
                false
            );
            if ($item === null) :
                $fieldValues = [];
                if (is_array($params)) :
                    $fieldValues = $params;
                endif;
                $item = ItemFactory::create(
                    $title,
                    (string)$datagroup->getId(),
                    $fieldValues,
                    true,
                    $parentId,
                    $startOrder
                );
                $this->eventsManager->fire(Item::class.':beforeModelSave', $item, $this);
                $item->save();
            endif;
            $return['pages'][$title] = (string)$item->getId();
            $return['ids'][] = (string)$item->getId();
            $startOrder++;
        endforeach;

        return $return;
    }

    protected function createDatafields(
        array $fields,
        string $titleField,
        int $order = 10
    ): array {
        $return = [];
        foreach ($fields as $title => $params) :
            Datafield::setFindValue($titleField, $params['calling_name']);
            Datafield::setFindPublished(false);
            $datafields = Datafield::findAll();
            if (count($datafields) === 0) :
                $datafieldSettings = [];
                if (isset($params['datafieldSettings'])) :
                    $datafieldSettings = $params['datafieldSettings'];
                endif;

                $datafield = DatafieldFactory::create(
                    $title,
                    $params['calling_name'],
                    $params['type'],
                    $datafieldSettings,
                    true,
                    $order
                );
                $datafield->save();
            else :
                $datafield = $datafields[0];
            endif;

            $return[] = [
                'id'           => (string)$datafield->getId(),
                'published'    => true,
                'required'     => isset($params['required']) ? true : false,
                'slug'         => isset($params['slug']) ? true : false,
                'slugCategory' => isset($params['slugCategory']) ? true : false,
                'seoTitle'     => isset($params['seoTitle']) ? true : false,
            ];
            $order++;
        endforeach;

        return $return;
    }

    protected function createBlocks(
        array $blocks,
        string $titleField,
        int $order = 10
    ): array {
        $return = [
            'blocks' => [],
            'ids'    => [],
        ];
        foreach ($blocks as $title => $params) :
            Block::setFindValue($titleField, $title);
            Block::setFindPublished(false);
            $blocks = Block::findAll();
            if (count($blocks) === 0) :
                $blockSettings = [];
                if (isset($params['blockSettings'])) :
                    $blockSettings = $params['blockSettings'];
                endif;

                $block = BlockFactory::create(
                    $title,
                    $params['block'],
                    $params['template'],
                    $blockSettings,
                    true,
                    $order
                );
                $block->save();

                if (isset($params['position']) && isset($params['datagroup'])) :
                    BlockPositionFactory::create(
                        ucfirst($params['position']).' - '.$block->_('name'),
                        (string)$block->getId(),
                        $params['position'],
                        $params['datagroup'],
                        true,
                        $order
                    )->save();
                endif;
            else :
                $block = $blocks[0];
            endif;
            $return['blocks'][$title] = (string)$block->getId();
            $return['ids'][] = (string)$block->getId();
            $order++;
        endforeach;

        return $return;
    }

    protected function createContentDatagroup(?array $extraFields = null): Datagroup
    {
        $fields = [
            'Item naam' => [
                'calling_name'      => 'name',
                'type'              => 'FieldText',
                'datafieldSettings' => [
                    'inputType' => 'text',
                    'multilang' => true,
                ],
                'required'          => true,
                'slug'              => true,
            ],
            'Introtext' => [
                'calling_name'      => 'introtext',
                'type'              => 'FieldTexteditor',
                'datafieldSettings' => [
                    'multilang' => true,
                ],
            ],
            'Bodytext'  => [
                'calling_name'      => 'bodytext',
                'type'              => 'FieldTexteditor',
                'datafieldSettings' => [
                    'multilang' => true,
                ],
            ],
        ];
        if (is_array($extraFields)) :
            $fields = array_merge($fields, $extraFields);
        endif;

        $fieldIds = $this->createDatafields($fields, 'calling_name');

        return $this->createDatagroup(
            'Pagina',
            'name.'.$this->configuration->getLanguageShort(),
            'template/core/views/blocks/MainContent/core',
            'content',
            $fieldIds,
            true
        );
    }

    protected function createPermissionRoles(array $roles): void
    {
        foreach ($roles as $name => $params) :
            PermissionRole::setFindValue('calling_name', $params['calling_name']);
            PermissionRole::setFindPublished(false);
            $permissionRoles = PermissionRole::findAll();
            if (count($permissionRoles) === 0) :
                PermissionRoleFactory::create(
                    $name,
                    $params['calling_name'],
                    true,
                    isset($params['adminAccess']) ? true : false,
                    isset($params['parentId']) ? $params['parentId'] : null
                )->save();
            endif;
        endforeach;
    }

    protected function createSystemEmails(
        array $emails,
        string $subjectField
    ): array {
        $return = [
            'emails' => [],
            'ids'    => [],
        ];
        foreach ($emails as $subject => $params) :
            Email::setFindValue($subjectField, $subject);
            Email::setFindPublished(false);
            $emails = Email::findAll();
            if (count($emails) === 0) :
                $email = EmailFactory::create(
                    $subject,
                    $params['body'],
                    $params['systemAction'],
                    $params['triggerState'],
                    isset($params['published']) ? $params['published'] : false,
                    isset($params['alternativeRecipient']) ? $params['alternativeRecipient'] : null,
                    isset($params['messageSuccess']) ? $params['messageSuccess'] : null,
                    isset($params['messageError']) ? $params['messageError'] : null
                );
                $email->save();
            else :
                $email = $emails[0];
            endif;
            $return['emails'][$subject] = (string)$email->getId();
            $return['ids'][] = (string)$email->getId();
        endforeach;

        $this->createSettings([
            'WEBSITE_CONTACT_EMAIL' => ['value' => ''],
            'WEBSITE_DEFAULT_NAME'  => ['value' => ''],
        ]);

        return $return;
    }

    public function createBasicPermissionRoles(): void
    {
        $roles = [
            'SuperAdmin'    => [
                'calling_name' => 'superadmin',
                'adminAccess'  => true,
            ],
            'Admin'         => [
                'calling_name' => 'admin',
                'adminAccess'  => true,
            ],
            'Geregistreerd' => [
                'calling_name' => 'registered',
            ],
            'Gast'          => [
                'calling_name' => 'guest',
            ],
        ];

        $this->createPermissionRoles($roles);
    }
}

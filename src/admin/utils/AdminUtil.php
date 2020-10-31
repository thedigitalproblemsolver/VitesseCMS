<?php declare(strict_types=1);

namespace VitesseCms\Admin\Utils;

use VitesseCms\Admin\Models\AdminMenu;
use VitesseCms\Admin\Models\AdminMenuGroup;
use VitesseCms\Admin\Models\AdminMenuGroupIterator;
use VitesseCms\Core\Services\ViewService;
use VitesseCms\Core\Enum\SystemEnum;
use VitesseCms\Core\Forms\AdminToolbarForm;
use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Setting\Services\SettingService;
use VitesseCms\User\Models\User;
use VitesseCms\User\Utils\PermissionUtils;
use Phalcon\Events\Manager;
use \count;

class AdminUtil
{
    /**
     * @var SettingService
     */
    protected $setting;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Manager
     */
    protected $eventsManager;

    /**
     * @var ViewService
     */
    protected $view;

    public function __construct(
        SettingService $setting,
        User $user,
        Manager $eventsManager,
        ViewService $view
    ) {
        $this->setting = $setting;
        $this->user = $user;
        $this->eventsManager = $eventsManager;
        $this->view = $view;
    }

    public function toolbar(): string
    {
        $adminGroupIterator = new AdminMenuGroupIterator();
        foreach (SystemEnum::COMPONENTS as $key => $label) :
            $adminGroupIterator->add(new AdminMenuGroup($label, $key, self::getComponentDatagroup($key)));
        endforeach;

        $adminMenu = new AdminMenu([], $adminGroupIterator, $this->setting, $this->user);
        $this->eventsManager->fire('adminMenu:AddChildren', $adminMenu);
        $adminForm = new AdminToolbarForm();
        $adminForm->setFormClass('form-inline my-2 my-lg-0');

        $navbar = [
            'navClass' => 'admin-toolbar fixed-top navbar-dark',
            'items'    => $this->toolbarAclCheck($adminMenu->getNavbarItems()),
            'form'     => $adminForm->renderForm(
                'admin/core/adminindex/toggleParameters',
                'adminToolbarForm'
            ),
        ];

        return $this->view->renderTemplate(
            'navbar',
            'partials',
            ['navbar' => $navbar]
        );
    }

    protected function toolbarAclCheck(array $navbarItems): array
    {
        if ('superadmin' !== $this->user->getPermissionRole()) :
            foreach ($navbarItems as $parentIndex => $parent) :
                foreach ($parent['children'] as $childIndex => $child) :
                    if ($child['slug'] !== '#') :
                        $path = explode('/', $child['slug']);
                        if (!PermissionUtils::check(
                            $this->user,
                            $path[1],
                            $path[2],
                            $path[3]
                        )) :
                            unset($parent['children'][$childIndex]);
                        endif;
                    endif;
                endforeach;
                if (count($parent['children']) === 0) :
                    unset($navbarItems[$parentIndex]);
                else :
                    //reset array-keys for mustache use
                    $newChildren = [];
                    foreach ($parent['children'] as $child):
                        $newChildren[] = $child;
                    endforeach;
                    $navbarItems[$parentIndex]['children'] = $newChildren;
                endif;
            endforeach;
        endif;

        return $navbarItems;
    }

    public static function getComponentDatagroup(string $component): array
    {
        Datagroup::setFindValue('parentId', null);
        Datagroup::setFindValue('component', $component);
        Datagroup::addFindOrder('name');

        return Datagroup::findAll();
    }

    public static function isAdminPage(): bool
    {
        return !(substr_count($_SERVER['REQUEST_URI'], 'admin/') === 0);
    }
}

<?php declare(strict_types=1);

namespace VitesseCms\Admin\Models;

use VitesseCms\Setting\Services\SettingService;
use VitesseCms\User\Models\User;

class AdminMenu
{
    /**
     * @var array
     */
    protected $navbarItems;

    /**
     * @var SettingService
     */
    protected $setting;

    /**
     * @var AdminMenuGroupIterator
     */
    protected $groups;

    /**
     * @var User
     */
    protected $user;

    public function __construct(
        array $navbarItems,
        AdminMenuGroupIterator $groups,
        SettingService $setting,
        User $user
    ) {
        $this->navbarItems = $navbarItems;
        $this->setting = $setting;
        $this->groups = $groups;
        $this->user = $user;
    }

    public function getNavbarItems(): array
    {
        return $this->navbarItems;
    }

    public function getSetting(): SettingService
    {
        return $this->setting;
    }

    public function addDropbown(string $name, AdminMenuNavBarChildren $children): AdminMenu
    {
        $this->navbarItems[] = (new AdminMenuNavBarItem(
            $name,
            '#',
            'dropdown-toggle',
            'data-toggle="dropdown"',
            $children->getItems()
        ))->toArray();

        return $this;
    }

    public function getGroups(): AdminMenuGroupIterator
    {
        return $this->groups;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}

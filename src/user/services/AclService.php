<?php declare(strict_types=1);

namespace VitesseCms\User\Services;

use VitesseCms\Core\Services\RouterService;
use VitesseCms\User\Models\User;
use VitesseCms\User\Utils\PermissionUtils;

class AclService
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var RouterService
     */
    protected $router;

    public function __construct(User $user, RouterService $routerService)
    {
        $this->user = $user;
        $this->router = $routerService;
    }

    public function hasAccess(string $function): bool
    {
        return PermissionUtils::check(
            $this->user,
            $this->router->getModulePrefix().$this->router->getModuleName(),
            $this->router->getControllerName(),
            $function
        );
    }
}

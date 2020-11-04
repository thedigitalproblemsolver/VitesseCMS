<?php declare(strict_types=1);

namespace VitesseCms\User\Utils;

use VitesseCms\Core\Utils\DebugUtil;
use VitesseCms\User\Models\User;
use Phalcon\Di;

class PermissionUtils
{
    protected static $aclMap;

    /**
     * @return array
     */
    public static function getDefaults(): array
    {
        return [
            'block'         => [
                'index' => [
                    'render'     => [
                        'access' => '*',
                    ],
                    'renderhtml' => [
                        'access' => '*',
                    ],
                ],
            ],
            'content'       => [
                'index' => [
                    'index' => [
                        'access' => '*',
                    ],
                ],
            ],
            'core'          => [
                'index' => [
                    'index' => [
                        'access' => '*',
                    ],
                ],
            ],
            'export'        => [
                'index'   => [
                    'index' => [
                        'access' => '*',
                    ],
                ],
                'sitemap' => [
                    'index' => [
                        'access' => '*',
                    ],
                ],
            ],
            'form'          => [
                'index' => [
                    'submit' => [
                        'access' => '*',
                    ],
                ],
            ],
            'mustache'      => [
                'index' => [
                    'gettemplate' => [
                        'access' => '*',
                    ],
                ],
            ],
            'communication' => [
                'newsletterlist'  => [
                    'addmember'   => [
                        'access' => '*',
                    ],
                    'unsubscribe' => [
                        'access' => [
                            'registered',
                            'admin',
                        ],
                    ],
                    'subscribe'   => [
                        'access' => [
                            'registered',
                            'admin',
                        ],
                    ],
                ],
                'newsletterqueue' => [
                    'unsubscribe' => [
                        'access' => '*',
                    ],
                    'opened'      => [
                        'access' => '*',
                    ],
                ],
            ],
            'shop'          => [
                'cart'     => [
                    'index'                => [
                        'access' => '*',
                    ],
                    'addtocart'            => [
                        'access' => '*',
                    ],
                    'getcarttext'          => [
                        'access' => '*',
                    ],
                    'removeitem'           => [
                        'access' => '*',
                    ],
                    'changequantity'       => [
                        'access' => '*',
                    ],
                    'setpackingforproduct' => [
                        'access' => '*',
                    ],
                ],
                'checkout' => [
                    'register'         => [
                        'access' => [
                            'guest',
                        ],
                    ],
                    'setshiptoaddress' => [
                        'access' => [
                            'registered',
                            'admin',
                        ],
                    ],
                ],
                'discount' => [
                    'parsecode' => [
                        'access' => '*',
                    ],
                ],
                'order'    => [
                    'saveandpay'        => [
                        'access' => [
                            'registered',
                            'admin',
                        ],
                    ],
                    'vieworder'         => [
                        'access' => [
                            'registered',
                            'admin',
                        ],
                    ],
                    'storeordermessage' => [
                        'access' => '*',
                    ],
                ],
                'payment'  => [
                    'process'  => [
                        'access' => '*',
                    ],
                    'cancel'   => [
                        'access' => '*',
                    ],
                    'redirect' => [
                        'access' => '*',
                    ],
                ],
                'shopper'  => [
                    'edit'        => [
                        'access' => [
                            'registered',
                            'admin',
                        ],
                    ],
                    'save'        => [
                        'access' => [
                            'registered',
                            'admin',
                        ],
                    ],
                    'geteditform' => [
                        'access' => [
                            'registered',
                            'admin',
                        ],
                    ],
                    'editshipto'  => [
                        'access' => [
                            'registered',
                            'admin',
                        ],
                    ],
                    'saveshipto'  => [
                        'access' => [
                            'registered',
                            'admin',
                        ],
                    ],
                ],
            ],
            'user'          => [
                'index'    => [
                    'index'     => [
                        'access' => [
                            'registered',
                            'admin',
                        ],
                    ],
                    'loginform' => [
                        'access' => [
                            '*',
                        ],
                    ],
                    'login'     => [
                        'access' => [
                            '*',
                        ],
                    ],
                    'logout'    => [
                        'access' => '*',
                    ],
                ],
                'password' => [
                    'changeform'      => [
                        'access' => [
                            'registered',
                            'admin',
                        ],
                    ],
                    'forgotform'      => [
                        'access' => [
                            'guest',
                        ],
                    ],
                    'resetform'       => [
                        'access' => [
                            'guest',
                        ],
                    ],
                    'parseforgotform' => [
                        'access' => [
                            'guest',
                        ],
                    ],
                    'parseresetform'  => [
                        'access' => [
                            'guest',
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function check(
        User $user,
        string $module,
        string $controller,
        string $action
    ): bool {
        $module = strtolower($module);
        $controller = strtolower($controller);
        $action = strtolower($action);

        if (
            'superadmin' === $user->getPermissionRole()
            || (
                DebugUtil::isDocker($_SERVER['SERVER_ADDR'])
                && $module === 'install'
                && $controller === 'index'
                && $action === 'index'
            ) || (
                DebugUtil::isDocker($_SERVER['SERVER_ADDR'])
                && $module === 'install'
                && $controller === 'index'
                && $action === 'createproperty'
            )
        ) :
            return true;
        endif;

        if (!is_array(self::$aclMap)) :
            self::$aclMap = self::getAccessFile();
        endif;

        return isset(self::$aclMap[$module][$controller][$action]['access'])
            && in_array($user->getPermissionRole(), self::$aclMap[$module][$controller][$action]['access'], true);
    }

    public static function getAccessFileName(): string
    {
        if (DebugUtil::isDocker($_SERVER['SERVER_ADDR'])) :
            return Di::getDefault()->get('config')->get('accountDir').'.access_dev.dat';
        endif;

        return Di::getDefault()->get('config')->get('accountDir').'.access.dat';
    }

    public static function getAccessFile(): array
    {
        return unserialize(
            base64_decode(
                gzinflate(
                    file_get_contents(self::getAccessFileName()
                    )
                )
            ),
            []
        );
    }
}

<?php declare(strict_types=1);

namespace VitesseCms\Shop\Helpers;

use VitesseCms\Core\Utils\DirectoryUtil;
use VitesseCms\Core\Utils\FileUtil;
use Phalcon\Di;

class ShippingHelper
{
    public static function getClass(string $type): string
    {
        if (is_file(
            Di::getDefault()->get('config')->get('accountDir').'/src/shop/shippingTypes/'.$type.'.php'
        )
        ) :
            return 'VitesseCms\\'.ucfirst(Di::getDefault()->get('config')->get('account')).'\Shop\ShippingTypes\\'.$type;
        endif;

        return 'VitesseCms\Shop\ShippingTypes\\'.$type;
    }

    public static function getTypes(string $rootDir, string $account) : array
    {
        $types = [];
        $shippingFiles = DirectoryUtil::getFilelist($rootDir.'src/shop/shippingTypes/');
        $shippingFilesAccount = DirectoryUtil::getFilelist(
            $rootDir .
            'config/account/'.
            $account .
            '/src/shop/shippingTypes/'
        );
        $files = array_merge($shippingFilesAccount, $shippingFiles);
        foreach ($files as $path => $file) :
            $name = FileUtil::getName($file);
            $types[$name] = $name;
        endforeach;

        return $types;
    }
}

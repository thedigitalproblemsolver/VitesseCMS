<?php declare(strict_types=1);

namespace VitesseCms\Core\Utils;

class BootstrapUtil
{
    public static function buildRegisterDirs(
        array $moduleDirs,
        string $account
    ): array {
        foreach ($moduleDirs as $moduleDir) :
            $moduleDirParts = explode('/', $moduleDir);
            $moduleDirParts = array_reverse($moduleDirParts);
            $moduleNamespace = ucfirst($moduleDirParts[0]);
            if ($moduleDirParts[2] === $account) :
                $moduleNamespace = ucfirst($moduleDirParts[2]).'\\'.$moduleNamespace;
            endif;

            $registerDirs[$moduleDir] = null;
            $registerNamespaces['VitesseCms\\'.$moduleNamespace] = $moduleDir;
            $subDirs = DirectoryUtil::getChildren($moduleDir);
            foreach ($subDirs as $subDir) :
                $subDirParts = explode('/', $subDir);
                $subDirParts = array_reverse($subDirParts);
                $registerDirs[$subDir] = null;
                $registerNamespaces['VitesseCms\\'.$moduleNamespace.'\\'.ucfirst($subDirParts[0])] = $subDir;
            endforeach;
        endforeach;
    }
}

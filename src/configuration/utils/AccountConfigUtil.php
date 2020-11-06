<?php declare(strict_types=1);

namespace VitesseCms\Configuration\Utils;

use VitesseCms\Core\Utils\DebugUtil;

class AccountConfigUtil extends AbstractConfigUtil
{
    public function __construct($filePath, $mode = null)
    {
        $this->setBaseDirs();

        $file = 'config.ini';
        if (DebugUtil::isDocker($_SERVER['SERVER_ADDR'] ?? '')) :
            $file = 'config_dev.ini';
        endif;

        $accountConfigFile = $this->systemDir.'../config/account/'.$filePath.'/'.$file;

        parent::__construct($accountConfigFile, $mode);
    }

}

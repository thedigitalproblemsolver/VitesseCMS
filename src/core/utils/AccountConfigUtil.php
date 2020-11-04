<?php declare(strict_types=1);

namespace VitesseCms\Core\Utils;

use Phalcon\Config\Adapter\Ini;

class AccountConfigUtil extends Ini
{

    protected $systemDir;

    public function __construct($filePath, $mode = null)
    {
        $this->systemDir = __DIR__.'/../../';

        $file = 'config.ini';
        if (DebugUtil::isDocker($_SERVER['SERVER_ADDR'] ?? '')) :
            $file = 'config_dev.ini';
        endif;

        $accountConfigFile = $this->systemDir.'../config/account/'.$filePath.'/'.$file;

        parent::__construct($accountConfigFile, $mode);
    }

}

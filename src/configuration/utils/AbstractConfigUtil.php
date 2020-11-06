<?php declare(strict_types=1);

namespace VitesseCms\Configuration\Utils;

use Phalcon\Config\Adapter\Ini;

abstract class AbstractConfigUtil extends Ini
{
    /**
     * @var string
     */
    protected $systemDir;

    /**
     * @var string
     */
    protected $moduleDir;

    protected function setBaseDirs(): void
    {
        $this->systemDir = __DIR__ . '/../../../../../';
        $this->moduleDir = __DIR__ . '/../../';
    }

    public function getSystemDir(): string
    {
        return $this->systemDir;
    }

    public function getModuleDir(): string
    {
        return $this->moduleDir;
    }

}

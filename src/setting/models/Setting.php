<?php declare(strict_types=1);

namespace VitesseCms\Setting\Models;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\Helpers\DirectoryHelper;
use VitesseCms\Core\Services\CacheService;
use VitesseCms\Core\Utils\DirectoryUtil;
use VitesseCms\Core\Utils\FileUtil;
use Phalcon\Di;

class Setting extends AbstractCollection
{
    /**
     * @var array
     */
    public $value;

    /**
     * @var string
     */
    public $calling_name;

    public function initialize()
    {
        $this->value = [];
    }

    public function getTypes(): array
    {
        $types = [[
            'value' => '',
            'label' => 'Choose a type',
            'selected' => false
        ]];
        $files = DirectoryUtil::getFilelist($this->di->config->get('rootDir') . 'src/setting/models/');
        foreach ($files as $path => $file) :
            $name = ucfirst(str_replace('Setting','', FileUtil::getName($file)));
            if($file !== 'Setting.php') :
                $types[] = [
                    'value' => FileUtil::getName($file),
                    'label' => strtolower($name),
                    'selected' => false
                ];
            endif;
        endforeach;

        return $types;
    }

    public function getValues(): array
    {
        return $this->value;
    }

    public function getCallingName(): ?string
    {
        return $this->calling_name;
    }

    public function afterSave()
    {
        /** @var CacheService $cache */
        $cache = Di::getDefault()->get('cache');
        foreach ($this->getValues() as $languageShort => $value):
            $cache->delete(
                $cache->getCacheKey($this->getCallingName().$languageShort)
            );
        endforeach;
    }
}

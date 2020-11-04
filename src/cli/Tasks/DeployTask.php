<?php declare(strict_types=1);

namespace VitesseCms\Cli\Tasks;

use VitesseCms\Cli\Models\Mapping;
use VitesseCms\Cli\Models\MappingIterator;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Core\Utils\DirectoryUtil;
use VitesseCms\Core\Utils\FileUtil;
use Phalcon\Cli\Task;
use Phalcon\Config\Adapter\Json;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Formatter\Crunched;

class DeployTask extends Task
{
    /**
     * @var array
     */
    protected $accountMapping;

    /**
     * @var InjectableInterface
     */
    protected $di;

    protected $coreAssetsDir;

    public function assetsAction(): void
    {
        $this->coreAssetsDir = __DIR__.'/../../../public_html/assets/default/';
        
        $this->accountMapping = [];
        if (is_file($this->getDI()->getConfiguration()->getAccountDir().'Deploy/FileMapping.json')) :
            $this->accountMapping = (new Json(
                $this->getDI()->getConfiguration()->getAccountDir().'Deploy/FileMapping.json')
            )->toArray();
        endif;
        $this->parseMapping($this->getJSMapping());
        $this->parseMapping($this->getImageMapping());
        $this->parseMapping($this->getCssMapping());

        $this->buildCss();
        $this->buildAdminCss();
    }

    public function cssAction(): void
    {
        $this->buildCss();
    }

    protected function parseMapping(MappingIterator $mappingIterator): void
    {
        while ($mappingIterator->valid()) :
            $mappig = $mappingIterator->current();
            if (substr_count($mappig->getSource(), '/*') === 1) :
                $dir = str_replace('/*', '/', $mappig->getSource());
                foreach (DirectoryUtil::getFilelist($dir) as $file) :
                    $this->copy($dir.$file, $mappig->getTarget().$file);
                endforeach;
            else :
                $this->copy($mappig->getSource(), $mappig->getTarget());
            endif;
            $mappingIterator->next();
        endwhile;
    }

    protected function copy(string $source, string $target): void
    {

        if (FileUtil::copy($source, $target)) :
            echo 'copied '.$source.' to '.$target.PHP_EOL;
        else :
            echo 'failed copying of '.$source.' to '.$target.PHP_EOL;
        endif;
    }

    protected function getJSMapping(): MappingIterator
    {
        $jsMapping = new MappingIterator([
            new Mapping(
                __DIR__.'/../../filemanager/resources/js/*',
                __DIR__.'/../../../public_html/assets/default/js/'
            ),
            new Mapping(
                __DIR__.'/../../core/resources/js/*',
                __DIR__.'/../../../public_html/assets/default/js/'
            ),
            new Mapping(
                __DIR__.'/../../../vendor/seiyria/bootstrap-slider/dist/bootstrap-slider.min.js',
                __DIR__.'/../../../public_html/assets/default/js/bootstrap-slider.min.js'
            ),
            new Mapping(
                __DIR__.'/../../../vendor/itsjavi/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js',
                __DIR__.'/../../../public_html/assets/default/js/bootstrap-colorpicker.min.js'
            ),
        ]);

        if (!empty($this->accountMapping['javascript'])):
            foreach ($this->accountMapping['javascript'] as $image) :
                $jsMapping->add(new Mapping(
                    __DIR__.'/../../../'.$image['source'],
                    __DIR__.'/../../../'.$image['target']));
            endforeach;
        endif;

        return $jsMapping;
    }

    protected function getCssMapping(): MappingIterator
    {
        $cssMapping = new MappingIterator([
            new Mapping(
                __DIR__.'/../../../vendor/seiyria/bootstrap-slider/dist/css/bootstrap-slider.min.css',
                $this->coreAssetsDir.'css/bootstrap-slider.min.css'
            ),
        ]);

        if (!empty($this->accountMapping['css'])):
            foreach ($this->accountMapping['css'] as $image) :
                $cssMapping->add(new Mapping(
                    __DIR__.'/../../../'.$image['source'],
                    __DIR__.'/../../../'.$image['target']));
            endforeach;
        endif;

        return $cssMapping;
    }

    protected function getImageMapping(): MappingIterator
    {
        $imageMapping = new MappingIterator([
            new Mapping(
                __DIR__.'/../../../vendor/itsjavi/bootstrap-colorpicker/dist/img/bootstrap-colorpicker/*',
                __DIR__.'/../../../public_html/assets/default/images/'
            ),
            new Mapping(
                __DIR__.'/../../../vendor/components/flag-icon-css/flags/1x1/*',
                __DIR__.'/../../../public_html/assets/default/images/flags/1x1/'
            ),
            new Mapping(
                __DIR__.'/../../../vendor/components/flag-icon-css/flags/4x3/*',
                __DIR__.'/../../../public_html/assets/default/images/flags/4x3/'
            ),
        ]);

        if (!empty($this->accountMapping['images'])):
            foreach ($this->accountMapping['images'] as $image) :
                $imageMapping->add(new Mapping(
                    __DIR__.'/../../../'.$image['source'],
                    __DIR__.'/../../../'.$image['target']));
            endforeach;
        endif;

        return $imageMapping;
    }

    protected function buildCss(): void
    {
        $scssCompiler = new Compiler();
        $scssCompiler->addImportPath($this->getDI()->getConfiguration()->getAccountDir().'scss/');
        $scssCompiler->setFormatter(Crunched::class);
        $scssCompiled = $scssCompiler->compile(
            file_get_contents($this->getDI()->getConfiguration()->getAccountDir().'scss/site.scss')
        );

        file_put_contents($this->getDI()->getConfiguration()->getAssetsDir().'css/site.css', $scssCompiled);
    }

    protected function buildAdminCss(): void
    {
        $scssCompiler = new Compiler();
        $scssCompiler->addImportPath(__DIR__.'/../../core/scss/');
        $scssCompiler->setFormatter(Crunched::class);
        $scssCompiled = $scssCompiler->compile(
            file_get_contents(__DIR__.'/../../core/scss/admin.scss')
        );

        file_put_contents($this->coreAssetsDir.'css/admin.css', $scssCompiled);
    }
}

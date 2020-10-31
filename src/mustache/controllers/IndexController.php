<?php declare(strict_types=1);

namespace VitesseCms\Mustache\Controllers;

use VitesseCms\Core\AbstractController;
use VitesseCms\Sef\Helpers\SefHelper;

class IndexController extends AbstractController
{
    public function onConstruct()
    {
    }

    public function getTemplateAction(): void
    {
        $content = '';
        $file = $this->configuration->getCoreTemplateDir().'views/'.$this->request->get('f').'.mustache';
        if (is_file($file)) :
            $templateName = str_replace('/', '_', strtolower($this->request->get('f')));
            $cacheKey = $this->cache->getCacheKey(
                $templateName.
                $this->configuration->getLanguageShort().
                filesize($file)
            );
            $content = $this->cache->get($cacheKey);
            if (!$content) :
                $partials = '';
                $content = $this->parsePlaceholders(file_get_contents($file));

                preg_match_all('/{{>(\s*)([a-z_\-\/]*)(\s*)}}/', $content, $aMatches);
                if (isset($aMatches[2]) && is_array($aMatches[2])) :
                    foreach ($aMatches[2] as $key => $value) :
                        $file = $this->configuration->getCoreTemplateDir().'views/partials/'.$value.'.mustache';
                        if (is_file($file)) :
                            $partials .= '<script id="'.$value.'" type="text/html">'.$this->parsePlaceholders(file_get_contents($file)).'</script>';
                        endif;
                    endforeach;
                endif;

                $content = '<html><head></head><body><script id="'.$templateName.'" type="text/html">'.$content.'</script>'.$partials.'</body></html>';
                $this->cache->save($cacheKey, $content);
            endif;
        endif;

        echo $content;
        die();
    }

    protected function parsePlaceholders(string $content): string
    {
        $content = str_replace('[]', '.'.$this->configuration->getLanguageShort(), $content);
        $content = $this->language->parsePlaceholders($content);
        $content = SefHelper::parsePlaceholders(
            $content,
            (string)$this->view->getVar('currentId')
        );
        $content = $this->setting->parsePlaceholders($content);

        preg_match_all('/{{([A-Z_]*)}}/', $content, $aMatches);
        if (isset($aMatches[1]) && is_array($aMatches[1])) :
            foreach ($aMatches[1] as $key => $value) :
                $content = str_replace(
                    ['{{{'.$value.'}}}', '{{'.$value.'}}'],
                    $this->view->getVar($value),
                    $content
                );
            endforeach;
        endif;

        return $content;
    }
}

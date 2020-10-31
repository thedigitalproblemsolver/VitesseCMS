<?php declare(strict_types=1);

namespace VitesseCms\Media\Services;

use VitesseCms\Media\Enums\AssetsEnum;
use Phalcon\Assets\Manager;

class AssetsService extends Manager
{
    /**
     * @var array
     */
    protected $used;

    /**
     * @var array
     */
    protected $js;

    /**
     * @var array
     */
    protected $css;

    /**
     * @var string
     */
    protected $webDir;

    public function __construct(string $webDir)
    {
        parent::__construct();

        $this->webDir = $webDir;
        $this->used = [];
        $this->js = [];
        $this->css = [];
    }

    /**
     * @deprecated split up in smaller functions so we have more controle
     */
    public function load(string $assetGroup): void
    {
        if (!in_array($assetGroup, $this->used, true)) :
            switch ($assetGroup) :
                case 'autocomplete':
                    $this->js[] = '//cdnjs.cloudflare.com/ajax/libs/corejs-typeahead/1.2.1/bloodhound.min.js';
                    $this->js[] = '//cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js';
                    break;
                case AssetsEnum::BOOTSTRAP_JS:
                    $this->load(AssetsEnum::JQUERY);
                    $this->load(AssetsEnum::POPPLER_JS);
                    $this->js[] = '//cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js';
                    $this->js[] = '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.min.js';
                    break;
                case AssetsEnum::BOOTSTRAP_TOGGLE:
                    $this->load(AssetsEnum::BOOTSTRAP_JS);
                    $this->js[] = '//cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js';
                    $this->css[] = '//cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css';
                    break;
                case AssetsEnum::COLORPICKER:
                    $this->load('bootstrapJs');
                    $this->js[] = 'bootstrap-colorpicker.min.js';
                    break;
                case AssetsEnum::COOKIECONSENT:
                    $this->js[] = '//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.1/cookieconsent.min.js';
                    $this->css[] = '//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.1/cookieconsent.min.css';
                    break;
                case AssetsEnum::EDITOR:
                    $this->load(AssetsEnum::SUMMERNOTE);
                    break;
                case AssetsEnum::FACEBOOK:
                    $this->js[] = 'facebook.js';
                    break;
                case AssetsEnum::FILEMANAGER:
                    $this->load('jquery');
                    $this->load('font-awesome');
                    $this->load('site');
                    $this->css[] = 'filemanager.css';
                    $this->js[] = 'filemanager.js';
                    break;
                case AssetsEnum::FONT_AWESOME:
                    $this->css[] = '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css';
                    break;
                case AssetsEnum::GOOGLE:
                    $this->js[] = 'theGoogle.js';
                    break;
                case AssetsEnum::JQUERY:
                    $this->js[] = '//ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js';
                    break;
                case AssetsEnum::FILTER:
                    $this->load('jquery');
                    $this->load('slider');
                    $this->js[] = 'filter.js';
                    break;
                case AssetsEnum::LAZYLOAD:
                    $this->load('jquery');
                    $this->js[] = '//cdnjs.cloudflare.com/ajax/libs/jquery_lazyload/1.9.7/jquery.lazyload.min.js';
                    break;
                case AssetsEnum::MUSTACHE_JS:
                    $this->js[] = '//cdnjs.cloudflare.com/ajax/libs/mustache.js/3.0.1/mustache.min.js';
                    $this->load('jquery');
                    $this->js[] = 'jquery.mustache.js';
                    break;
                case AssetsEnum::POPPLER_JS:
                    $this->js[] = '//cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js';
                    break;
                case AssetsEnum::RECAPTCHA:
                    $this->js[] = '//www.google.com/recaptcha/api.js';
                    break;
                case AssetsEnum::SELECT2:
                    $this->load('jquery');
                    $this->js[] = '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js';
                    $this->js[] = 'select2Sortable.js';
                    $this->css[] = '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css';
                    break;
                case AssetsEnum::SITE:
                    $this->load('jquery');
                    $this->js[] = 'helper.js';
                    $this->js[] = 'ui.js';
                    $this->js[] = 'form.js';
                    $this->js[] = 'ajax.js';
                    $this->js[] = 'sys.js';
                    break;
                case AssetsEnum::SHOP:
                    $this->js[] = 'shop.js';
                    break;
                case AssetsEnum::SLIDER:
                    $this->load('jquery');
                    $this->load('site');
                    $this->js[] = 'bootstrap-slider.min.js';
                    $this->css[] = 'bootstrap-slider.min.css';
                    break;
                case 'sortable':
                    $this->load('jquery');
                    $this->js[] = '//cdnjs.cloudflare.com/ajax/libs/jquery-sortable/0.9.13/jquery-sortable-min.js';
                    break;
                case AssetsEnum::SUMMERNOTE:
                    $this->load('bootstrapJs');
                    $this->js[] = '//cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote-bs4.js';
                    $this->css[] = '//cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote-bs4.css';
                    break;
            endswitch;
            $this->used[] = $assetGroup;
        endif;
    }

    public function loadAdmin(): void
    {
        $this->load('sortable');
        $this->load('editor');
        $this->load('select2');
        $this->js['site-admin'] = '/assets/default/js/admin.js?v=' . filemtime($this->webDir . '/assets/default/js/admin.js');
        $this->css['site-admin'] = '/assets/default/css/admin.css?v=' . filemtime($this->webDir . '/assets/default/css/admin.css');
    }

    public function loadGoogleMaps(string $apiKey): void
    {
        $this->js['google-maps-1'] = '//maps.google.com/maps/api/js?key=' . $apiKey;
        $this->js['google-maps-2'] = '//cdnjs.cloudflare.com/ajax/libs/gmaps.js/0.4.25/gmaps.min.js';
    }

    public function insertJs(string $url): void
    {
        $this->js[] = $url;
    }

    public function getByType(string $type): array
    {
        switch ($type) :
            case 'js':
                return $this->js;
            case 'css':
                return $this->css;
        endswitch;

        return [];
    }
}

<?php declare(strict_types=1);

namespace VitesseCms\Mustache;

use Phalcon\DiInterface;
use Phalcon\Mvc\View\Engine\Mustache;
use Phalcon\Mvc\ViewBaseInterface;

class MustacheEngine extends Mustache
{
    /**
     * @var array
     */
    protected $parsedPaths;

    public function __construct(
        ViewBaseInterface $view,
        Engine $mustacheEngine,
        DiInterface $di = null
    ) {
        parent::__construct($view, $di);
        $this->mustache = $mustacheEngine;
        $this->parsedPaths = [];
    }

    public function render( $path, $params, $mustClean = false): void
    {
        if(!is_file($path)) {
            die($path);
        }

        if(empty($this->parsedPaths[$path])) :
            $this->parsedPaths[$path] = file_get_contents($path);
        endif;

        $this->_view->setContent($this->mustache->render($this->parsedPaths[$path], $params));
    }
}

<?php declare(strict_types=1);

namespace VitesseCms\Form\Utils;

use VitesseCms\Core\Interfaces\InjectableInterface;
use Phalcon\Forms\ElementInterface;

class ElementUiUtil implements InjectableInterface
{
    public static function setTemplate(ElementInterface $element, string $template): ElementInterface
    {
        if (empty($element->getAttribute('template'))) :
            $element->setAttribute('template', $template);
        endif;

        return $element;
    }
}

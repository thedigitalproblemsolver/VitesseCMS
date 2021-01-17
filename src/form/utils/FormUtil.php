<?php declare(strict_types=1);

namespace VitesseCms\Form\Utils;

use VitesseCms\Form\AbstractForm;
use Phalcon\Forms\ElementInterface;

class FormUtil
{
    public static function renderInputTemplate(
        ElementInterface $element,
        ?string $short,
        AbstractForm $form
    ): string {
        if ($element->getAttribute('template') === 'csrf') :
            return (string)$element;
        endif;

        $params = [
            'elementId'              => $element->getAttribute('id'),
            'elementLabel'           => $element->getLabel(),
            'elementName'            => $element->getName(),
            'elementDefault'         => $element->getDefault(),
            'elementValue'           => false,
            'formLabelAsPlaceholder' => $form->getLabelAsPlaceholder(),
            'short'                  => $short,
            'attributes'             => $element->getAttributes(),
        ];

        if ($short === null) :
            $params['elementValue'] = $element->getValue();
        endif;

        if (is_array($element->getAttribute('defaultValue'))) :
            if ($short !== null) :
                $params['elementValue'] = $element->getAttribute('defaultValue')[$short];
            else :
                $params['elementValue'] = $element->getAttribute('defaultValue')[$form->configuration->getLanguageShort()];
            endif;
        elseif (is_string($element->getAttribute('defaultValue'))):
            $params['elementValue'] = $element->getAttribute('defaultValue');
        endif;

        if (empty($element->getAttribute('id'))) :
            $params['elementId'] = uniqid('', false);
        endif;

        return $form->view->renderTemplate(
            $element->getAttribute('template'),
            $form->configuration->getCoreTemplateDir().'views/partials/form/'.$form->getFormTemplate(),
            $params
        );
    }

    public static function hasValidRecaptcha(array $post): bool
    {
        return !empty($post['g-recaptcha-response']) && !empty($post['recaptcha-token']);
    }
}

<?php
namespace VitesseCms\Form\Helpers;

use VitesseCms\Form\AbstractForm;
use Phalcon\Forms\Form;

/**
 * Class FormHelper
 */
class FormHelper
{
    /**
     * @param Form $form
     *
     * @return string
     */
    public static function buildTableFromElements(Form $form): string
    {
        $html = '<table>';
        foreach ($form->getElements() as $element) :
            $html .= '<tr>
                <td>'.$element->getName().'</td>
                <td width="15px"></td>
                <td>'.$element->getValue().'</td>
            ';
        endforeach;
        $html .= '</table>';

        return $html;
    }
}

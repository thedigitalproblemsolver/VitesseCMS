<?php declare(strict_types=1);

namespace VitesseCms\Field\Forms;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\Factories\ObjectFactory;
use VitesseCms\Field\Interfaces\AdminformInterface;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Models\Attributes;

class AdminFieldText implements AdminformInterface
{
    public static function buildForm(AbstractForm $form, AbstractCollection $item): void
    {
        $selected = ObjectFactory::create();
        $selected->set($item->_('inputType'), true);
        $form->addDropdown(
            'Input Type',
            'inputType',
            (new Attributes())
                ->setRequired()
                ->setOptions([
                    [
                        'value' =>'text',
                        'label' => 'Text',
                        'selected' => $selected->_('text')
                    ],
                    //'color' => 'Color',
                    //'date' => 'Date',
                    //'datetime' => 'Date-time',
                    [
                        'value' => 'email',
                        'label' => 'E-mail',
                        'selected' => $selected->_('email')
                    ],
                    [
                        'value' => 'hidden',
                        'label' => 'Hidden',
                        'selected' => $selected->_('hidden')
                    ],
                    //'month' => 'Month',
                    [
                        'value' => 'number',
                        'label' => 'Number',
                        'selected' => $selected->_('number')
                    ],
                    //'range' => 'Range',
                    //'search' => 'Search',
                    [
                        'value' => 'tel',
                        'label' => 'Phone',
                        'selected' => $selected->_('tel')
                    ],
                    //'time' => 'Time',
                    [
                        'value' => 'url',
                        'label' => 'Url',
                        'selected' => $selected->_('url')
                    ],
                    [
                        'value' => 'password',
                        'label' => 'Password',
                        'selected' => $selected->_('password')
                    ]
                    //'week' => 'Week',
                ]
            ))
        ;

        if($selected->_('hidden')) :
            self::addHidden($form, $item);
        endif;
    }

    protected static function addHidden(AbstractForm $form, AbstractCollection $item) : void
    {
        $form->_(
            'text',
            'Default value',
            'defaultValue',
            [
                'multilang' => $item->_('multilang')
            ]
        );
    }
}

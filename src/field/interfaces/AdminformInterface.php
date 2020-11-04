<?php

namespace VitesseCms\Field\Interfaces;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Form\AbstractForm;

/**
 * Class AdminformInterface
 */
interface AdminformInterface
{
    /**
     * @param AbstractForm $form
     * @param AbstractCollection $item
     */
    public static function buildForm(AbstractForm $form, AbstractCollection $item): void ;
}

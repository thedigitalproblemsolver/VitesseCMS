<?php declare(strict_types=1);

namespace VitesseCms\Install\Forms;

use VitesseCms\Form\AbstractForm;
use Phalcon\Tag;

class LogoForm extends AbstractForm
{
    public function build(): LogoForm
    {
        $this->addHtml(Tag::tagHtml('h1').'Add a logo'.Tag::tagHtmlClose('h1'))
            ->addFilemanager('Choose a image', 'image')
            ->addSubmitButton('create');

        return $this;
    }
}

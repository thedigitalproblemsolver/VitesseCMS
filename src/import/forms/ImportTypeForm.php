<?php declare(strict_types=1);

namespace VitesseCms\Import\Forms;

use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Import\Models\ImportType;
use VitesseCms\Import\Utils\ImportUtil;
use Phalcon\Tag;

class ImportTypeForm extends AbstractForm
{
    public function initialize(ImportType $item): void
    {
        $files = ImportUtil::getImporters(
            $this->configuration->getSystemDir(),
            $this->configuration->getAccountDir()
        );

        $this->addText('%CORE_NAME%', 'name', (new Attributes())->setRequired(true))
            ->addDropdown(
                '%ADMIN_DATAGROUP%',
                'datagroup',
                (new Attributes())->setOptions(ElementHelper::arrayToSelectOptions(Datagroup::findAll()))
            )->addDropdown(
                '%IMPORT_TYPE%',
                'type',
                (new Attributes())->setRequired(true)->setOptions(ElementHelper::arrayToSelectOptions($files))
            )
            ->addText('Image folder','imageFolder', (new Attributes())->setRequired(true))
        ;

        if($item->hasType()) :
            $item->getTypeClass()::buildAdminForm($this, $item);
        endif;

        if($item->getId()) :
            $this->addHtml(Tag::linkTo(
                [
                    'action' => $this->url->getBaseUri().'import/index/index/'.$item->getId(),
                    'target' => '_blank'
                ],
                'Trigger import'
            ));
        endif;

        $this->addSubmitButton('%CORE_SAVE%');
    }
}

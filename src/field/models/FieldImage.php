<?php declare(strict_types=1);

namespace VitesseCms\Field\Models;

use VitesseCms\Content\Models\Item;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Admin\Utils\AdminUtil;
use VitesseCms\Core\Utils\FileUtil;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Core\Models\Datafield;
use VitesseCms\Field\AbstractField;

class FieldImage extends AbstractField
{
    public function buildItemFormElement(AbstractForm $form, Datafield $datafield, AbstractCollection $data = null)
    {
        if (AdminUtil::isAdminPage()) :
            $this->setOption('template', 'filemanager');
        endif;

        if ($datafield->_('allowedFiletypeGroups')) :
            $filetypeGroups = [];
            foreach ((array)$datafield->_('allowedFiletypeGroups') as $filetypeGroup) :
                $filetypeGroups = array_merge($filetypeGroups, FileUtil::getFiletypesByGroup($filetypeGroup));
            endforeach;
            $this->setOption('allowedTypes', $filetypeGroups);
        endif;

        $form->_(
            'file',
            $datafield->getNameField(),
            $datafield->getCallingName(),
            $this->getOptions()
        );
    }
}

<?php declare(strict_types=1);

namespace VitesseCms\Sef\Models;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Admin\Utils\AdminUtil;
use VitesseCms\Sef\Utils\SefUtil;

class Redirect extends AbstractCollection
{
    public function afterFetch()
    {
        $this->set('name', $this->_('from') . ' > ' . $this->_('to'));
        parent::afterFetch();
        if (AdminUtil::isAdminPage()) :
            $iconClass = 'flag-icon fa fa-globe';
            if ($this->_('languageShort')) :
                $iconClass = 'flag-icon flag-icon-' . $this->_('languageShort');
            endif;
            $this->set(
                'adminListName',
                '<i class="'.$iconClass . ' mr-2"></i>' .
                $this->_('from') . ' > ' . $this->_('to')
            );
        endif;
    }

    public function validation(): bool
    {
        $this->set('from', SefUtil::fixSlashes($this->_('from')));
        $this->set('to', SefUtil::fixSlashes($this->_('to')));

        if ($this->_('from') === $this->_('to')) :
            return false;
        endif;

        if ($this->getId() === null) :
            Redirect::setFindPublished(false);
            Redirect::setFindValue('from', $this->_('from'));
            Redirect::setFindValue('to', $this->_('to'));
            if (Redirect::count() > 0) :
                return false;
            endif;
        endif;

        return true;
    }
}

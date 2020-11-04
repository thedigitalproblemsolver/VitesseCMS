<?php declare(strict_types=1);

namespace VitesseCms\Core\Forms;

use VitesseCms\Form\AbstractForm;
use VitesseCms\User\Utils\PermissionUtils;

class AdminToolbarForm extends AbstractForm
{
    public function initialize(): void
    {
        if (PermissionUtils::check($this->user, 'block', 'adminblockposition', 'edit')) :
            $this->_(
                'checkbox',
                'Layout',
                'layoutMode',
                [
                    'checked' => $this->session->get('layoutMode', false),
                ]
            );
        endif;
        if (PermissionUtils::check($this->user, 'block', 'adminblock', 'edit')) :
            $this->_(
                'checkbox',
                'Editor',
                'editorMode',
                [
                    'checked' => $this->session->get('editorMode', false),
                ]
            );
        endif;
        $checked = $this->session->get('cache');
        if (!is_bool($checked)):
            $checked = true;
        endif;
        $this->_(
            'checkbox',
            'Cache',
            'cache',
            [
                'checked' => $checked,
            ]
        );
    }
}

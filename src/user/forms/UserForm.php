<?php declare(strict_types=1);

namespace VitesseCms\User\Forms;

use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Form\AbstractForm;
use VitesseCms\User\Models\PermissionRole;

class UserForm extends AbstractForm
{

    /**
     * initialize
     */
    public function initialize()
    {
        $this->_(
            'email',
            '%CORE_EMAIL%',
            'email',
            ['required' => 'required']
        );
        $this->_(
            'select',
            '%ADMIN_ROLE%',
            'role',
            [
                'required' => 'required',
                'options'  => PermissionRole::class,
            ]
        );
        $this->_(
            'checkbox',
            '%USER_PASSWORD_FORCED_RESET%',
            'forcePasswordReset'
        );

        if ($this->getDI()->get('user')->getPermissionRole() === 'superadmin') :
            $this->_(
                'password',
                '%USER_PASSWORD%',
                'new_password'
            );
        endif;

        if ($this->setting->has('USER_DATAGROUP_PERSONALINFORMATION')) :
            /** @var Datagroup $datagroup */
            $datagroup = Datagroup::findById($this->setting->get('USER_DATAGROUP_PERSONALINFORMATION'));
            $this->_(
                'html',
                'html',
                'datagrouptitle',
                [
                    'html' => '<br /><h2>'.$datagroup->_('name').'</h2>',
                ]
            );
            $datagroup->buildItemForm($this);
        endif;

        $this->_(
            'submit',
            '%CORE_SAVE%'
        );
    }
}

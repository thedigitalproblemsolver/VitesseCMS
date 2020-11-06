<?php declare(strict_types=1);

namespace VitesseCms\User\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Core\Utils\DirectoryUtil;
use VitesseCms\Core\Utils\FileUtil;
use VitesseCms\Core\Utils\SystemUtil;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Form\AbstractForm;
use VitesseCms\User\Models\PermissionRole;
use VitesseCms\User\Utils\PermissionUtils;
use Phalcon\Di;
use Phalcon\Tag;

class AdminpermissionsController extends AbstractAdminController
{
    //TODO html naar mustache verplaatsen
    public function adminListAction(): void
    {
        PermissionRole::setFindPublished(false);
        PermissionRole::setFindValue('parentId', null);
        PermissionRole::setFindValue('calling_name', ['$ne' => 'superadmin']);
        $roles = PermissionRole::findAll();
        $modules = SystemUtil::getModules(Di::getDefault()->get('configuration'));
        $checks = [];
        if (is_file(PermissionUtils::getAccessFileName())) :
            $checks = PermissionUtils::getAccessFile();
        endif;

        $defaults = PermissionUtils::getDefaults();

        $table = '<form 
            action="admin/user/adminpermissions/save" 
            autocomplete="off"
            method="post"
        >
        <table class="table table-hover table-bordered" >
        <tr>
            <th>function</th>';

        $rolesList = [];
        foreach ($roles as $key => $role) :
            $table .= '<th>'.$role->_('name').'</th>';
            $rolesList[] = $role;
            if ($role->_('hasChildren')) :
                unset($roles[$key]);
                PermissionRole::setFindPublished(false);
                PermissionRole::setFindValue('parentId', (string)$role->_('_id'));
                PermissionRole::setFindValue('calling_name', ['$ne' => 'superadmin']);
                $children = PermissionRole::findAll();
                if ($children) :
                    foreach ($children as $child) :
                        $rolesList[] = $child;
                        $table .= '<th>'.$child->_('name').' <small>child of '.$role->_('name').'</small></th>';
                    endforeach;
                endif;
            endif;
        endforeach;
        $table .= '</tr>';
        $roles = $rolesList;

        foreach ($modules as $moduleName => $modulePath) :
            $controllers = DirectoryUtil::getFilelist($modulePath.'/controllers');
            $table .= '<tr>
                <th colspan="'.(\count($roles) + 1).'">'.$moduleName.'</th>
            </tr>';
            foreach ($controllers as $controllerPath => $controllerName) :
                $adminOnlyAccess = false;
                if (substr_count($controllerName, 'Admin') > 0) :
                    $adminOnlyAccess = true;
                endif;

                $table .= '<tr>
                    <td colspan="'.(\count($roles) + 1).'">'.FileUtil::getName($controllerName).'</td>
                </tr>';
                $functions = FileUtil::getFunctions($controllerPath);
                foreach ($functions as $function) :
                    $controllerName = str_replace('controller', '', strtolower(FileUtil::getName($controllerName)));
                    $orgName = $function;
                    $function = strtolower(str_replace('Action', '', $function));

                    $fieldName = 'check['.$moduleName.']['.$controllerName.']['.$function.'][access][]';
                    $table .= '<tr>
                        <td>-&nbsp;'.$orgName.'</td>';
                    foreach ($roles as $role) :
                        if (
                            $adminOnlyAccess === false
                            || (
                                $adminOnlyAccess === true
                                && $role->_('adminAccess')
                            )
                        ) :
                            $parameters = [
                                $fieldName,
                                'value' => $role->_('calling_name'),
                                'class' => 'form-control',
                            ];
                            //check non-core acl
                            if (
                                isset($checks[$moduleName][$controllerName][$function]['access'])
                                && \in_array(
                                    $role->_('calling_name'),
                                    $checks[$moduleName][$controllerName][$function]['access'],
                                    true
                                )
                            ) :
                                $parameters['checked'] = 'checked';
                            endif;

                            //check core acl
                            if (
                                isset($defaults[$moduleName][$controllerName][$function]['access'])
                                && (
                                    $defaults[$moduleName][$controllerName][$function]['access'] === '*'
                                    || \in_array(
                                        $role->_('calling_name'),
                                        $defaults[$moduleName][$controllerName][$function]['access'],
                                        true
                                    )
                                )
                            ) :
                                $parameters['checked'] = 'checked';
                                $parameters['readonly'] = 'readonly';
                            endif;

                            //check parent role
                            if ($role->_('parentId')) :
                                $parentRole = PermissionRole::findById($role->_('parentId'));
                                if (
                                    isset($checks[$moduleName][$controllerName][$function]['access'])
                                    && \in_array(
                                        $parentRole->_('calling_name'),
                                        $checks[$moduleName][$controllerName][$function]['access'],
                                        true
                                    )
                                ) :
                                    $parameters['checked'] = 'checked';
                                endif;

                                if (
                                    isset($defaults[$moduleName][$controllerName][$function]['access'])
                                    && (
                                        $defaults[$moduleName][$controllerName][$function]['access'] === '*'
                                        || \in_array(
                                            $parentRole->_('calling_name'),
                                            $defaults[$moduleName][$controllerName][$function]['access'],
                                            true
                                        )
                                    )
                                ) :
                                    $parameters['checked'] = 'checked';
                                    $parameters['readonly'] = 'readonly';
                                endif;
                            endif;

                            $element = Tag::checkField($parameters);
                            $table .= '<td>'.$element.'</td>';
                        else :
                            $table .= '<td></td>';
                        endif;
                    endforeach;
                    $table .= '</tr>';
                endforeach;
            endforeach;
        endforeach;

        $table .= '<tr>
            <td colspan="'.(\count($roles) + 1).'" >
                <button 
                    type="submit" 
                    class="btn btn-success btn-block"
                >%CORE_SAVE%</button>
            </td>
        </tr></table>
        </form>';

        $this->view->setVar('content', $table);
        $this->prepareView();
    }

    public function saveAction(?string $itemId = null, AbstractCollection $item = null, AbstractForm $form = null): void
    {
        $hash = gzdeflate(
            base64_encode(
                serialize($this->request->getPost('check'))
            )
        );
        file_put_contents(PermissionUtils::getAccessFileName(), $hash);

        $this->flash->_('ADMIN_PERMISSIONS_SAVED_SUCCESS');

        $this->redirect();
    }
}

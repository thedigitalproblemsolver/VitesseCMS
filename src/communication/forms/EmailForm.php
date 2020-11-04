<?php declare(strict_types=1);

namespace VitesseCms\Communication\Forms;

use VitesseCms\Core\Utils\DirectoryUtil;
use VitesseCms\Core\Utils\FileUtil;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Media\Enums\AssetsEnum;

class EmailForm extends AbstractForm
{
    public function initialize():void
    {
        $options = [];
        $modules = DirectoryUtil::getChildren($this->configuration->getRootDir() . 'src');
        foreach ($modules as $moduleName => $modulePath) :
            $controllers = DirectoryUtil::getFilelist($modulePath . '/controllers');
            foreach ($controllers as $controllerPath => $controllerName) :
                $controllerName = str_replace(
                    ['.php', 'Controller'],
                    '',
                    $controllerName
                );
                $functions = FileUtil::getFunctions($controllerPath);
                foreach ($functions as $function) :
                    $function = str_replace('Action','',$function);
                    $options[strtolower($moduleName.$controllerName.$function)] = $moduleName.' - '.$controllerName.' - '.$function;
                endforeach;
            endforeach;
        endforeach;

        $this->addText(
            '%ADMIN_SUBJECT%',
            'subject',
            (new Attributes())->setRequired(true)->setMultilang(true)
        )->addEditor(
            '%ADMIN_EMAIL_TEXT%',
            'body',
            (new Attributes())->setRequired(true)->setMultilang(true)
        )->addEmail(
            'Alternative recipient',
            'recipient',
            (new Attributes())->setMultilang(true)
        )->addText(
            '%ADMIN_EMAIL_SYSTEM_SUCCESS_MESSAGE%',
            'messageSuccess',
            (new Attributes())->setMultilang(true)
        )->addText(
            '%ADMIN_EMAIL_SYSTEM_ERROR_MESSAGE%',
            'messageError',
            (new Attributes())->setMultilang(true)
        )->addDropdown(
            '%ADMIN_EMAIL_SYSTEM_ACTION_TRIGGER%',
            'systemAction',
            (new Attributes())->setOptions(ElementHelper::arrayToSelectOptions($options))->setInputClass(AssetsEnum::SELECT2)
        )->addDropdown(
            '%ADMIN_EMAIL_SYSTEM_ACTION_RESULT_TRIGGER%',
            'state',
            (new Attributes())->setOptions(ElementHelper::arrayToSelectOptions([
                'success' => '%ADMIN_ALERT_SUCCESS%',
                'danger' => '%ADMIN_ALERT_DANGER%',
                'notice' => '%ADMIN_ALERT_INFO%',
                'warning' => '%ADMIN_ALERT_WARNING%',
                'custom' => '%ADMIN_HARD_CODED%'
            ]))->setInputClass(AssetsEnum::SELECT2)
        )->addSubmitButton('%CORE_SAVE%');
    }
}

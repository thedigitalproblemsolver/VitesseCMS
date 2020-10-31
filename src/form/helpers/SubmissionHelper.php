<?php declare(strict_types=1);

namespace VitesseCms\Form\Helpers;

use Misd\Linkify\Linkify;
use VitesseCms\Communication\Repositories\NewsletterListRepository;
use VitesseCms\Communication\Repositories\NewsletterRepository;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Database\Utils\MongoUtil;
use VitesseCms\Form\Enums\SubmissionEnum;
use VitesseCms\Form\Models\Submission;
use Phalcon\Di;
use ReflectionObject;
use ReflectionProperty;

class SubmissionHelper
{
    public static function getHtmlTable(AbstractCollection $submission): string
    {
        return self::buildHtmlTable($submission, SubmissionEnum::EXCLUDED_FIELDS_USER);
    }

    public static function getHtmlAdminTable(
        Submission $submission,
        bool $linkify = false
    ): string {
        return self::buildHtmlTable($submission, SubmissionEnum::EXCLUDED_FIELDS_ADMIN, $linkify);
    }

    public static function bindFormPost(
        Submission $submission,
        array $array
    ): void {
        foreach ($array as $key => $value) :
            $submission->addFieldname($key);
            $submission->set($key, $value);
        endforeach;
    }

    protected static function parseValue($value, bool $linkify = false): string
    {
        $return = $value;
        if (is_object($value)) :
            return $value->_('name');
        endif;

        if (is_array($value)) :
            return $value['name'] ?? '';
        endif;

        if (is_string($value) && MongoUtil::isObjectId($value)) :
            $item = (new ItemRepository())->getById($value);
            if ($item !== null) :
                $return = $item->getNameField();
            endif;
            $newsletter = (new NewsletterListRepository())->getById($value);
            if ($newsletter !== null) :
                $return = $newsletter->getNameField();
            endif;
        endif;

        if ($linkify) :
            if (is_file(Di::getDefault()->get('config')->get('uploadDir').'uploads/'.$value)) :
                $return = Di::getDefault()->get('url')->getBaseUri().
                    'uploads/'.
                    Di::getDefault()->get('config')->get('account').
                    '/uploads/'.$value;
            endif;

            $return = (new Linkify(['attr' => ['target' => '_blank']]))->process($return);
        endif;

        return $return;
    }

    protected static function buildHtmlTable(
        AbstractCollection $submission,
        array $excludedFields,
        bool $linkify = false
    ): string {
        $table = '<table class="table">';
        $properties = (new ReflectionObject($submission))->getProperties(
            ReflectionProperty::IS_PUBLIC
        );

        foreach ($properties as $property) :
            if (!in_array($property->name, $excludedFields, true)) :
                $name = $property->name;
                if (
                    is_array($submission->_('fieldNames'))
                    && isset($submission->_('fieldNames')[$name])
                ) :
                    $name = $submission->_('fieldNames')[$name];
                endif;

                $table .= '<tr>
                    <td>'.$name.'</td>
                    <td width="15px"></td>
                    <td>'.self::parseValue($submission->_($property->name), $linkify).'</td>
                </tr>';
            endif;
        endforeach;
        $table .= '</table>';

        return $table;
    }
}

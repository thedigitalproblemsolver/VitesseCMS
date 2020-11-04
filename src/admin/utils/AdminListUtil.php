<?php declare(strict_types=1);

namespace VitesseCms\Admin\Utils;

use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Database\Utils\MongoUtil;
use VitesseCms\User\Services\AclService;
use Phalcon\Tag;

class AdminListUtil
{
    public static function getAdminListButtons(
        AbstractCollection $item,
        string $editBaseUri,
        AclService $acl,
        array $unDeletable = []
    ): string
    {
        $return = '';

        if ($item->hasSlug()):
            $return = Tag::linkTo([
                'action' => $item->_('slug'),
                'target' => '_blank',
                'title' => 'Preview',
                'class' => 'fa fa-eye',
            ]);
        endif;

        $return .= $item->getExtraAdminListButtons();

        if ($acl->hasAccess('togglepublish')) :
            $return .= Tag::linkTo([
                'action' => $editBaseUri . '/togglePublish/' . $item->getId(),
                'class' => ItemHelper::getPublishIcon($item->isPublished()),
                'title' => ItemHelper::getPublishText($item->isPublished()),
                'id' => 'publish_' . $item->getId(),
            ]);
        endif;
        if ($acl->hasAccess('edit')) :
            $showAddChild = false;

            $datagroupId = $item->_('datagroup');
            if (MongoUtil::isObjectId($datagroupId)) :
                Datagroup::setFindPublished(false);
                /** @var Datagroup $datagroup */
                $datagroup = Datagroup::findById($datagroupId);
                if ($datagroup) :
                    $showAddChild = $datagroup->hasChildren();
                endif;
            endif;

            if ($showAddChild) :
                $return .= Tag::linkTo([
                    'action' => $editBaseUri . '/edit/?parentId=' . $item->getId(),
                    'class' => 'fa fa-plus openmodal',
                    'title' => '%ADMIN_ADD_CHILD_ITEM%',
                ]);
            endif;

            $return .= Tag::linkTo([
                'action' => $editBaseUri . '/edit/' . $item->getId(),
                'class' => 'fa fa-edit openmodal',
                'title' => '%ADMIN_EDIT_ITEM%',
            ]);
        endif;

        if ($acl->hasAccess('copy')) :
            $return .= Tag::linkTo([
                'action' => $editBaseUri . '/copy/' . $item->getId(),
                'class' => 'fa fa-copy',
                'title' => '%ADMIN_COPY_ITEM%',
            ]);
        endif;

        if (
            $acl->hasAccess('delete')
            && !in_array($item->getId(), $unDeletable, true)
        ) :
            $return .= Tag::linkTo([
                'action' => $editBaseUri . '/delete/' . $item->getId(),
                'class' => 'fa fa-trash',
                'title' => '%ADMIN_DELETE_ITEM%',
                'id' => 'delete_' . $item->getId(),
            ]);
        endif;

        return $return;
    }
}

<?php declare(strict_types=1);

namespace VitesseCms\Communication\Models;

use VitesseCms\Communication\Factories\NewsletterListMemberFactory;
use VitesseCms\Communication\Helpers\NewsletterListHelper;
use VitesseCms\Communication\Helpers\NewsletterQueueHelper;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\User\Models\User;

class NewsletterList extends AbstractCollection
{
    /**
     * @var array
     */
    public $members;

    public function onConstruct()
    {
        $this->members = [];
    }

    public function addMember(string $email): NewsletterList
    {
        if (!NewsletterListHelper::emailExistsAsMember($email, $this)) :
            $userId = '';
            User::setFindValue('email', $email);
            $user = User::findFirst();
            if ($user) :
                $userId = (string)$user->getId();
            endif;

            $members = $this->_('members');
            if (!\is_array($members)) :
                $members = [];
            endif;

            $member = NewsletterListMemberFactory::create(
                $email,
                new \DateTime(),
                $this,
                $userId
            );
            $members[] = $member;

            if ($user) :
                $userNewsletterLists = $user->_('newsletterLists');
                if (!\is_array($userNewsletterLists)) :
                    $userNewsletterLists = [];
                endif;
                $userNewsletterLists[(string)$this->getId()] = $member;
                $user->set('newsletterLists', $userNewsletterLists);
                $user->save();
            endif;

            $this->set('members', $members);
            $this->di->log->write(
                $this->getId(),
                __CLASS__,
                'Added '.$email.' to '.$this->_('name')
            );
        endif;

        return $this;
    }

    public function subscribeMember(string $email): NewsletterList
    {
        $members = (array)$this->_('members');
        foreach ($members as $key => $member) :
            if ($member['email'] === $email) :
                $members[$key]['subscribeDate'] = (new \DateTime())->format('Y-m-d H:i:s');
                $members[$key]['unSubscribeDate'] = null;
                if ($member['userId']) :
                    $user = User::findById($member['userId']);
                    if ($user) :
                        $userNewsletterLists = $user->_('newsletterLists');
                        if (
                            \is_array($userNewsletterLists)
                            && isset($userNewsletterLists[(string)$this->getId()])
                        ) :
                            $userNewsletterLists[(string)$this->getId()] = $members[$key];
                            $user->set('newsletterLists', $userNewsletterLists)->save();
                        endif;
                    endif;
                endif;
            endif;
        endforeach;
        $this->set('members', $members);

        $this->di->log->write(
            $this->getId(),
            __CLASS__,
            'Subscribe '.$email.' from '.$this->_('name').' by admin'
        );

        return $this;
    }

    public function unsubscribeMember(string $email): NewsletterList
    {
        $members = (array)$this->_('members');
        foreach ($members as $key => $member) :
            if ($member['email'] === $email && empty($member['unSubscribeDate'])) :
                $members[$key]['unSubscribeDate'] = (new \DateTime())->format('Y-m-d H:i:s');
                if ($member['userId']) :
                    $user = User::findById($member['userId']);
                    if ($user) :
                        $userNewsletterLists = $user->_('newsletterLists');
                        if (
                            \is_array($userNewsletterLists)
                            && isset($userNewsletterLists[(string)$this->getId()])
                        ) :
                            $userNewsletterLists[(string)$this->getId()] = $members[$key];
                            $user->set('newsletterLists', $userNewsletterLists);
                            $user->save();
                        endif;
                    endif;
                endif;
            endif;
        endforeach;
        $this->set('members', $members);

        NewsletterQueueHelper::removeByNewsletterList($this, $email);

        $this->di->log->write(
            $this->getId(),
            __CLASS__,
            'Unsubscribe '.$email.' from '.$this->_('name')
        );

        return $this;
    }

    public function removeMember(string $email): NewsletterList
    {
        $members = (array)$this->_('members');
        foreach ($members as $key => $member) :
            if (isset($member['email']) && $member['email'] === $email) :
                unset($members[$key]);
                if ($member['userId']) :
                    $user = User::findById($member['userId']);
                    if ($user) :
                        $userNewsletterLists = $user->_('newsletterLists');
                        if (
                            \is_array($userNewsletterLists)
                            && isset($userNewsletterLists[(string)$this->getId()])
                        ) :
                            unset($userNewsletterLists[(string)$this->getId()]);
                            $user->set('newsletterLists', $userNewsletterLists);
                            $user->save();
                        endif;
                    endif;
                endif;
            endif;
        endforeach;
        $this->set('members', $members);

        NewsletterQueueHelper::removeByNewsletterList($this, $email, true);

        $this->di->log->write(
            $this->getId(),
            __CLASS__,
            'Removed '.$email.' from '.$this->_('name')
        );

        return $this;
    }

    public function getMembers(): array
    {
        return $this->members;
    }
}

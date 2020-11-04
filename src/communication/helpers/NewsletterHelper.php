<?php

namespace VitesseCms\Communication\Helpers;

use VitesseCms\Communication\Factories\NewsletterListMemberFactory;
use VitesseCms\Communication\Models\Newsletter;
use VitesseCms\Communication\Models\NewsletterList;

/**
 * Class NewsletterHelper
 */
class NewsletterHelper {

    /**
     * @param Newsletter $newsletter
     * @param array|null $member
     *
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public static function queueMembers(Newsletter $newsletter, array $member = null): void
    {
        if(!$newsletter->_('hasChildren')) :
            NewsletterQueueHelper::addToQueue($newsletter, null, $member);
        else :
            Newsletter::setFindValue('parentId', (string)$newsletter->getId());
            $childNewsletters = Newsletter::findAll();
            $referenceTime = new \DateTime();
            /** @var Newsletter $childNewsletter */
            foreach ($childNewsletters as $childNewsletter) :
                if($childNewsletter->_('days')) :
                    $referenceTime->modify('+'.$childNewsletter->_('days').' days');
                endif;
                if($childNewsletter->_('sendTime')) :
                    $timeArray = explode(':',$childNewsletter->_('sendTime'));
                    $referenceTime->setTime($timeArray[0],$timeArray[1],0);
                endif;
                NewsletterQueueHelper::addToQueue( $childNewsletter, $referenceTime, $member);
            endforeach;
        endif;
    }

    /**
     * @param Newsletter $newsletter
     * @param string $email
     *
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public static function addMemberByEmail(Newsletter $newsletter, string $email ): void
    {
        $newsletterList = NewsletterList::findById($newsletter->_('list'));
        if($newsletterList) :
            $newsletterList->addMember($email);
            $newsletterList->save();
            if($newsletter->_('sendAfterSubscribtion')) {
                self::queueMembers(
                    $newsletter,
                    NewsletterListMemberFactory::create($email, new \DateTime(), $newsletterList)
                );
            }
        endif;
    }

    /**
     * @param Newsletter $newsletter
     * @param string $email
     *
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public static function unsubscribeMemberByEmail(Newsletter $newsletter, string $email ) {
        $newsletterList = NewsletterList::findById($newsletter->_('list'));
        if($newsletterList) :
            $newsletterList->unsubscribeMember($email);
            $newsletterList->save();
        endif;
    }

    /**
     * @param Newsletter $newsletter
     * @param string $email
     *
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public static function removeMemberByEmail(Newsletter $newsletter, string $email ) {
        $newsletterList = NewsletterList::findById($newsletter->_('list'));
        if($newsletterList) :
            $newsletterList->removeMember($email);
            $newsletterList->save();
        endif;
    }
}

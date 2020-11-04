<?php

namespace VitesseCms\Communication\Factories;

use DateTime;
use VitesseCms\Communication\Models\NewsletterList;

/**
 * Class NewsletterListMemberFactory
 */
class NewsletterListMemberFactory
{
    /**
     * @param string $email
     * @param DateTime $subscribeDate
     * @param NewsletterList $newsletterList
     * @param string $userId
     *
     * @return array
     */
    public static function create(
        string $email,
        DateTime $subscribeDate,
        NewsletterList $newsletterList,
        string $userId = ''
    ): array {
        return [
            'email'           => $email,
            'userId'          => $userId,
            'newsletterListId' => (string) $newsletterList->getId(),
            'subscribeDate'   => $subscribeDate->format('Y-m-d H:i:s'),
            'unSubscribeDate' => null,
            'deletedOn'       => null,
        ];
    }
}

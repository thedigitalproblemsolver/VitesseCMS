<?php

namespace VitesseCms\Communication\Factories;

use VitesseCms\Communication\Models\Email;

/**
 * Class EmailFactory
 */
class EmailFactory
{
    /**
     * @param string $subject
     * @param string $body
     * @param string $systemAction
     * @param string $triggerState
     * @param bool $published
     * @param string|null $alternativeRecipient
     * @param string|null $messageSuccess
     * @param string|null $messageError
     *
     * @return Email
     */
    public static function create(
        string $subject,
        string $body,
        string $systemAction,
        string $triggerState,
        bool $published = false,
        string $alternativeRecipient = null,
        string $messageSuccess = null,
        string $messageError = null
    ): Email {
        $email = new Email();
        $email->set('subject', $subject, true);
        $email->set('body', $body, true);
        $email->set('systemAction', $systemAction);
        $email->set('state', $triggerState);
        $email->set('published', $published);
        $email->set('recipient', $alternativeRecipient,true);
        $email->set('messageSuccess', $messageSuccess, true);
        $email->set('messageError', $messageError,true);

        return $email;
    }
}

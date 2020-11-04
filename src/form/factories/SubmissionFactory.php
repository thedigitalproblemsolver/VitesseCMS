<?php declare(strict_types=1);

namespace VitesseCms\Form\Factories;

use VitesseCms\Form\Models\Submission;
use VitesseCms\Language\Models\Language;
use Phalcon\Di;

class SubmissionFactory
{
    public static function createSubmit(): Submission
    {
        $submission = new Submission();
        $submission->set('sourceUri', $_SERVER['HTTP_REFERER']);
        $submission->set('ipAddress', $_SERVER['REMOTE_ADDR']);
        Language::setFindValue('short', Di::getDefault()->get('configuration')->getLanguageShort());
        $submission->set('language', Language::findFirst() );

        if(Di::getDefault()->get('user')->loggedIn()) :
            $submission->set('user', Di::getDefault()->get('user'));
        endif;

        return $submission;
    }
}

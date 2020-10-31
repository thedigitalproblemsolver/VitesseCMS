<?php declare(strict_types=1);

namespace VitesseCms\Communication\Helpers;

use VitesseCms\Communication\Factories\NewsletterQueueFactory;
use VitesseCms\Communication\Models\Newsletter;
use VitesseCms\Communication\Models\NewsletterList;
use VitesseCms\Communication\Models\NewsletterQueue;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\Models\JobQueue;
use VitesseCms\Core\Services\RouterService;
use VitesseCms\Core\Services\ViewService;
use VitesseCms\Database\Utils\MongoUtil;
use VitesseCms\Sef\Utils\UtmUtil;
use DateTime;
use VitesseCms\Setting\Services\SettingService;
use VitesseCms\User\Models\User;
use MongoDB\BSON\ObjectId;
use Phalcon\Di;
use Phalcon\Http\Request;
use Phalcon\Mvc\User\Component;
use Phalcon\Queue\Beanstalk\Job;

class NewsletterQueueHelper
{
    public static function send(
        NewsletterQueue $newsletterQueue,
        SettingService $setting,
        ViewService $view
    ): bool {
        $view->set(
            'sendMailAfterFooter',
            '{UNSUBSCRIBE}%NEWSLETTER_UNSUBSCRIBE%{/UNSUBSCRIBE}
            <img src="'.$view->getVar('BASE_URI').'communication/newsletterqueue/opened/'.
            (string)$newsletterQueue->getId().'"
             style="width:1px;height:1px" 
             alt="'.$setting->get('WEBSITE_DEFAULT_NAME').'" />'
        );
        Newsletter::setFindPublished(false);
        $newsletter = Newsletter::findById($newsletterQueue->_('newsletterId'));
        $view->set('sendMailHeaderImage', $newsletter->_('emailHeaderImage'));
        $view->set('sendMailMotto', $newsletter->_('motto'));
        if($newsletter->_('emailHeaderTargetPage')) :
            $view->set('sendMailHeaderUrl', 'page:'.$newsletter->_('emailHeaderTargetPage'));
        endif;

        UtmUtil::setSource('newsletter');
        UtmUtil::setMedium('email');
        UtmUtil::setCampaign($newsletterQueue->_('subject'));

        $component = new Component();
        $component->content->addEventInput('newsletterQueueId',(string)$newsletterQueue->getId());
        if(MongoUtil::isObjectId($newsletterQueue->_('userId'))) :
            $component->content->addEventInput('userId',$newsletterQueue->_('userId'));
        else :
            User::setFindValue('email', $newsletterQueue->_('email'));
            $user = User::findFirst();
            if($user) :
                $component->content->addEventInput('userId',(string)$user->getId());
            endif;
        endif;

        return (bool) $component->mailer->prepareMail(
            trim($newsletterQueue->_('email')),
            $newsletterQueue->_('subject'),
            $newsletterQueue->_('body'),
            $newsletter->_('emailType'),
            ''
        )->send();
    }

    public static function recordOpened(string $newsletterQueueId): void
    {
        NewsletterQueue::setFindPublished(false);
        $newsletterQueue = NewsletterQueue::findById($newsletterQueueId);
        if (
            $newsletterQueue
            && $newsletterQueue->_('dateOpened') === ''
        ) :
            $newsletterQueue->set('dateOpened',
                (new DateTime())->format('Y-m-d H:i:s')
            )->save();
        endif;
    }

    public static function addToQueue(
        Newsletter $newsletter,
        ?DateTime $referenceTime = null,
        ?array $newMember = null
    ): void {
        $newsletterList = NewsletterList::findById($newsletter->_('list'));
        $jobOptions = [];
        if($newsletter->_('sendDate') && $newsletter->_('sendTime')) :
            $diffInSeconds = strtotime(
                $newsletter->_('sendDate').' '.
                $newsletter->_('sendTime')
            ) - (new \DateTime())->getTimestamp();
            $jobOptions['delay'] = $diffInSeconds;
        elseif($referenceTime !== null) :
            $diffInSeconds = $referenceTime->getTimestamp() - (new \DateTime())->getTimestamp();
            $jobOptions['delay'] = $diffInSeconds;
        endif;

        if($newMember) :
            $members[] = $newMember;
        else :
            $members = (array)$newsletterList->_('members');
        endif;

        foreach ($members as $member) :
            if ($member['unSubscribeDate'] === null) :
                NewsletterQueue::setFindPublished(false);
                NewsletterQueue::setFindValue('email', $member['email']);
                NewsletterQueue::setFindValue('newsletterId', (string)$newsletter->getId());
                NewsletterQueue::setFindValue('newsletterListId', (string)$newsletterList->getId());
                if (NewsletterQueue::count() === 0) :
                    $user = User::findById($member['userId']);
                    $userId = '';
                    if ($user) :
                        $userId = (string)$user->getId();
                    endif;

                    $newsletterQueue = NewsletterQueueFactory::create(
                        $member['email'],
                        $userId,
                        (string)$newsletter->getId(),
                        (string)$newsletterList->getId(),
                        $newsletter->_('subject'),
                        $newsletter->_('body')
                    );
                    $newsletterQueue->setId(new ObjectId());
                    $request = new Request();
                    $router = new RouterService(
                        $user ? $user: new User(),
                        $request,
                        Di::getDefault()->get('configuration'),
                        Di::getDefault()->get('url'),
                        Di::getDefault()->get('cache'),
                        Di::getDefault()->get('view'),
                        new ItemRepository()
                    );
                    $router->setDefaults(
                        [
                            'module' => 'communication',
                            'controller' => 'adminnewsletterqueue',
                            'action' => 'sendqueuednewsletter',
                            'params'  => [
                                (string)$newsletterQueue->getId()
                            ]
                        ]
                    );


                    $jobId = Di::getDefault()->get('jobQueue')->create(
                        $router,
                        $request,
                        $user?$user:null,
                        $jobOptions
                    );
                    $newsletterQueue->set('jobId', $jobId);
                    $newsletterQueue->save();
                endif;
            endif;
        endforeach;
    }
    
    public static function removeByNewsletterList(
        NewsletterList $newsletterList,
        string $email,
        bool $removeAll = false
    ): void {
        NewsletterQueue::setFindValue('email', $email);
        NewsletterQueue::setFindValue('newsletterListId', (string)$newsletterList->getId());
        NewsletterQueue::setFindPublished(false);
        if(!$removeAll) :
            NewsletterQueue::setFindValue('dateSent', null);
        endif;
        $newsletterQueues = NewsletterQueue::findAll();

        foreach ($newsletterQueues as $newsletterQueue) :
            if($newsletterQueue->_('jobId')) :
                JobQueue::setFindPublished(false);
                JobQueue::setFindValue('jobId', $newsletterQueue->_('jobId'));
                JobQueue::setFindValue(
                    'params',
                    (string)$newsletterQueue->getId(),
                    'like'
                );
                $jobQueue = JobQueue::findFirst();
                if($jobQueue) {
                    $jobQueue->delete();
                }
                /** @var Job $jobQueue */
                $jobQueue = Di::getDefault()->get('jobQueue')->jobPeek($newsletterQueue->_('jobId'));
                if($jobQueue) {
                    $jobQueue->delete();
                }
            endif;
            $newsletterQueue->delete();
        endforeach;
    }
}

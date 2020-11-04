<?php declare(strict_types=1);

namespace VitesseCms\Communication\Services;

use VitesseCms\Content\Services\ContentService;
use VitesseCms\Core\Services\ConfigService;
use VitesseCms\Core\Services\ViewService;
use VitesseCms\Core\Utils\FileUtil;
use VitesseCms\Sef\Utils\UtmUtil;
use VitesseCms\Setting\Services\SettingService;
use Phalcon\Mailer\Manager;
use Phalcon\Mailer\Message;

class MailerService extends Manager
{
    /**
     * @var SettingService
     */
    protected $setting;

    /**
     * @var ConfigService
     */
    protected $configuration;

    /**
     * @var ContentService
     */
    protected $content;

    /**
     * @var ViewService
     */
    protected $view;

    public function __construct(
        SettingService $setting,
        ConfigService $configuration,
        ContentService $content,
        ViewService $viewService
    ) {
        parent::__construct([
            'driver' => 'mail',
            'from'   => [
                'email' => $setting->get('WEBSITE_CONTACT_EMAIL'),
                'name'  => $setting->get('WEBSITE_DEFAULT_NAME'),
            ],
        ]);
        $this->setting = $setting;
        $this->configuration = $configuration;
        $this->content = $content;
        $this->view = $viewService;
    }

    public function sendMail(
        string $toAddress,
        string $subject,
        string $body,
        string $emailType = '',
        string $replyTo = ''
    ): bool {
        return (bool)$this->prepareMail(
            $toAddress,
            $subject,
            $body,
            $emailType,
            $replyTo
        )->send();
    }

    public function prepareMail(
        string $toAddress,
        string $subject,
        string $body,
        string $emailType = '',
        string $replyTo = ''
    ): Message {
        $mailMessage = $this->createMessage()
            ->to($toAddress)
            ->subject($this->prepareString($subject))
            ->content($this->parseBody($body, $emailType))
            ->bcc($this->setting->get('WEBSITE_CATCHALL_EMAIL'));

        if ($replyTo) :
            $mailMessage->replyTo($replyTo);
        endif;

        $this->embedImages($mailMessage);

        return $mailMessage;
    }

    protected function parseBody(string $body, string $emailType = ''): string
    {
        $path = 'template/core/views/emails';
        $template = 'core';

        if (!empty($emailType)) :
            $tmp = explode('/', $emailType);
            $tmp = array_reverse($tmp);
            $template = $tmp[0];
            unset($tmp[0]);
            $tmp = array_reverse($tmp);
            $path = implode('/', $tmp);
        endif;

        return UtmUtil::append($this->content->parseContent(
            $this->view->renderTemplate(
                $template,
                $this->configuration->getRootDir().$path,
                ['sendMailBody' => $this->prepareString($body)]
            )
        ));
    }

    protected function prepareString(string $content): string
    {
        preg_match_all('/{{([a-zA-Z_]*)}}/', $content, $aMatches);
        if (is_array($aMatches) && isset($aMatches[1]) && is_array($aMatches[1])) :
            foreach ($aMatches[1] as $key => $value) :
                $content = str_replace(
                    ['http://{{'.$value.'}}', 'https://{{'.$value.'}}', '{{'.$value.'}}'],
                    ['{{'.$value.'}}', '{{'.$value.'}}', $this->view->getVar($value)],
                    $content
                );
            endforeach;
        endif;

        return $content;
    }

    protected function embedImages(Message $message): void
    {
        $content = $message->getContent();
        $search = $replace = [];

        preg_match_all('@src="([^"]+)"@', $content, $imageUrls);
        if (is_array($imageUrls) && isset($imageUrls[1]) && is_array($imageUrls[1])) :
            foreach ($imageUrls[1] as $imageUrl) :
                $localFile = FileUtil::urlToLocalMapper($imageUrl);
                if (is_file($localFile)):
                    $cid = $message->embed($localFile);
                    $search[] = $imageUrl;
                    $replace[] = $cid;
                endif;
            endforeach;

            $message->content(str_replace($search, $replace, $content));
        endif;
    }
}

<?php declare(strict_types=1);

namespace VitesseCms\Communication\Repositories;

use VitesseCms\Block\Repositories\BlockFormBuilderRepository;
use VitesseCms\Communication\Interfaces\RepositoryInterface;
use VitesseCms\Core\Repositories\DatagroupRepository;
use VitesseCms\Language\Repositories\LanguageRepository;

class RepositoryCollection implements RepositoryInterface
{
    /**
     * @var NewsletterTemplateRepository
     */
    public $newsletterTemplate;

    /**
     * @var BlockFormBuilderRepository
     */
    public $blockFormBuilder;

    /**
     * @var LanguageRepository
     */
    public $language;

    /**
     * @var DatagroupRepository
     */
    public $datagroup;

    /**
     * @var NewsletterRepository
     */
    public $newsletter;

    /**
     * @var NewsletterListRepository
     */
    public $newsletterList;

    /**
     * @var NewsletterQueueRepository
     */
    public $newsletterQueue;

    public function __construct(
        NewsletterTemplateRepository $newsletterTemplateRepository,
        BlockFormBuilderRepository $blockFormBuilderRepository,
        LanguageRepository $languageRepository,
        DatagroupRepository $datagroupRepository,
        NewsletterRepository $newsletterRepository,
        NewsletterListRepository $newsletterListRepository,
        NewsletterQueueRepository $newsletterQueueRepository
    ) {
        $this->newsletterTemplate = $newsletterTemplateRepository;
        $this->blockFormBuilder = $blockFormBuilderRepository;
        $this->language = $languageRepository;
        $this->datagroup = $datagroupRepository;
        $this->newsletter = $newsletterRepository;
        $this->newsletterList = $newsletterListRepository;
        $this->newsletterQueue = $newsletterQueueRepository;
    }
}

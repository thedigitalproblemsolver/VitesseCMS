<?php declare(strict_types=1);

namespace VitesseCms\Form\Repositories;

use VitesseCms\Block\Repositories\BlockFormBuilderRepository;
use VitesseCms\Communication\Repositories\NewsletterRepository;
use VitesseCms\Core\Repositories\DatagroupRepository;
use VitesseCms\Form\Interfaces\RepositoryInterface;
use VitesseCms\Language\Repositories\LanguageRepository;

class RepositoryCollection implements RepositoryInterface
{
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

    public function __construct(
        BlockFormBuilderRepository $blockFormBuilderRepository,
        LanguageRepository $languageRepository,
        DatagroupRepository $datagroupRepository,
        NewsletterRepository $newsletterRepository
    ) {
        $this->blockFormBuilder = $blockFormBuilderRepository;
        $this->language = $languageRepository;
        $this->datagroup = $datagroupRepository;
        $this->newsletter = $newsletterRepository;
    }
}

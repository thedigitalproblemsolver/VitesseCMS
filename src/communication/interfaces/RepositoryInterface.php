<?php declare(strict_types=1);

namespace VitesseCms\Communication\Interfaces;

use VitesseCms\Block\Repositories\BlockFormBuilderRepository;
use VitesseCms\Communication\Repositories\NewsletterListRepository;
use VitesseCms\Communication\Repositories\NewsletterQueueRepository;
use VitesseCms\Communication\Repositories\NewsletterRepository;
use VitesseCms\Communication\Repositories\NewsletterTemplateRepository;
use VitesseCms\Core\Repositories\DatagroupRepository;
use VitesseCms\Language\Repositories\LanguageRepository;

/**
 * Interface RepositoryInterface
 * @property NewsletterTemplateRepository $newsletterTemplate
 * @property BlockFormBuilderRepository $blockFormBuilder
 * @property LanguageRepository $language
 * @property DatagroupRepository $datagroup
 * @property NewsletterRepository $newsletter
 * @property NewsletterListRepository $newsletterList
 * @property NewsletterQueueRepository $newsletterQueue
 */
interface RepositoryInterface
{
}

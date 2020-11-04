<?php declare(strict_types=1);

namespace VitesseCms\Form\Interfaces;

use VitesseCms\Block\Repositories\BlockFormBuilderRepository;
use VitesseCms\Communication\Repositories\NewsletterRepository;
use VitesseCms\Core\Repositories\DatagroupRepository;
use VitesseCms\Language\Repositories\LanguageRepository;

/**
 * Interface RepositoryInterface
 * @property BlockFormBuilderRepository $blockFormBuilder
 * @property LanguageRepository $language
 * @property DatagroupRepository $datagroup
 * @property NewsletterRepository $newsletter
 */
interface RepositoryInterface
{
}

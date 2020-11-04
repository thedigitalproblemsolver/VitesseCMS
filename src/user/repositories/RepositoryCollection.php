<?php declare(strict_types=1);

namespace VitesseCms\User\Repositories;

use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\User\Interfaces\RepositoriesInterface;

class RepositoryCollection implements RepositoriesInterface
{
    /**
     * @var UserRepository
     */
    public $user;

    /**
     * @var ItemRepository
     */
    public $item;

    public function __construct(
        UserRepository $userRepository,
        ItemRepository $itemRepository
    ) {
        $this->user = $userRepository;
        $this->item = $itemRepository;
    }
}

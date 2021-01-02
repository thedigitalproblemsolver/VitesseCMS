<?php declare(strict_types=1);

namespace VitesseCms\Database\Models;

class FindOrder
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var int
     */
    protected $order;

    public function __construct(
        string $key,
        int $order
    )
    {
        $this->key = $key;
        $this->order = $order;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getOrder(): int
    {
        return $this->order;
    }
}

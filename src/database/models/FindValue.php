<?php declare(strict_types=1);

namespace VitesseCms\Database\Models;

class FindValue
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var string
     */
    protected $type;

    public function __construct(
        string $key,
        $value,
        string $type = 'string'
    ) {
        $this->key = $key;
        $this->value = $value;
        $this->type = $type;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getType(): string
    {
        return $this->type;
    }
}

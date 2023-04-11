<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Application\Entities;

use ddziaduch\OutboxPattern\Application\Events\ProductCreated;
use ddziaduch\OutboxPattern\Application\ValueObjects\ProductId;

final class Product
{
    /** @var array<object> */
    private array $events = [];

    private function __construct(
        public readonly ProductId $id,
        public readonly string $name,
    ) {
    }

    public static function create(string $name): self
    {
        $id = new ProductId();
        $instance = new self($id, $name);
        $instance->events[] = new ProductCreated($id->value, $name);

        return $instance;
    }

    /** @return iterable<object> */
    public function events(): iterable
    {
        foreach ($this->events as $event) {
            yield $event;
        }

        $this->events = [];
    }
}

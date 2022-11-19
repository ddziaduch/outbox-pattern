<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Domain\Aggregate;

use ddziaduch\OutboxPattern\Domain\Event\Event;
use ddziaduch\OutboxPattern\Domain\Event\ProductCreated;
use ddziaduch\OutboxPattern\Domain\ValueObject\ProductId;

final class Product implements AggregateRoot
{
    /** @var array<Event> */
    private array $events = [];

    private function __construct(
        private readonly ProductId $id,
        public readonly string $name,
    ) {
    }

    public static function create(ProductId $id, string $name): self
    {
        $instance = new self($id, $name);
        $instance->events[] = new ProductCreated($id);

        return $instance;
    }

    public function id(): ProductId
    {
        return $this->id;
    }

    /** @return iterable<Event> */
    public function events(): iterable
    {
        foreach ($this->events as $event) {
            yield $event;
        }

        $this->events = [];
    }
}

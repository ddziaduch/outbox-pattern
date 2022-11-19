<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests\Fake;

use ddziaduch\OutboxPattern\Domain\Event\Event;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAware;

final class FakeObjectWithOutbox implements OutboxAware
{
    /** @var \SplObjectStorage<\ddziaduch\OutboxPattern\Domain\Event\Event, mixed> */
    private readonly \SplObjectStorage $outbox;

    public function __construct(private readonly mixed $id)
    {
        $this->outbox = new \SplObjectStorage();
    }

    public function getOutbox(): \SplObjectStorage
    {
        return $this->outbox;
    }

    public function id(): mixed
    {
        return $this->id;
    }
}

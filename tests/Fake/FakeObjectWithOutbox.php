<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests\Fake;

use ddziaduch\OutboxPattern\Domain\Event;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAware;

final class FakeObjectWithOutbox implements OutboxAware
{
    /** @var \SplQueue<Event> */
    private readonly \SplQueue $outbox;

    public function __construct(private readonly mixed $id)
    {
        $this->outbox = new \SplQueue();
    }

    public function getOutbox(): \SplQueue
    {
        return $this->outbox;
    }

    public function id(): mixed
    {
        return $this->id;
    }
}

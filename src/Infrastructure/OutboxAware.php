<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure;

use ddziaduch\OutboxPattern\Domain\Event;

interface OutboxAware
{
    public function id(): mixed;

    /** @return \SplQueue<Event> */
    public function getOutbox(): \SplQueue;
}

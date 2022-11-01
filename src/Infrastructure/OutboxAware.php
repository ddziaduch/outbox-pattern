<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure;

use ddziaduch\OutboxPattern\Domain\Event;

interface OutboxAware
{
    /** @return \SplQueue<Event> */
    public function getOutbox(): iterable;
}

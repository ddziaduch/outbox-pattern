<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Application\Port;

use ddziaduch\OutboxPattern\Domain\Event\Event;

interface EventReader
{
    /** @return iterable<Event> */
    public function read(): iterable;
}

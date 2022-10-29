<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure;

use ddziaduch\OutboxPattern\Domain\Event;

interface ObjectWithOutbox
{
    public function getDocument(): object;

    /** @return \SplQueue<Event> */
    public function getOutbox(): \SplQueue;
}

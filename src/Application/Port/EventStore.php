<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Application\Port;

use ddziaduch\OutboxPattern\Domain\Event;

interface EventStore
{
    public function store(Event $event);
}

<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Domain\Event;

use ddziaduch\OutboxPattern\Domain\ValueObject\AggregateRootId;

interface Event
{
    /** @return string UUID */
    public function id(): string;

    public function aggregateRootId(): AggregateRootId;
}

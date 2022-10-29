<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Domain;

interface Event
{
    public function aggregateRootId(): AggregateRootId;

    /** @return class-string<AggregateRoot> */
    public function aggregateRootClassName(): string;
}

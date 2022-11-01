<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Domain;

interface AggregateRoot
{
    public function id(): AggregateRootId;
}

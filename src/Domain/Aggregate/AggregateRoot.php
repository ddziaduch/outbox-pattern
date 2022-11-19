<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Domain\Aggregate;

use ddziaduch\OutboxPattern\Domain\ValueObject\AggregateRootId;

interface AggregateRoot
{
    public function id(): AggregateRootId;
}

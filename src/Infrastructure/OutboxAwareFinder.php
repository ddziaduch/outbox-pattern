<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure;

use ddziaduch\OutboxPattern\Domain\AggregateRootId;

interface OutboxAwareFinder
{
    public function find(AggregateRootId $id): ?OutboxAware;
}

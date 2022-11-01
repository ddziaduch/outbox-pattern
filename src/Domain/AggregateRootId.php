<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Domain;

interface AggregateRootId
{
    public function value(): mixed;
}

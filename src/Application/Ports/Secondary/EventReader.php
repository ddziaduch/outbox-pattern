<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Application\Ports\Secondary;

interface EventReader
{
    /** @return iterable<object> */
    public function read(): iterable;
}

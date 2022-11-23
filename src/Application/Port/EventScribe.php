<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Application\Port;

interface EventScribe
{
    public function write(object $event): void;
}

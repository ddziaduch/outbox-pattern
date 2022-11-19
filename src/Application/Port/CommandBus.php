<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Application\Port;

use ddziaduch\OutboxPattern\Application\Command;

interface CommandBus
{
    public function execute(Command $command): void;
}

<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Application\Ports\Secondary;

use ddziaduch\OutboxPattern\Application\Ports\Primary\Command;

interface CommandBus
{
    public function execute(Command $command): void;
}

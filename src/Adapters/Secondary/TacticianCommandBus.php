<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Adapters\Secondary;

use ddziaduch\OutboxPattern\Application\Ports\Primary\Command;
use ddziaduch\OutboxPattern\Application\Ports\Secondary\CommandBus;

class TacticianCommandBus implements CommandBus
{
    public function __construct(
        private readonly \League\Tactician\CommandBus $bus,
    ) {
    }

    public function execute(Command $command): void
    {
        $this->bus->handle($command);
    }
}

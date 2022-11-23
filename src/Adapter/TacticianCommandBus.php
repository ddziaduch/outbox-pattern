<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Adapter;

use ddziaduch\OutboxPattern\Application\Command;
use ddziaduch\OutboxPattern\Application\Port\CommandBus;

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

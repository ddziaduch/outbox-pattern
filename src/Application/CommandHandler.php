<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Application;

interface CommandHandler
{
    public function __invoke(Command $command): void;
}

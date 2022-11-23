<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Application;

use ddziaduch\OutboxPattern\Application\Port\EventScribe;
use Psr\EventDispatcher\EventDispatcherInterface;

final class EventDispatcherDecorator implements EventDispatcherInterface
{
    public function __construct(
        private readonly EventScribe $eventScribe,
    ) {
    }

    public function dispatch(object $event): object
    {
        $this->eventScribe->write($event);

        return $event;
    }
}

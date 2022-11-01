<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Application;

use ddziaduch\OutboxPattern\Application\Port\EventScribe;
use ddziaduch\OutboxPattern\Domain\Event;
use Psr\EventDispatcher\EventDispatcherInterface;

final class EventDispatcherDecorator implements EventDispatcherInterface
{
    public function __construct(
        private readonly EventScribe $eventStore,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function dispatch(object $event): object
    {
        if ($event instanceof Event) {
            $this->eventStore->write($event);
        } else {
            $this->dispatcher->dispatch($event);
        }

        return $event;
    }
}

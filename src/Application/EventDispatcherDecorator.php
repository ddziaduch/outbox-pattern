<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Application;

use ddziaduch\OutboxPattern\Application\Port\EventScribe;
use ddziaduch\OutboxPattern\Domain\Event\Event;
use Psr\EventDispatcher\EventDispatcherInterface;

final class EventDispatcherDecorator implements EventDispatcherInterface
{
    public function __construct(
        private readonly EventScribe $eventScribe,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function dispatch(object $event): object
    {
        // TODO always save
        if ($event instanceof Event) {
            $this->eventScribe->write($event);
        } else {
            $this->dispatcher->dispatch($event);
        }

        return $event;
    }
}

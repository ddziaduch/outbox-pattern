<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use Psr\EventDispatcher\EventDispatcherInterface;

class Outbox implements EventDispatcherInterface
{
    /** @var object[] */
    private array $events = [];

    public function __destruct()
    {
        if (!empty($this->events)) {
            throw new \LogicException(
                'The object must be persisted after dispatching it\'s events.',
            );
        }
    }

    public function dispatch(object $event): object
    {
        $this->events[] = $event;

        return $event;
    }

    /** @param LifecycleEventArgs<ObjectManager> $args */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if (!property_exists($object, 'outbox')) {
            return;
        }

        $outbox = $object->outbox;

        if (!is_array($outbox)) {
            return;
        }

        $object->outbox = array_merge(
            $object->outbox,
            array_map('serialize', $this->events),
        );

        $this->events = [];
    }
}

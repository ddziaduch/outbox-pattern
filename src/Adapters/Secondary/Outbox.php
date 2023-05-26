<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Adapters\Secondary;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\Event\LifecycleEventArgs;
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

    /** @param LifecycleEventArgs<DocumentManager> $args */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $document = $args->getObject();

        if (!property_exists($document, 'outbox')) {
            return;
        }

        $currentOutbox = $document->outbox;

        if (!is_array($currentOutbox)) {
            return;
        }

        $document->outbox = array_merge(
            $currentOutbox,
            array_map('serialize', $this->events),
        );

        $this->events = [];
    }
}

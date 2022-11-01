<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Adapter;

use ddziaduch\OutboxPattern\Application\Port\EventReader;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAware;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAwareRepositories;

final class MongoEventReader implements EventReader
{
    public function __construct(
        private readonly OutboxAwareRepositories $repositories,
    ) {
    }

    public function read(): iterable
    {
        foreach ($this->repositories->all() as $repository) {
            $objects = $repository->findBy(['outbox' => ['$not' => ['$size' => 0]]]);
            foreach ($objects as $object) {
                if (!$object instanceof OutboxAware) {
                    throw new \LogicException('Expected repository to return collection of ' . OutboxAware::class);
                }

                $outbox = $object->getOutbox();
                while ($outbox->count() > 0) {
                    $event = $outbox->dequeue();
                    yield $event;
                }
            }
        }
    }
}

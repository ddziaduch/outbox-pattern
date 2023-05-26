<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Adapters\Secondary;

use ddziaduch\OutboxPattern\Application\Ports\Secondary\EventReader;
use ddziaduch\OutboxPattern\Infrastructure\Doctrine\OutboxAwareRepositories;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

final class MongoEventReader implements EventReader
{
    public function __construct(
        private readonly OutboxAwareRepositories $repositories,
        private readonly DocumentManager $documentManager,
    ) {
    }

    /** @return iterable<object> */
    public function read(): iterable
    {
        /** @var DocumentRepository<object> $repository */
        foreach ($this->repositories as $repository) {
            $documents = $repository->findBy(['outbox' => ['$not' => ['$size' => 0]]]);
            foreach ($documents as $document) {
                if (!is_object($document)) {
                    throw new \LogicException('Expected document to be an object');
                }

                if (!property_exists($document, 'outbox')) {
                    throw new \LogicException('Expected document to have property outbox');
                }

                if (!is_array($document->outbox)) {
                    throw new \LogicException('Expected outbox to be an array');
                }

                foreach ($document->outbox as $serializedEvent) {
                    $event = unserialize($serializedEvent);
                    if (!is_object($event)) {
                        throw new \LogicException('Expected event to be an object');
                    }
                    yield $event;
                }

                $document->outbox = [];
                $this->documentManager->persist($document);
            }
        }

        $this->documentManager->flush();
    }
}

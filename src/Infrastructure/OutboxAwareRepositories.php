<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;

class OutboxAwareRepositories
{
    public function __construct(private readonly ObjectManager $objectManager)
    {
    }

    /** @return iterable<ObjectRepository<OutboxAware>> */
    public function all(): iterable
    {
        foreach ($this->allMetadata() as $metadata) {
            yield $this->objectManager->getRepository($metadata->getName());
        }
    }

    /** @return iterable<ClassMetadata<OutboxAware>> */
    private function allMetadata(): iterable
    {
        return array_filter(
            $this->objectManager->getMetadataFactory()->getAllMetadata(),
            static fn(ClassMetadata $metadata): bool => is_a(
                $metadata->getName(),
                OutboxAware::class,
                true,
            ),
        );
    }
}

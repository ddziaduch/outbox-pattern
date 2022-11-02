<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;

class OutboxAwareClassMetadata
{
    public function __construct(private readonly ObjectManager $objectManager)
    {
    }

    /** @return iterable<ClassMetadata<OutboxAware>> */
    public function all(): iterable
    {
        /** @var array<ClassMetadata<OutboxAware>> $metadata */
        $metadata = array_filter(
            $this->objectManager->getMetadataFactory()->getAllMetadata(),
            static fn(ClassMetadata $metadata): bool => is_a(
                $metadata->getName(),
                OutboxAware::class,
                true,
            ),
        );

        return $metadata;
    }
}

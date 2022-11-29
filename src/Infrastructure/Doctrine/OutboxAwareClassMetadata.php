<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure\Doctrine;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Traversable;

/** @implements \IteratorAggregate<ClassMetadata<object>> */
class OutboxAwareClassMetadata implements \IteratorAggregate
{
    public function __construct(private readonly ObjectManager $objectManager)
    {
    }

    /** @return Traversable<ClassMetadata<object>> */
    public function getIterator(): Traversable
    {
        foreach ($this->objectManager->getMetadataFactory()->getAllMetadata() as $metadata) {
            $objectClassName = $metadata->getName();
            $objectReflectionClass = new \ReflectionClass($objectClassName);

            if (!$objectReflectionClass->hasProperty('outbox')) {
                continue;
            }

            $outboxMethodReflection = $objectReflectionClass->getProperty('outbox');
            $returnType = $outboxMethodReflection->getType();

            if (
                $returnType instanceof \ReflectionNamedType
                && $returnType->allowsNull() === false
                && $returnType->getName() === 'array'
            ) {
                yield $metadata;
            }
        }
    }
}

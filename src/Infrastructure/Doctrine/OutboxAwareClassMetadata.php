<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure\Doctrine;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Traversable;

/** @implements \IteratorAggregate<ClassMetadata<object>> */
class OutboxAwareClassMetadata implements \IteratorAggregate
{
    public function __construct(private readonly DocumentManager $documentManager)
    {
    }

    /** @return Traversable<ClassMetadata<object>> */
    public function getIterator(): Traversable
    {
        foreach ($this->documentManager->getMetadataFactory()->getAllMetadata() as $metadata) {
            $documentClassName = $metadata->getName();
            $documentReflectionClass = new \ReflectionClass($documentClassName);

            if (!$documentReflectionClass->hasProperty('outbox')) {
                continue;
            }

            $outboxMethodReflection = $documentReflectionClass->getProperty('outbox');
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

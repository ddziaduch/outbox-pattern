<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure\Doctrine\Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Field;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Id;

#[Document(collection: 'products')]
class Product
{
    /** @var string[] */
    #[Field(type: 'collection')]
    public array $outbox = [];

    public function __construct(
        #[Id(type: 'string', strategy: 'NONE')]
        public string $id,
        #[Field(type: 'string')]
        public string $name,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Adapter;

use ddziaduch\OutboxPattern\Application\Port\SaveProduct;
use ddziaduch\OutboxPattern\Domain\Product;
use Doctrine\Persistence\ObjectManager;

class MongoSaveProduct implements SaveProduct
{
    public function __construct(
        private readonly ObjectManager $objectManager,
    ) {
    }

    public function __invoke(Product $product): void
    {
        $this->objectManager->persist(
            new \ddziaduch\OutboxPattern\Infrastructure\Doctrine\Documents\Product(
                $product->id->value,
                $product->name,
            )
        );
    }
}

<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Adapters\Secondary;

use ddziaduch\OutboxPattern\Application\Entities\Product;
use ddziaduch\OutboxPattern\Application\Ports\Secondary\SaveProduct;
use ddziaduch\OutboxPattern\Infrastructure\Doctrine\Documents\Product as ProductDocument;
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
            new ProductDocument(
                $product->id->value,
                $product->name,
            )
        );
        $this->objectManager->flush();
    }
}

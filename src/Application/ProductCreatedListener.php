<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Application;

use ddziaduch\OutboxPattern\Domain\Event\ProductCreated;

final class ProductCreatedListener
{
    public function __invoke(ProductCreated $event): void
    {
        echo sprintf('Product %s created!', $event->productName) . PHP_EOL;
    }
}

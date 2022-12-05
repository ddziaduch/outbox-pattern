<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Application;

use ddziaduch\OutboxPattern\Domain\Event\ProductCreated;

final class ProductCreatedListener
{
    public function __invoke(ProductCreated $event): void
    {
        echo sprintf(
            '%s: Product with name "%s" created!',
            __CLASS__,
            $event->productName
        ) . PHP_EOL;
    }
}

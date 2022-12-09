<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Application;

use ddziaduch\OutboxPattern\Domain\ProductCreated;

final class ProductCreatedListener
{
    public function __invoke(ProductCreated $event): void
    {
        printf(
            '%s: Product with name "%s" created!%s',
            __CLASS__,
            $event->productName,
            PHP_EOL,
        );
    }
}

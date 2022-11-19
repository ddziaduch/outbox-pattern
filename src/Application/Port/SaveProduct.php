<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Application\Port;

use ddziaduch\OutboxPattern\Domain\Aggregate\Product;

interface SaveProduct
{
    public function __invoke(Product $product): void;
}

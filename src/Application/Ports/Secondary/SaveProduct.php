<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Application\Ports\Secondary;

use ddziaduch\OutboxPattern\Application\Entities\Product;

interface SaveProduct
{
    public function __invoke(Product $product): void;
}

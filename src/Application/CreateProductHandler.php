<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Application;

use ddziaduch\OutboxPattern\Application\Port\SaveProduct;
use ddziaduch\OutboxPattern\Domain\Aggregate\Product;
use Psr\EventDispatcher\EventDispatcherInterface;

class CreateProductHandler
{
    public function __construct(
        private readonly SaveProduct $saveProduct,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(CreateProductCommand $command): void
    {
        $product = Product::create($command->name);

        foreach ($product->events() as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        ($this->saveProduct)($product);
    }
}

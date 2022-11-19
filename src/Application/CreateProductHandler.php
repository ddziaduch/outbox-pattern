<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Application;

use ddziaduch\OutboxPattern\Application\Port\SaveProduct;
use ddziaduch\OutboxPattern\Domain\Aggregate\Product;
use ddziaduch\OutboxPattern\Domain\ValueObject\ProductId;
use Psr\EventDispatcher\EventDispatcherInterface;

class CreateProductHandler implements CommandHandler
{
    public function __construct(
        private readonly SaveProduct $saveProduct,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(Command $command): void
    {
        assert($command instanceof CreateProductCommand);

        $product = Product::create(ProductId::new(), $command->name);

        ($this->saveProduct)($product);

        foreach ($product->events() as $event) {
            $this->eventDispatcher->dispatch($event);
        }
    }
}

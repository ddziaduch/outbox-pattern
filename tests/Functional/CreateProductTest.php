<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests\Functional;

use ddziaduch\OutboxPattern\Application\CreateProductCommand;
use ddziaduch\OutboxPattern\Application\Port\CommandBus;
use ddziaduch\OutboxPattern\Infrastructure\ContainerFactory;
use ddziaduch\OutboxPattern\Infrastructure\Doctrine\Documents\Product;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

class CreateProductTest extends TestCase
{
    private ObjectManager $objectManager;
    private CommandBus $commandBus;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new ContainerFactory())->create();
        $this->commandBus = $container->get(CommandBus::class);
        $this->objectManager = $container->get(ObjectManager::class);

        $this->removeAllProductsFromDb();
    }

    public function testCreationPutsEventsToOutbox(): void
    {
        $this->commandBus->execute(new CreateProductCommand('test'));
        $products = $this->objectManager->getRepository(Product::class)->findAll();

        self::assertCount(1, $products);

        /** @var false|Product $product */
        $product = end($products);
        $outbox = $product ? $product->outbox : [];

        self::assertCount(1, $outbox);
    }

    private function removeAllProductsFromDb(): void
    {
        foreach ($this->objectManager->getRepository(Product::class)->findAll() as $product) {
            $this->objectManager->remove($product);
        }

        $this->objectManager->flush();
    }
}

<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests\Functional;

use ddziaduch\OutboxPattern\Application\CreateProductCommand;
use ddziaduch\OutboxPattern\Application\Port\CommandBus;
use ddziaduch\OutboxPattern\Domain\Event\ProductCreated;
use ddziaduch\OutboxPattern\Infrastructure\ContainerFactory;
use ddziaduch\OutboxPattern\Infrastructure\Doctrine\Documents\Product;
use ddziaduch\OutboxPattern\Infrastructure\MongoEventReader;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

/** @covers \ddziaduch\OutboxPattern\Application\CreateProductCommand */
/** @covers \ddziaduch\OutboxPattern\Application\CreateProductHandler */
/** @covers \ddziaduch\OutboxPattern\Infrastructure\EventDispatcherDecorator */
/** @covers \ddziaduch\OutboxPattern\Infrastructure\OutboxRelay */
/** @covers \ddziaduch\OutboxPattern\Infrastructure\MongoEventReader */
class CreateProductTest extends TestCase
{
    private ObjectManager $objectManager;
    private CommandBus $commandBus;
    private MongoEventReader $eventReader;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new ContainerFactory())->create();

        $commandBus = $container->get(CommandBus::class);
        assert($commandBus instanceof CommandBus);
        $this->commandBus = $commandBus;

        $objectManager = $container->get(ObjectManager::class);
        assert($objectManager instanceof ObjectManager);
        $this->objectManager = $objectManager;

        $eventReader = $container->get(MongoEventReader::class);
        assert($eventReader instanceof MongoEventReader);
        $this->eventReader = $eventReader;

        $this->removeAllProductsFromDb();
    }

    public function testCreationPutsEventsToOutbox(): void
    {
        $products = ['first', 'second'];
        foreach ($products as $product) {
            $this->commandBus->execute(new CreateProductCommand($product));
        }

        self::assertCount(
            2,
            $this->objectManager
                ->getRepository(Product::class)
                ->findAll(),
        );

        $events = $this->eventReader->read();
        self::assertCount(2, $events);

        /** @var ProductCreated $event */
        foreach ($events as $key => $event) {
            self::assertInstanceOf(ProductCreated::class, $event);
            self::assertSame($products[$key], $event->productName);
        }
    }

    private function removeAllProductsFromDb(): void
    {
        foreach ($this->objectManager->getRepository(Product::class)->findAll() as $product) {
            $this->objectManager->remove($product);
        }

        $this->objectManager->flush();
    }
}

<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests\Functional;

use ddziaduch\OutboxPattern\Application\Events\ProductCreated;
use ddziaduch\OutboxPattern\Infrastructure\ContainerFactory;
use ddziaduch\OutboxPattern\Infrastructure\Doctrine\Documents\Product;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use League\Event\EventDispatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/** @coversNothing  */
class CreateProductTest extends TestCase
{

    private DocumentManager $documentManager;
    private Application $application;
    /** @var ProductCreated[] */
    private array $interceptedEvents;
    /** @var ObjectRepository<Product> */
    private ObjectRepository $productRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new ContainerFactory())->create();

        $application = $container->get(Application::class);
        assert($application instanceof Application);
        $this->application = $application;

        $documentManager = $container->get(DocumentManager::class);
        assert($documentManager instanceof DocumentManager);

        $this->documentManager = $documentManager;
        $this->productRepository = $documentManager->getRepository(Product::class);

        $this->removeAllProductsFromDb();

        $this->interceptedEvents = [];

        $dispatcher = $container->get(EventDispatcher::class);
        assert($dispatcher instanceof EventDispatcher);

        $dispatcher->subscribeTo(
            ProductCreated::class,
            function (ProductCreated $event): void {
                $this->interceptedEvents[] = $event;
            },
        );

        ob_start();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        ob_end_clean();
    }

    public function testCreationDispatchesEvent(): void
    {
        $productName = 'fake-product';
        $this->runCliCommand(
            'app:create-product',
            ['name' => $productName],
        );

        self::assertCount(
            1,
            $this->productRepository->findAll(),
        );

        $this->runCliCommand('app:dispatch-events');

        self::assertCount(1, $this->interceptedEvents);
        $event = end($this->interceptedEvents);

        self::assertInstanceOf(ProductCreated::class, $event);
        self::assertSame($productName, $event->productName);
    }

    private function removeAllProductsFromDb(): void
    {
        foreach ($this->productRepository->findAll() as $product) {
            $this->documentManager->remove($product);
        }

        $this->documentManager->flush();
    }

    /**
     * @param mixed[] $input
     */
    private function runCliCommand(string $commandName, array $input = []): void
    {
        $command = $this->application->find($commandName);
        $commandTester = new CommandTester($command);
        $commandTester->execute($input);
    }
}

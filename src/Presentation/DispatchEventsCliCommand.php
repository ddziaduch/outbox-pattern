<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Presentation;

use ddziaduch\OutboxPattern\Infrastructure\MongoEventReader;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as CliCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'dispatch-events')]
final class DispatchEventsCliCommand extends CliCommand
{
    public function __construct(
        private readonly MongoEventReader $eventReader,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $events = $this->eventReader->read();

        foreach ($events as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        $output->writeln(count($events) . ' events has been dispatched!');

        return CliCommand::SUCCESS;
    }
}

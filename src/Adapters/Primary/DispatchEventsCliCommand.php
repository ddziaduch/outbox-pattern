<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Adapters\Primary;

use ddziaduch\OutboxPattern\Infrastructure\MongoEventReader;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as SymfonyCliCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'dispatch-events')]
final class DispatchEventsCliCommand extends SymfonyCliCommand
{
    public function __construct(
        private readonly MongoEventReader $eventReader,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = 0;
        foreach ($this->eventReader->read() as $event) {
            $this->eventDispatcher->dispatch($event);
            $count++;
        }

        $output->writeln(
            sprintf(
                '%s: dispatched %u events',
                __CLASS__,
                $count,
            ),
        );

        return SymfonyCliCommand::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Presentation;

use ddziaduch\OutboxPattern\Application\CreateProductCommand as ApplicationCommand;
use ddziaduch\OutboxPattern\Application\Port\CommandBus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'create-product')]
class CreateProductCommand extends Command
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('name', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->commandBus->execute(
                new ApplicationCommand(
                    $input->getArgument('name'),
                ),
            );
        } catch (\Throwable $exception) {
            $output->writeln($exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

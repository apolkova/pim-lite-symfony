<?php
declare(strict_types=1);

namespace App\Command;

use App\Message\ImportProductsMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'pim:import:products', description: 'Enqueue CSV import of products')]
final class ImportProductsCommand extends Command
{
    public function __construct(private MessageBusInterface $bus)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'Path to CSV file (inside container or bind mount)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = (string) $input->getArgument('file');
        $this->bus->dispatch(new ImportProductsMessage($path));
        $output->writeln("<info>Import enqueued: {$path}</info>");
        return Command::SUCCESS;
    }
}

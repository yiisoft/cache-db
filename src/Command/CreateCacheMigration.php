<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Db\Command\CommandInterface;

#[AsCommand(
    name: 'cache/migrate',
    description: 'Creates a migration for the cache table.'
)]
final class CreateCacheMigration extends Command
{
    public function __construct(private DbCache $cache)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(name: 'force', shortcut: 'f', description: 'Force recreation of table if it already exists.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $db = $this->cache->getDb();
        $command = $db->createCommand();
        $schema = $db->getSchema();

        /** @psalm-var bool $force */
        $force = $input->getOption('force');
        $existTable = $schema->getTableSchema($this->cache->getTable(), true);

        if ($force && $existTable !== null) {
            $this->dropTable($command, $io);
        }

        if ($force === false && $existTable !== null) {
            $io->title('Checking if table exists.');
            $io->success('Table ' . $this->cache->getTable() . ' already exists.');

            return Command::SUCCESS;
        }

        $io->title('Creating cache table migration');
        $command->createTable(
            $this->cache->getTable(),
            [
                'id' => $schema->createColumn('string', 128)->notNull(),
                'expire' => $schema->createColumn('integer'),
                'data' => $schema->createColumn('binary'),
                'PRIMARY KEY ([[id]])',
            ],
        )->execute();

        $io->writeln('<fg=green>>>> Table' . $this->cache->getTable() . ' created.');
        $io->writeln('');
        $io->success('Migration created successfully.');

        return Command::SUCCESS;
    }

    public function dropTable(CommandInterface $command, SymfonyStyle $io): void
    {
        $command->dropTable($this->cache->getTable())->execute();

        $io->title('Cache table dropped');
        $io->writeln('<fg=green>>>> Table: ' . $this->cache->getTable() . ' dropped.</>');
    }
}

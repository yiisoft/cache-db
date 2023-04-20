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
    public function __construct(private DbCache $dbCache)
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
        $db = $this->dbCache->getDb();
        $command = $db->createCommand();
        $schema = $db->getSchema();
        $table = $this->dbCache->getTable();

        /** @psalm-var bool $force */
        $force = $input->getOption('force');
        $existTable = $schema->getTableSchema($table, true);

        if ($force && $existTable !== null) {
            $this->dropTable($command, $table, $io);
        }

        if ($force === false && $existTable !== null) {
            $io->title('Checking if table exists.');
            $io->success("Table: $table already exists.");

            return Command::SUCCESS;
        }

        $io->title('Creating cache table migration');
        $command->createTable(
            $table,
            [
                'id' => $schema->createColumn('string', 128)->notNull(),
                'expire' => $schema->createColumn('integer'),
                'data' => $schema->createColumn('binary'),
                'PRIMARY KEY ([[id]])',
            ],
        )->execute();

        $io->writeln("<fg=green>>>> Table: $table created.");
        $io->success('Migration created successfully.');

        return Command::SUCCESS;
    }

    private function dropTable(CommandInterface $command, string $table, SymfonyStyle $io): void
    {
        $command->dropTable($table)->execute();

        $io->title('Cache table dropped');
        $io->writeln("<fg=green>>>> Table: $table dropped.");
    }
}

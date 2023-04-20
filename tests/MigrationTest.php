<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests;

/**
 * @group Mssql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
abstract class MigrationTest extends TestCase
{
    public function testCreateMigrationNoExistTable(): void
    {
        if ($this->db->getTableSchema('{{%test-table}}') !== null) {
            $this->db->createCommand()->dropTable('{{%test-table}}')->execute();
        }

        $result = $this->createMigration($this->db);

        $this->assertSame(0, $result);

        $output = $this->commandTester->getDisplay(true);

        $this->assertStringContainsString('Creating cache table migration', $output);
        $this->assertStringContainsString('>>> Table: {{%test-table}} created.', $output);
        $this->assertStringContainsString(' [OK] Migration created successfully.', $output);
    }

    public function testCreateMigrationExistTable(): void
    {
        $this->createMigration($this->db);

        $result = $this->createMigration($this->db);

        $this->assertSame(0, $result);

        $output = $this->commandTester->getDisplay(true);

        $this->assertStringContainsString('Checking if table exists.', $output);
        $this->assertStringContainsString('[OK] Table: {{%test-table}} already exists.', $output);
    }

    public function testCreateMigrationWithForce(): void
    {
        $this->createMigration($this->db);

        $result = $this->createMigration($this->db, true);

        $this->assertSame(0, $result);

        $output = $this->commandTester->getDisplay(true);

        $this->assertStringContainsString('Cache table dropped', $output);
        $this->assertStringContainsString('>>> Table: {{%test-table}} dropped.', $output);
        $this->assertStringContainsString('Creating cache table migration', $output);
        $this->assertStringContainsString('>>> Table: {{%test-table}} created.', $output);
        $this->assertStringContainsString('[OK] Migration created successfully.', $output);
    }
}

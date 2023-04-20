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

    public function testVerifyTableStructure(): void
    {
        $this->createMigration($this->db);

        $table = $this->db->getTableSchema('{{%test-table}}');

        $this->assertNotNull($table);

        $this->assertSame('test-table', $table->getName());
        $this->assertSame(['id'], $table->getPrimaryKey());
        $this->assertSame('string', $table->getColumn('id')->getType());
        $this->assertSame(128, $table->getColumn('id')->getSize());
        $this->assertSame('binary', $table->getColumn('data')->getType());
        $this->assertSame('integer', $table->getColumn('expire')->getType());
    }
}

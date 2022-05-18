<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Migration;

use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Yii\Db\Migration\Informer\MigrationInformerInterface;
use Yiisoft\Yii\Db\Migration\MigrationBuilder;
use Yiisoft\Yii\Db\Migration\RevertibleMigrationInterface;

/**
 * Creates cache table.
 */
final class M202101140204CreateCache implements RevertibleMigrationInterface
{
    private MigrationInformerInterface $migrationInformer;

    /**
     * @var DbCache An instance for creating a cache table.
     */
    private DbCache $cache;

    public function __construct(DbCache $cache, MigrationInformerInterface $migrationInformer)
    {
        $this->cache = $cache;
        $this->migrationInformer = $migrationInformer;
    }

    public function up(MigrationBuilder $b): void
    {
        $builder = new MigrationBuilder($this->cache->getDb(), $this->migrationInformer);

        $builder->createTable($this->cache->getTable(), [
            'id' => $builder
                ->string(128)
                ->notNull(),
            'expire' => $builder->integer(),
            'data' => $builder->binary(),
            'PRIMARY KEY ([[id]])',
        ]);
    }

    public function down(MigrationBuilder $b): void
    {
        $builder = new MigrationBuilder($this->cache->getDb(), $this->migrationInformer);
        $builder->dropTable($this->cache->getTable());
    }
}

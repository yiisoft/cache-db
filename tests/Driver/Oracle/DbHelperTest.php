<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Oracle;

use Yiisoft\Cache\Db\Tests\Common\AbstractDbHelperTest;
use Yiisoft\Cache\Db\Tests\Support\OracleHelper;

/**
 * @group Oracle
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbHelperTest extends AbstractDbHelperTest
{
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new OracleHelper())->createConnection();

        parent::setUp();
    }
}

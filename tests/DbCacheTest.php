<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests;

use ArrayIterator;
use DateInterval;
use IteratorAggregate;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use ReflectionObject;
use stdClass;
use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Cache\Db\CacheException;
use Yiisoft\Cache\Db\Migration\M202101140204CreateCache;
use Yiisoft\Db\Command\Command;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Yii\Db\Migration\Informer\MigrationInformerInterface;
use Yiisoft\Yii\Db\Migration\MigrationBuilder;

use function array_keys;
use function array_map;
use function is_array;
use function is_object;

final class DbCacheTest extends TestCase
{
    private DbCache $cache;

    public function setUp(): void
    {
        parent::setUp();

        $this->cache = $this->getContainer()->get(DbCache::class);

        $migration = new M202101140204CreateCache(
            $this->cache,
            $this->getContainer()->get(MigrationInformerInterface::class),
        );

        $migration->up($this->getContainer()->get(MigrationBuilder::class));
    }

    public function testGetters(): void
    {
        $this->assertSame('test-table', $this->cache->getTable());
        $this->assertSame($this->getContainer()->get(ConnectionInterface::class), $this->cache->getDb());
    }

    public function dataProvider(): array
    {
        $object = new stdClass();
        $object->test_field = 'test_value';

        return [
            'integer' => ['test_integer', 1],
            'double' => ['test_double', 1.1],
            'string' => ['test_string', 'a'],
            'boolean_true' => ['test_boolean_true', true],
            'boolean_false' => ['test_boolean_false', false],
            'object' => ['test_object', $object],
            'array' => ['test_array', ['test_key' => 'test_value']],
            'null' => ['test_null', null],
            'supported_key_characters' => ['AZaz09_.', 'b'],
            '64_characters_key_max' => ['bVGEIeslJXtDPrtK.hgo6HL25_.1BGmzo4VA25YKHveHh7v9tUP8r5BNCyLhx4zy', 'c'],
            'string_with_number_key' => ['111', 11],
            'string_with_number_key_1' => ['022', 22],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @throws InvalidArgumentException
     */
    public function testSet($key, $value): void
    {
        for ($i = 0; $i < 2; $i++) {
            $this->assertTrue($this->cache->set($key, $value));
        }
    }

    /**
     * @dataProvider dataProvider
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @throws InvalidArgumentException
     */
    public function testGet($key, $value): void
    {
        $this->cache->set($key, $value);
        $valueFromCache = $this->cache->get($key, 'default');

        $this->assertSameExceptObject($value, $valueFromCache);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @throws InvalidArgumentException
     */
    public function testValueInCacheCannotBeChanged($key, $value): void
    {
        $this->cache->set($key, $value);
        $valueFromCache = $this->cache->get($key, 'default');

        $this->assertSameExceptObject($value, $valueFromCache);

        if (is_object($value)) {
            $originalValue = clone $value;
            $valueFromCache->test_field = 'changed';
            $value->test_field = 'changed';
            $valueFromCacheNew = $this->cache->get($key, 'default');
            $this->assertSameExceptObject($originalValue, $valueFromCacheNew);
        }
    }

    /**
     * @dataProvider dataProvider
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @throws InvalidArgumentException
     */
    public function testHas($key, $value): void
    {
        $this->cache->set($key, $value);

        $this->assertTrue($this->cache->has($key));
        // check whether exists affects the value
        $this->assertSameExceptObject($value, $this->cache->get($key));

        $this->assertTrue($this->cache->has($key));
        $this->assertFalse($this->cache->has('not_exists'));
    }

    public function testGetNonExistent(): void
    {
        $this->assertNull($this->cache->get('non_existent_key'));
    }

    /**
     * @dataProvider dataProvider
     *
     * @param $key
     * @param $value
     *
     * @throws InvalidArgumentException
     */
    public function testDelete($key, $value): void
    {
        $this->cache->set($key, $value);

        $this->assertSameExceptObject($value, $this->cache->get($key));
        $this->assertTrue($this->cache->delete($key));
        $this->assertNull($this->cache->get($key));
    }

    /**
     * @dataProvider dataProvider
     *
     * @param $key
     * @param $value
     *
     * @throws InvalidArgumentException
     */
    public function testClear($key, $value): void
    {
        foreach ($this->dataProvider() as $datum) {
            $this->cache->set($datum[0], $datum[1]);
        }

        $this->assertTrue($this->cache->clear());
        $this->assertNull($this->cache->get($key));
    }

    /**
     * @return array testing multiSet with and without expiry
     */
    public function dataProviderSetMultiple(): array
    {
        return [
            [null],
            [2],
        ];
    }

    /**
     * @dataProvider dataProviderSetMultiple
     *
     * @param int|null $ttl
     *
     * @throws InvalidArgumentException
     */
    public function testSetMultiple(?int $ttl): void
    {
        $data = $this->getDataProviderData();
        $this->cache->setMultiple($data, $ttl);

        foreach ($data as $key => $value) {
            $this->assertSameExceptObject($value, $this->cache->get((string) $key));
        }
    }

    public function testGetMultiple(): void
    {
        $data = $this->getDataProviderData();
        $keys = array_map('strval', array_keys($data));
        $this->cache->setMultiple($data);

        $this->assertSameExceptObject($data, $this->cache->getMultiple($keys));
    }

    public function testDeleteMultiple(): void
    {
        $data = $this->getDataProviderData();
        $keys = array_map('strval', array_keys($data));
        $this->cache->setMultiple($data);

        $this->assertSameExceptObject($data, $this->cache->getMultiple($keys));

        $this->cache->deleteMultiple($keys);
        $emptyData = array_map(static fn () => null, $data);

        $this->assertSameExceptObject($emptyData, $this->cache->getMultiple($keys));
    }

    public function testZeroAndNegativeTtl(): void
    {
        $this->cache->setMultiple(['a' => 1, 'b' => 2]);

        $this->assertTrue($this->cache->has('a'));
        $this->assertTrue($this->cache->has('b'));

        $this->cache->set('a', 11, -1);
        $this->assertFalse($this->cache->has('a'));

        $this->cache->set('b', 22, 0);
        $this->assertFalse($this->cache->has('b'));
    }

    public function iterableProvider(): array
    {
        return [
            'array' => [
                ['a' => 1, 'b' => 2,],
                ['a' => 1, 'b' => 2,],
            ],
            'ArrayIterator' => [
                ['a' => 1, 'b' => 2,],
                new ArrayIterator(['a' => 1, 'b' => 2,]),
            ],
            'IteratorAggregate' => [
                ['a' => 1, 'b' => 2,],
                new class () implements IteratorAggregate {
                    public function getIterator()
                    {
                        return new ArrayIterator(['a' => 1, 'b' => 2,]);
                    }
                },
            ],
            'generator' => [
                ['a' => 1, 'b' => 2,],
                (static function () {
                    yield 'a' => 1;
                    yield 'b' => 2;
                })(),
            ],
        ];
    }

    /**
     * @dataProvider iterableProvider
     *
     * @param array $array
     * @param iterable $iterable
     *
     * @throws InvalidArgumentException
     */
    public function testValuesAsIterable(array $array, iterable $iterable): void
    {
        $this->cache->setMultiple($iterable);

        $this->assertSameExceptObject($array, $this->cache->getMultiple(array_keys($array)));
    }

    public function testSetWithDateIntervalTtl(): void
    {
        $this->cache->set('a', 1, new DateInterval('PT1H'));
        $this->assertSameExceptObject(1, $this->cache->get('a'));

        $this->cache->setMultiple(['b' => 2]);
        $this->assertSameExceptObject(['b' => 2], $this->cache->getMultiple(['b']));
    }

    public function testDeleteForCacheItemNotExist(): void
    {
        $this->assertNull($this->cache->get('key'));
        $this->assertTrue($this->cache->delete('key'));
        $this->assertNull($this->cache->get('key'));
    }

    public function invalidKeyProvider(): array
    {
        return [
            'int' => [1],
            'float' => [1.1],
            'null' => [null],
            'bool' => [true],
            'object' => [new stdClass()],
            'callable' => [fn () => 'key'],
            'psr-reserved' => ['{}()/\@:'],
            'empty-string' => [''],
        ];
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testGetThrowExceptionForInvalidKey($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->cache->get($key);
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testSetThrowExceptionForInvalidKey($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->cache->set($key, 'value');
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testDeleteThrowExceptionForInvalidKey($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->cache->delete($key);
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testGetMultipleThrowExceptionForInvalidKeys($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->cache->getMultiple([$key]);
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testGetMultipleThrowExceptionForInvalidKeysNotIterable($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->cache->getMultiple($key);
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testSetMultipleThrowExceptionForInvalidKeysNotIterable($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->cache->setMultiple($key);
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testDeleteMultipleThrowExceptionForInvalidKeys($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->cache->deleteMultiple([$key]);
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testDeleteMultipleThrowExceptionForInvalidKeysNotIterable($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->cache->deleteMultiple($key);
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testHasThrowExceptionForInvalidKey($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->cache->has($key);
    }

    public function testSetThrowExceptionForFailExecuteCommand(): void
    {
        $cache = $this->createDbCacheWithFailConnection();
        $this->expectException(CacheException::class);
        $cache->set('key', 'value');
    }

    public function testDeleteThrowExceptionForFailExecuteCommand(): void
    {
        $cache = $this->createDbCacheWithFailConnection();
        $this->expectException(CacheException::class);
        $cache->delete('key');
    }

    public function testClearThrowExceptionForFailExecuteCommand(): void
    {
        $cache = $this->createDbCacheWithFailConnection();
        $this->expectException(CacheException::class);
        $cache->clear();
    }

    public function testSetMultipleThrowExceptionForFailExecuteCommand(): void
    {
        $cache = $this->createDbCacheWithFailConnection();
        $this->expectException(CacheException::class);
        $cache->setMultiple(['key-1' => 'value-1', 'key-2' => 'value-2']);
    }

    public function testDeleteMultipleThrowExceptionForFailExecuteCommand(): void
    {
        $cache = $this->createDbCacheWithFailConnection();
        $this->expectException(CacheException::class);
        $cache->deleteMultiple(['key-1', 'key-2']);
    }

    private function createDbCacheWithFailConnection(): DbCache
    {
        $command = $this->getMockBuilder(Command::class)
            ->onlyMethods(['execute'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass()
        ;
        $command->method('execute')->willThrowException(new Exception('Some error.'));

        $db = $this->createMock(ConnectionInterface::class);
        $db->method('createCommand')->willReturn($command);

        return new DbCache($db);
    }

    private function getDataProviderData(): array
    {
        $data = [];

        foreach ($this->dataProvider() as $item) {
            $data[(string) $item[0]] = $item[1];
        }

        return $data;
    }

    private function assertSameExceptObject($expected, $actual): void
    {
        // assert for all types
        $this->assertEquals($expected, $actual);

        // no more asserts for objects
        if (is_object($expected)) {
            return;
        }

        // asserts same for all types except objects and arrays that can contain objects
        if (!is_array($expected)) {
            $this->assertSame($expected, $actual);
            return;
        }

        // assert same for each element of the array except objects
        foreach ($expected as $key => $value) {
            if (!is_object($value)) {
                $this->assertSame($expected[$key], $actual[$key]);
            } else {
                $this->assertEquals($expected[$key], $actual[$key]);
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Common;

use ArrayIterator;
use DateInterval;
use IteratorAggregate;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionObject;
use stdClass;
use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Cache\Db\InvalidArgumentException;
use Yiisoft\Log\Logger;

use function array_keys;
use function array_map;
use function is_array;
use function is_object;

abstract class AbstractDbCacheTest extends TestCase
{
    public function testGetters(): void
    {
        $this->assertSame($this->table, $this->dbCache->getTable());
        $this->assertSame($this->db, $this->dbCache->getDb());
    }

    public static function dataProvider(): array
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

    #[DataProvider('dataProvider')]
    public function testSet(string $key, mixed $value): void
    {
        for ($i = 0; $i < 2; $i++) {
            $this->assertTrue($this->dbCache->set($key, $value));
        }
    }

    #[DataProvider('dataProvider')]
    public function testGet(string $key, mixed $value): void
    {
        $this->dbCache->set($key, $value);
        $this->assertSameExceptObject($value, $this->dbCache->get($key, 'default'));
    }

    #[DataProvider('dataProvider')]
    public function testValueInCacheCannotBeChanged(string $key, mixed $value): void
    {
        $this->dbCache->set($key, $value);

        /** @psalm-var mixed $valueFromCache */
        $valueFromCache = $this->dbCache->get($key, 'default');

        $this->assertSameExceptObject($value, $valueFromCache);

        if (is_object($value) && is_object($valueFromCache)) {
            $originalValue = clone $value;
            $valueFromCache->test_field = 'changed';
            $value->test_field = 'changed';

            /** @psalm-var mixed $valueFromCacheNew */
            $valueFromCacheNew = $this->dbCache->get($key, 'default');

            $this->assertSameExceptObject($originalValue, $valueFromCacheNew);
        }
    }

    #[DataProvider('dataProvider')]
    public function testHas(string $key, mixed $value): void
    {
        $this->dbCache->set($key, $value);

        $this->assertTrue($this->dbCache->has($key));
        // check whether exists affects the value
        $this->assertSameExceptObject($value, $this->dbCache->get($key));

        $this->assertTrue($this->dbCache->has($key));
        $this->assertFalse($this->dbCache->has('not_exists'));
    }

    public function testGetNonExistent(): void
    {
        $this->assertNull($this->dbCache->get('non_existent_key'));
    }

    #[DataProvider('dataProvider')]
    public function testDelete(string $key, mixed $value): void
    {
        $this->dbCache->set($key, $value);

        $this->assertSameExceptObject($value, $this->dbCache->get($key));
        $this->assertTrue($this->dbCache->delete($key));
        $this->assertNull($this->dbCache->get($key));
    }

    #[DataProvider('dataProvider')]
    public function testClear(string $key): void
    {
        /** @psalm-var array $datum */
        foreach (self::dataProvider() as $datum) {
            $this->dbCache->set((string) $datum[0], $datum[1]);
        }

        $this->assertTrue($this->dbCache->clear());
        $this->assertNull($this->dbCache->get($key));
    }

    /**
     * @return array testing multiSet with and without expiry
     */
    public static function dataProviderSetMultiple(): array
    {
        return [
            [null],
            [2],
        ];
    }

    #[DataProvider('dataProviderSetMultiple')]
    public function testSetMultiple(?int $ttl): void
    {
        $data = $this->getDataProviderData();
        $this->dbCache->setMultiple($data, $ttl);

        /** @psalm-var mixed $value */
        foreach ($data as $key => $value) {
            $this->assertSameExceptObject($value, $this->dbCache->get((string) $key));
        }
    }

    public function testGetMultiple(): void
    {
        $data = $this->getDataProviderData();
        $keys = array_map(strval(...), array_keys($data));
        $this->dbCache->setMultiple($data);

        $this->assertSameExceptObject($data, $this->dbCache->getMultiple($keys));
    }

    public function testDeleteMultiple(): void
    {
        $data = $this->getDataProviderData();
        $keys = array_map(strval(...), array_keys($data));
        $this->dbCache->setMultiple($data);

        $this->assertSameExceptObject($data, $this->dbCache->getMultiple($keys));

        $this->dbCache->deleteMultiple($keys);
        $emptyData = array_map(static fn() => null, $data);

        $this->assertSameExceptObject($emptyData, $this->dbCache->getMultiple($keys));
    }

    public function testZeroAndNegativeTtl(): void
    {
        $this->dbCache->setMultiple(['a' => 1, 'b' => 2]);

        $this->assertTrue($this->dbCache->has('a'));
        $this->assertTrue($this->dbCache->has('b'));

        $this->dbCache->set('a', 11, -1);
        $this->assertFalse($this->dbCache->has('a'));

        $this->dbCache->set('b', 22, 0);
        $this->assertFalse($this->dbCache->has('b'));
    }

    /**
     * Data provider for {@see testNormalizeTtl()}
     *
     * @return array test data
     */
    public static function dataProviderNormalizeTtl(): array
    {
        return [
            [123, 123],
            ['123', 123],
            ['', 0], // expired
            [null, null], // infinity
            [0, 0], // expired
            [new DateInterval('PT6H8M'), 6 * 3600 + 8 * 60],
            [new DateInterval('P2Y4D'), 2 * 365 * 24 * 3600 + 4 * 24 * 3600],
        ];
    }

    #[DataProvider('dataProviderNormalizeTtl')]
    public function testNormalizeTtl(mixed $ttl, mixed $expectedResult): void
    {
        $reflection = new ReflectionObject($this->dbCache);
        $method = $reflection->getMethod('normalizeTtl');

        /** @psalm-var mixed $result */
        $result = $method->invokeArgs($this->dbCache, [$ttl]);

        $this->assertSameExceptObject($expectedResult, $result);
    }

    public static function iterableProvider(): array
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
                new class implements IteratorAggregate {
                    public function getIterator(): ArrayIterator
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

    #[DataProvider('iterableProvider')]
    public function testValuesAsIterable(array $array, iterable $iterable): void
    {
        $this->dbCache->setMultiple($iterable);

        $this->assertSameExceptObject($array, $this->dbCache->getMultiple(array_keys($array)));
    }

    public function testSetWithDateIntervalTtl(): void
    {
        $this->dbCache->set('a', 1, new DateInterval('PT1H'));
        $this->assertSameExceptObject(1, $this->dbCache->get('a'));

        $this->dbCache->setMultiple(['b' => 2]);
        $this->assertSameExceptObject(['b' => 2], $this->dbCache->getMultiple(['b']));
    }

    public function testDeleteForCacheItemNotExist(): void
    {
        $this->assertNull($this->dbCache->get('key'));
        $this->assertTrue($this->dbCache->delete('key'));
        $this->assertNull($this->dbCache->get('key'));
    }

    public static function invalidKeyProvider(): array
    {
        return [
            'psr-reserved' => ['{}()/\@:'],
            'empty-string' => [''],
        ];
    }

    #[DataProvider('invalidKeyProvider')]
    public function testGetThrowExceptionForInvalidKey(string $key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dbCache->get($key);
    }

    #[DataProvider('invalidKeyProvider')]
    public function testSetThrowExceptionForInvalidKey(string $key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dbCache->set($key, 'value');
    }

    #[DataProvider('invalidKeyProvider')]
    public function testDeleteThrowExceptionForInvalidKey(string $key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dbCache->delete($key);
    }

    #[DataProvider('invalidKeyProvider')]
    public function testGetMultipleThrowExceptionForInvalidKeys(string $key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dbCache->getMultiple([$key]);
    }

    #[DataProvider('invalidKeyProvider')]
    public function testDeleteMultipleThrowExceptionForInvalidKeys(string $key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dbCache->deleteMultiple([$key]);
    }

    #[DataProvider('invalidKeyProvider')]
    public function testHasThrowExceptionForInvalidKey(string $key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dbCache->has($key);
    }

    public function testSetThrowExceptionForFailExecuteCommand(): void
    {
        $cache = $this->createCacheDbFail();
        $this->assertFalse($cache->set('key', 'value'));

        /** @psalm-var Logger[] $logger */
        $logger = $this->getInaccessibleProperty($this->getLogger(), 'messages');

        /** @psalm-var string $context */
        $context = $this->getInaccessibleProperty($logger[0], 'context');

        $this->assertSame('Yiisoft\\Cache\\Db\\DbCache::set', $context[0]);

        /** @psalm-var string $message */
        $message = $this->getInaccessibleProperty($logger[0], 'message');

        /** @psalm-var string $loggerMessageUpdate */
        $loggerMessageUpdate = $this->getInaccessibleProperty($cache, 'loggerMessageUpdate');

        $this->assertCount(1, $logger);
        $this->assertStringContainsString($loggerMessageUpdate, $message);
        $this->assertStringContainsString('Unable to update cache data: SQLSTATE', $message);
    }

    public function testSetThrowExceptionForFailExecuteCommandWithLoggerCustomMessageUpdate(): void
    {
        $cache = $this->createCacheDbFail();
        $cache->setLoggerMessageUpdate('Custom message update: ');
        $cache->set('key', 'value');

        /** @psalm-var Logger[] $logger */
        $logger = $this->getInaccessibleProperty($this->getLogger(), 'messages');

        /** @psalm-var string $message */
        $message = $this->getInaccessibleProperty($logger[0], 'message');

        $this->assertCount(1, $logger);
        $this->assertStringContainsString('Custom message update: ', $message);
    }

    public function testDeleteThrowExceptionForFailExecuteCommand(): void
    {
        $cache = $this->createCacheDbFail();
        $cache->delete('key');

        /** @psalm-var Logger[] $logger */
        $logger = $this->getInaccessibleProperty($this->getLogger(), 'messages');

        /** @psalm-var string $context */
        $context = $this->getInaccessibleProperty($logger[0], 'context');

        $this->assertSame('Yiisoft\\Cache\\Db\\DbCache::deleteData', $context[0]);

        /** @psalm-var string $message */
        $message = $this->getInaccessibleProperty($logger[0], 'message');

        /** @psalm-var string $loggerMessageDelete */
        $loggerMessageDelete = $this->getInaccessibleProperty($cache, 'loggerMessageDelete');

        $this->assertCount(1, $logger);
        $this->assertStringContainsString($loggerMessageDelete, $message);
        $this->assertStringContainsString('Unable to delete cache data: SQLSTATE[', $message);
    }

    public function testDeleteThrowExceptionForFailExecuteCommandWithLoggerCustomMessageDelete(): void
    {
        $cache = $this->createCacheDbFail();
        $cache->setLoggerMessageDelete('Custom message delete: ');
        $cache->delete('key');

        /** @psalm-var Logger[] $logger */
        $logger = $this->getInaccessibleProperty($this->getLogger(), 'messages');

        /** @psalm-var string $message */
        $message = $this->getInaccessibleProperty($logger[0], 'message');

        $this->assertCount(1, $logger);
        $this->assertStringContainsString('Custom message delete: ', $message);
    }

    public function testClearThrowExceptionForFailExecuteCommand(): void
    {
        $cache = $this->createCacheDbFail();
        $cache->clear();

        /** @psalm-var Logger[] $logger */
        $logger = $this->getInaccessibleProperty($this->getLogger(), 'messages');

        $this->assertCount(1, $logger);
    }

    public function testSetMultipleThrowExceptionForFailExecuteCommand(): void
    {
        $cache = $this->createCacheDbFail();
        $this->assertFalse($cache->setMultiple(['key-1' => 'value-1', 'key-2' => 'value-2']));

        /** @psalm-var Logger[] $logger */
        $logger = $this->getInaccessibleProperty($this->getLogger(), 'messages');

        $this->assertCount(2, $logger);
    }

    public function testGetDataEmptyKey(): void
    {
        $getData = $this->invokeMethod($this->dbCache, 'getData', ['', [], 'all']);
        $this->assertFalse($getData);

        /** @psalm-var mixed $getData */
        $getData = $this->invokeMethod($this->dbCache, 'getData', [[], [], 'all']);
        $this->assertSame([], $getData);
    }

    public function testDeleteDataEmptyKey(): void
    {
        $getData = $this->invokeMethod($this->dbCache, 'deleteData', ['', [], 'all']);
        $this->assertFalse($getData);
    }

    public function testDeleteDataTrueKey(): void
    {
        $getData = $this->invokeMethod($this->dbCache, 'deleteData', [true, [], 'all']);
        $this->assertTrue($getData);
    }

    private function getDataProviderData(): array
    {
        $data = [];

        /** @psalm-var array $item */
        foreach (self::dataProvider() as $item) {
            /** @psalm-var mixed */
            $data[(string) $item[0]] = $item[1];
        }

        return $data;
    }

    private function assertSameExceptObject(mixed $expected, mixed $actual): void
    {
        // assert for all types
        $this->assertEquals($expected, $actual);

        // no more asserts for objects
        if (is_object($expected)) {
            return;
        }

        // asserts the same for all types except objects and arrays that can contain objects
        if (!is_array($expected)) {
            $this->assertSame($expected, $actual);
            return;
        }

        /**
         * assert the same for each element of the array except objects
         *
         * @psalm-var mixed $value
         */
        foreach ($expected as $key => $value) {
            if (!is_object($value)) {
                $this->assertSame($value, $actual[$key]);
            } else {
                $this->assertEquals($value, $actual[$key]);
            }
        }
    }

    private function createCacheDbFail(): DbCache
    {
        $logger = $this->getLogger();
        $logger->flush();
        $cache = new DbCache($this->db, 'noExist');
        $cache->setLogger($logger);

        return $cache;
    }
}

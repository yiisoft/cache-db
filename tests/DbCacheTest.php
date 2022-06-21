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
use Yiisoft\Db\Exception\Exception;

use function array_keys;
use function array_map;
use function is_array;
use function is_object;

abstract class DbCacheTest extends TestCase
{
    public function testGetters(): void
    {
        $this->assertSame('test-table', $this->dbCache->getTable());
        $this->assertSame($this->db, $this->dbCache->getDb());
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
            $this->assertTrue($this->dbCache->set($key, $value));
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
        $this->dbCache->set($key, $value);
        $valueFromCache = $this->dbCache->get($key, 'default');

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
        $this->dbCache->set($key, $value);
        $valueFromCache = $this->dbCache->get($key, 'default');

        $this->assertSameExceptObject($value, $valueFromCache);

        if (is_object($value)) {
            $originalValue = clone $value;
            $valueFromCache->test_field = 'changed';
            $value->test_field = 'changed';
            $valueFromCacheNew = $this->dbCache->get($key, 'default');
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
        $this->dbCache->set($key, $value);

        $this->assertSameExceptObject($value, $this->dbCache->get($key));
        $this->assertTrue($this->dbCache->delete($key));
        $this->assertNull($this->dbCache->get($key));
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
            $this->dbCache->set($datum[0], $datum[1]);
        }

        $this->assertTrue($this->dbCache->clear());
        $this->assertNull($this->dbCache->get($key));
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
        $this->dbCache->setMultiple($data, $ttl);

        foreach ($data as $key => $value) {
            $this->assertSameExceptObject($value, $this->dbCache->get((string) $key));
        }
    }

    public function testGetMultiple(): void
    {
        $data = $this->getDataProviderData();
        $keys = array_map('strval', array_keys($data));
        $this->dbCache->setMultiple($data);

        $this->assertSameExceptObject($data, $this->dbCache->getMultiple($keys));
    }

    public function testDeleteMultiple(): void
    {
        $data = $this->getDataProviderData();
        $keys = array_map('strval', array_keys($data));
        $this->dbCache->setMultiple($data);

        $this->assertSameExceptObject($data, $this->dbCache->getMultiple($keys));

        $this->dbCache->deleteMultiple($keys);
        $emptyData = array_map(static fn () => null, $data);

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
     * @throws Exception
     *
     * @return array test data
     */
    public function dataProviderNormalizeTtl(): array
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

    /**
     * @dataProvider dataProviderNormalizeTtl
     *
     * @param mixed $ttl
     * @param mixed $expectedResult
     *
     * @throws ReflectionException
     */
    public function testNormalizeTtl($ttl, $expectedResult): void
    {
        $reflection = new ReflectionObject($this->dbCache);
        $method = $reflection->getMethod('normalizeTtl');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->dbCache, [$ttl]);
        $method->setAccessible(false);

        $this->assertSameExceptObject($expectedResult, $result);
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
        $this->dbCache->get($key);
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testSetThrowExceptionForInvalidKey($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dbCache->set($key, 'value');
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testDeleteThrowExceptionForInvalidKey($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dbCache->delete($key);
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testGetMultipleThrowExceptionForInvalidKeys($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dbCache->getMultiple([$key]);
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testGetMultipleThrowExceptionForInvalidKeysNotIterable($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dbCache->getMultiple($key);
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testSetMultipleThrowExceptionForInvalidKeysNotIterable($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dbCache->setMultiple($key);
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testDeleteMultipleThrowExceptionForInvalidKeys($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dbCache->deleteMultiple([$key]);
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testDeleteMultipleThrowExceptionForInvalidKeysNotIterable($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dbCache->deleteMultiple($key);
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testHasThrowExceptionForInvalidKey($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dbCache->has($key);
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

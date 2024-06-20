<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests;

use PHPUnit\Framework\TestCase;
use stdClass;
use Yiisoft\Cache\Db\PhpSerializer;

use function pi;

use const PHP_INT_MAX;

final class PhpSerializerTest extends TestCase
{
    public static function serializeDataProvider(): array
    {
        $object = new stdClass();
        $object->foo = 'bar';
        $object->bar = 'foo';

        return [
            [
                true,
            ],
            [
                false,
            ],
            [
                null,
            ],
            [
                PHP_INT_MAX,
            ],
            [
                pi(),
            ],
            [
                'string',
            ],
            [
                [
                    'key' => 'value',
                    'foo' => 'bar',
                    'true' => true,
                    'false' => false,
                    'array' => (array) $object,
                    'int' => 8_000,
                ],
            ],
            [
                $object,
            ],
        ];
    }

    /**
     * @dataProvider serializeDataProvider
     * @param mixed $data
     * @return void
     */
    public function testSerialize(mixed $data): void
    {
        $serializer = new PhpSerializer();
        $result = $serializer->serialize($data);

        if (is_object($data)) {
            self::assertEquals($data, $serializer->unserialize($result));
        } else {
            self::assertSame($data, $serializer->unserialize($result));
        }
    }
}

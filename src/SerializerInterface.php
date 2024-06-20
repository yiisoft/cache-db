<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db;

interface SerializerInterface
{
    public function serialize(mixed $value): string;

    public function unserialize(string $data): mixed;
}

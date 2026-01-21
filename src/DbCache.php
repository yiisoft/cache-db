<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db;

use DateInterval;
use DateTime;
use PDO;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;
use Traversable;
use Yiisoft\Cache\Serializer\PhpSerializer;
use Yiisoft\Cache\Serializer\SerializerInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Expression\Value\Param;
use Yiisoft\Db\Query\Query;

use function array_fill_keys;
use function is_resource;
use function is_string;
use function iterator_to_array;
use function random_int;
use function strpbrk;
use function time;

/**
 * DbCache stores cache data in a database table.
 *
 * Use {@see DbSchemaManager::ensureTable()} to initialize database schema.
 */
final class DbCache implements CacheInterface
{
    use LoggerAwareTrait;

    private string $loggerMessageDelete = 'Unable to delete cache data: ';
    private string $loggerMessageUpdate = 'Unable to update cache data: ';

    /**
     * @param ConnectionInterface $db The database connection instance.
     * @param string $table The name of the database table to store the cache data. Defaults to "cache".
     * @param int $gcProbability The probability (parts per million) that garbage collection (GC) should
     * be performed when storing a piece of data in the cache. Defaults to 100, meaning 0.01% chance.
     * This number should be between 0 and 1000000. A value 0 meaning no GC will be performed at all.
     */
    public function __construct(private ConnectionInterface $db, private string $table = '{{%yii_cache}}', public int $gcProbability = 100, private readonly ?SerializerInterface $serializer = new PhpSerializer()) {}

    /**
     * Gets an instance of a database connection.
     */
    public function getDb(): ConnectionInterface
    {
        return $this->db;
    }

    /**
     * Gets the name of the database table to store the cache data.
     */
    public function getTable(): string
    {
        return $this->table;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->validateKey($key);

        /** @var bool|float|int|string|null $value */
        $value = $this->getData($key, ['data'], 'scalar');

        return $value === false ? $default : $this->serializer->unserialize((string) $value);
    }

    /**
     * @param string $key The cache data ID.
     * @param mixed $value The cache data value.
     * @param DateInterval|int|string|null $ttl The cache data TTL.
     *
     * @throws InvalidArgumentException
     */
    public function set(string $key, mixed $value, DateInterval|int|string|null $ttl = null): bool
    {
        $this->validateKey($key);
        $ttl = $this->normalizeTtl($ttl);

        if ($this->isExpiredTtl($ttl)) {
            return $this->delete($key);
        }

        try {
            /**
             * @psalm-suppress MixedArgumentTypeCoercion `$this->buildDataRow()` next returns `array<string, mixed>`
             */
            $this->db
                ->createCommand()
                ->upsert($this->table, $this->buildDataRow($key, $ttl, $value, true))
                ->execute();

            $this->gc();

            return true;
        } catch (Throwable $e) {
            $this->logger?->log(LogLevel::ERROR, $this->loggerMessageUpdate . $e->getMessage(), [__METHOD__]);

            return false;
        }
    }

    public function delete(string $key): bool
    {
        $this->validateKey($key);
        $this->deleteData($key);

        return true;
    }

    public function clear(): bool
    {
        $this->deleteData(true);

        return true;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        /** @psalm-var array<array-key,array-key> $keys */
        $keys = $this->iterableToArray($keys);
        $this->validateKeys($keys);
        $values = array_fill_keys($keys, $default);

        /** @psalm-var array<string, string|resource> */
        foreach ($this->getData($keys, ['id', 'data'], 'all') as $value) {
            if (is_resource($value['data']) && get_resource_type($value['data']) === 'stream') {
                $value['data'] = stream_get_contents($value['data']);
            }

            /** @psalm-var string */
            $values[$value['id']] = $this->serializer->unserialize((string) $value['data']);
        }

        /** @psalm-var iterable<string,mixed> */
        return $values;
    }

    /**
     * @param iterable $values A list of key => value pairs for a multiple-set operation.
     * @param DateInterval|int|string|null $ttl The cache data TTL.
     *
     * @throws InvalidArgumentException
     */
    public function setMultiple(iterable $values, DateInterval|int|string|null $ttl = null): bool
    {
        $ttl = $this->normalizeTtl($ttl);
        $values = $this->iterableToArray($values);
        $rows = $keys = [];

        foreach ($values as $key => $value) {
            $key = (string) $key;
            $this->validateKey($key);
            $rows[] = $this->buildDataRow($key, $ttl, $value, false);
            $keys[] = $key;
        }

        try {
            $this->deleteData($keys);

            if (!empty($rows) && !$this->isExpiredTtl($ttl)) {
                $this->db
                    ->createCommand()
                    ->insertBatch($this->table, $rows, ['id', 'expire', 'data'])
                    ->execute();
            }

            $this->gc();

            return true;
        } catch (Throwable $e) {
            $message = $e->getMessage();
            $this->logger?->log(LogLevel::ERROR, "$this->loggerMessageUpdate$message", [__METHOD__]);

            return false;
        }
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $keys = $this->iterableToArray($keys);
        $this->validateKeys($keys);

        return $this->deleteData($keys);
    }

    public function has(string $key): bool
    {
        $this->validateKey($key);

        return (bool) $this->getData($key, ['id'], 'exists');
    }

    /**
     * Set logger message for delete operation failure.
     *
     * @param string $value The message.
     */
    public function setLoggerMessageDelete(string $value): void
    {
        $this->loggerMessageDelete = $value;
    }

    /**
     * Set logger message for update operation failure.
     *
     * @param string $value The message.
     */
    public function setLoggerMessageUpdate(string $value): void
    {
        $this->loggerMessageUpdate = $value;
    }

    /**
     * Gets the cache data from the database.
     *
     * @param array|string $id One or more IDs for deleting data.
     * @param string[] $fields Selectable fields.
     * @param string $method Method of the returned data ("all", "scalar", "exists").
     *
     * @return mixed The cache data.
     */
    private function getData(array|string $id, array $fields, string $method): mixed
    {
        if (empty($id)) {
            return is_string($id) ? false : [];
        }

        return (new Query($this->db))
            ->from($this->table)
            ->select($fields)
            ->where(['id' => $id])
            ->andWhere(['OR', ['expire' => null], ['>', 'expire', time()]])
            ->{$method}();
    }

    /**
     * Deletes cache data from the database.
     *
     * @param array|bool|string $id One or more IDs for deleting data.
     *
     * If `true`, the all cache data will be deleted from the database.
     */
    private function deleteData(array|string|bool $id): bool
    {
        if (empty($id)) {
            return false;
        }

        try {
            $condition = $id === true ? '' : ['id' => $id];
            $this->db
                ->createCommand()
                ->delete($this->table, $condition)
                ->execute();

            return true;
        } catch (Throwable $e) {
            $message = $e->getMessage();
            $this->logger?->log(LogLevel::ERROR, "$this->loggerMessageDelete$message", [__METHOD__]);

            return false;
        }
    }

    /**
     * Builds a row of cache data to insert into the database.
     *
     * @param string $id The cache data ID.
     * @param int|null $ttl The cache data TTL.
     * @param mixed $value The cache data value.
     * @param bool $associative If `true`, an associative array is returned. If `false`, a list is returned.
     *
     * @return array The row of cache data to insert into the database.
     */
    private function buildDataRow(string $id, ?int $ttl, mixed $value, bool $associative): array
    {
        $expire = $this->isInfinityTtl($ttl) ? null : ((int) $ttl + time());
        $data = new Param($this->serializer->serialize($value), PDO::PARAM_LOB);

        if ($associative) {
            return ['id' => $id, 'expire' => $expire, 'data' => $data];
        }

        return [$id, $expire, $data];
    }

    /**
     * Removes the expired data values.
     *
     * @throws Throwable
     */
    private function gc(): void
    {
        if (random_int(0, 1_000_000) < $this->gcProbability) {
            $this->db
                ->createCommand()
                ->delete($this->table, ['AND', ['>', 'expire', 0], ['<', 'expire', time()]])
                ->execute();
        }
    }

    /**
     * Normalizes cache TTL handling `null` value, strings and {@see DateInterval} objects.
     *
     * @param DateInterval|int|string|null $ttl The raw TTL.
     *
     * @return int|null TTL value as UNIX timestamp.
     */
    private function normalizeTtl(DateInterval|int|string|null $ttl): ?int
    {
        if ($ttl === null) {
            return null;
        }

        if ($ttl instanceof DateInterval) {
            return (new DateTime('@0'))
                ->add($ttl)
                ->getTimestamp();
        }

        return (int) $ttl;
    }

    private function isExpiredTtl(?int $ttl): bool
    {
        return $ttl !== null && $ttl <= 0;
    }

    private function isInfinityTtl(?int $ttl): bool
    {
        return $ttl === null;
    }

    /**
     * Converts iterable to array. If provided value isn't iterable, it throws an InvalidArgumentException.
     */
    private function iterableToArray(iterable $iterable): array
    {
        return $iterable instanceof Traversable ? iterator_to_array($iterable) : $iterable;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function validateKey(mixed $key): void
    {
        if (!is_string($key) || $key === '' || strpbrk($key, '{}()/\@:')) {
            throw new \Yiisoft\Cache\Db\InvalidArgumentException('Invalid key value.');
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function validateKeys(array $keys): void
    {
        foreach ($keys as $key) {
            $this->validateKey($key);
        }
    }
}

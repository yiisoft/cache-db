<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db;

use DateInterval;
use DateTime;
use InvalidArgumentException;
use PDO;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Psr\SimpleCache\CacheInterface;
use Throwable;
use Traversable;
use Yiisoft\Db\Command\Param;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Query\Query;

use function array_fill_keys;
use function gettype;
use function is_iterable;
use function is_string;
use function iterator_to_array;
use function random_int;
use function serialize;
use function strpbrk;
use function time;
use function unserialize;

/**
 * DbCache stores cache data in a database table.
 *
 * Database schema could be initialized by applying migration:
 *
 * {@see \Yiisoft\Cache\Db\Migration\M202101140204CreateCache}.
 */
final class DbCache implements CacheInterface
{
    use LoggerAwareTrait;

    /**
     * @var ConnectionInterface The database connection instance.
     */
    private ConnectionInterface $db;

    private string $loggerMessageDelete = 'Unable to delete cache data: ';
    private string $loggerMessageUpdate = 'Unable to update cache data: ';

    /**
     * @var string The name of the database table to store the cache data. Defaults to "cache".
     */
    private string $table;

    /**
     * @var int The probability (parts per million) that garbage collection (GC) should be performed
     * when storing a piece of data in the cache. Defaults to 100, meaning 0.01% chance.
     * This number should be between 0 and 1000000. A value 0 meaning no GC will be performed at all.
     */
    public int $gcProbability;

    /**
     * @param ConnectionInterface $db The database connection instance.
     * @param string $table The name of the database table to store the cache data. Defaults to "cache".
     * @param int $gcProbability The probability (parts per million) that garbage collection (GC) should
     * be performed when storing a piece of data in the cache. Defaults to 100, meaning 0.01% chance.
     * This number should be between 0 and 1000000. A value 0 meaning no GC will be performed at all.
     */
    public function __construct(ConnectionInterface $db, string $table = '{{%cache}}', int $gcProbability = 100)
    {
        $this->db = $db;
        $this->table = $table;
        $this->gcProbability = $gcProbability;
    }

    /**
     * Gets an instance of a database connection.
     *
     * @return ConnectionInterface
     */
    public function getDb(): ConnectionInterface
    {
        return $this->db;
    }

    /**
     * Gets the name of the database table to store the cache data.
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    public function get($key, $default = null): mixed
    {
        $this->validateKey($key);
        $value = $this->getData($key, ['data'], 'scalar');

        return $value === false ? $default : unserialize($value);
    }

    /**
     * @param string $key The cache data ID.
     * @param mixed $value The cache data value.
     * @param DateInterval|int|string|null $ttl The cache data TTL.
     */
    public function set($key, $value, $ttl = null): bool
    {
        $this->validateKey($key);
        $ttl = $this->normalizeTtl($ttl);

        if ($this->isExpiredTtl($ttl)) {
            return $this->delete($key);
        }

        try {
            $this->db
                ->createCommand()
                ->upsert($this->table, $this->buildDataRow($key, $ttl, $value, true))
                ->noCache()
                ->execute();

            $this->gc();

            return true;
        } catch (Throwable $e) {
            $this->logger?->log(LogLevel::ERROR, $this->loggerMessageUpdate . $e->getMessage(), [__METHOD__]);

            return false;
        }
    }

    public function delete($key): bool
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

    public function getMultiple($keys, $default = null): iterable
    {
        $keys = $this->iterableToArray($keys);
        $this->validateKeys($keys);
        $values = array_fill_keys($keys, $default);

        foreach ($this->getData($keys, ['id', 'data'], 'all') as $value) {
            if (is_resource($value['data']) && get_resource_type($value['data']) === 'stream') {
                $value['data'] = stream_get_contents($value['data']);
            }
            $values[$value['id']] = unserialize($value['data']);
        }

        return $values;
    }

    /**
     * @param iterable $values A list of key => value pairs for a multiple-set operation.
     * @param DateInterval|int|string|null $ttl The cache data TTL.
     */
    public function setMultiple($values, $ttl = null): bool
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
                    ->batchInsert($this->table, ['id', 'expire', 'data'], $rows)
                    ->noCache()
                    ->execute();
            }

            $this->gc();

            return true;
        } catch (Throwable $e) {
            $this->logger?->log(LogLevel::ERROR, $this->loggerMessageUpdate . $e->getMessage(), [__METHOD__]);

            return false;
        }
    }

    public function deleteMultiple($keys): bool
    {
        $keys = $this->iterableToArray($keys);
        $this->validateKeys($keys);
        $this->deleteData($keys);

        return true;
    }

    public function has($key): bool
    {
        $this->validateKey($key);

        return $this->getData($key, ['id'], 'exists');
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
     * @param array $fields Selectable fields.
     * @param string $method Method of the returned data ( "all", "scalar", "exists").
     *
     * @return mixed The cache data.
     */
    private function getData($id, array $fields, string $method)
    {
        if (empty($id)) {
            return is_string($id) ? false : [];
        }

        return (new Query($this->db))
            ->noCache()
            ->from($this->table)
            ->select($fields)
            ->where(['id' => $id])
            ->andWhere(['OR', ['expire' => null], ['>', 'expire', time()]])
            ->{$method}();
    }

    /**
     * Deletes a cache data from the database.
     *
     * @param array|string|true $id One or more IDs for deleting data.
     *
     * If `true`, the all cache data will be deleted from the database.
     */
    private function deleteData($id): void
    {
        if (empty($id)) {
            return;
        }

        try {
            $condition = $id === true ? '' : ['id' => $id];
            $this->db
                ->createCommand()
                ->delete($this->table, $condition)
                ->noCache()
                ->execute();
        } catch (Throwable $e) {
            $this->logger?->log(LogLevel::ERROR, $this->loggerMessageDelete . $e->getMessage(), [__METHOD__]);
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
    private function buildDataRow(string $id, ?int $ttl, $value, bool $associative): array
    {
        $expire = $this->isInfinityTtl($ttl) ? null : ($ttl + time());
        $data = new Param(serialize($value), PDO::PARAM_LOB);

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
        if (random_int(0, 1000000) < $this->gcProbability) {
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
     * @return int TTL value as UNIX timestamp.
     */
    private function normalizeTtl($ttl): ?int
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
     * Converts iterable to array. If provided value is not iterable it throws an InvalidArgumentException.
     *
     * @param mixed $iterable
     *
     * @return array
     */
    private function iterableToArray($iterable): array
    {
        if (!is_iterable($iterable)) {
            throw new InvalidArgumentException('Iterable is expected, got ' . gettype($iterable));
        }

        /** @psalm-suppress RedundantCast */
        return $iterable instanceof Traversable ? iterator_to_array($iterable) : (array) $iterable;
    }

    /**
     * @param mixed $key
     */
    private function validateKey($key): void
    {
        if (!is_string($key) || $key === '' || strpbrk($key, '{}()/\@:')) {
            throw new InvalidArgumentException('Invalid key value.');
        }
    }

    /**
     * @param array $keys
     */
    private function validateKeys(array $keys): void
    {
        foreach ($keys as $key) {
            $this->validateKey($key);
        }
    }
}

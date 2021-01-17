<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db;

use DateInterval;
use DateTime;
use PDO;
use Psr\SimpleCache\CacheInterface;
use Throwable;
use Traversable;
use Yiisoft\Cache\Db\Exception\CacheException;
use Yiisoft\Cache\Db\Exception\InvalidArgumentException;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Pdo\PdoValue;
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
 * {@see \Yiisoft\Cache\Db\Migration\M202101140204CreateCache}.
 */
final class DbCache implements CacheInterface
{
    private const TTL_INFINITY = 0;
    private const TTL_EXPIRED = -1;

    /**
     * @var ConnectionInterface|null The database connection instance.
     */
    private ?ConnectionInterface $db = null;

    /**
     * @var DbFactory Factory for creating a database connection instance.
     * Provides lazy loading of the {@see \Yiisoft\Db\Connection\ConnectionInterface} instance
     * to prevent a circular reference to the connection when building container definitions.
     */
    private DbFactory $factory;

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
     * @param DbFactory $factory Factory for creating a database connection instance.
     * Provides lazy loading of the {@see \Yiisoft\Db\Connection\ConnectionInterface} instance
     * to prevent a circular reference to the connection when building container definitions.
     * @param string $table The name of the database table to store the cache data. Defaults to "cache".
     * @param int $gcProbability The probability (parts per million) that garbage collection (GC) should
     * be performed when storing a piece of data in the cache. Defaults to 100, meaning 0.01% chance.
     * This number should be between 0 and 1000000. A value 0 meaning no GC will be performed at all.
     */
    public function __construct(DbFactory $factory, string $table = '{{%cache}}', int $gcProbability = 100)
    {
        $this->factory = $factory;
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
        if ($this->db === null) {
            $this->db = $this->factory->create();
        }

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

    public function get($key, $default = null)
    {
        $this->validateKey($key);
        $value = $this->getData($key, ['data'], 'scalar');
        return $value === false ? $default : unserialize($value);
    }

    public function set($key, $value, $ttl = null): bool
    {
        $this->validateKey($key);
        $ttl = $this->normalizeTtl($ttl);

        if ($ttl <= self::TTL_EXPIRED) {
            return $this->delete($key);
        }

        try {
            $this->getDb()->createCommand()
                ->upsert($this->table, $this->buildDataRow($key, $ttl, $value, true))
                ->noCache()
                ->execute()
            ;

            $this->gc();
            return true;
        } catch (Throwable $e) {
            throw new CacheException('Unable to update or insert cache data.', 0, $e);
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
            $values[$value['id']] = unserialize($value['data']);
        }

        return $values;
    }

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

            if (!empty($rows) && $ttl > self::TTL_EXPIRED) {
                $this->getDb()->createCommand()
                    ->batchInsert($this->table, ['id', 'expire', 'data'], $rows)
                    ->noCache()
                    ->execute()
                ;
            }

            $this->gc();
            return true;
        } catch (Throwable $e) {
            throw new CacheException('Unable to update or insert cache data.', 0, $e);
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
     * @param array|string $id
     * @param array $fields
     * @param string $method
     *
     * @return mixed
     */
    private function getData($id, array $fields, string $method)
    {
        if (empty($id)) {
            return is_string($id) ? false : [];
        }

        return (new Query($this->getDb()))
            ->noCache()
            ->from($this->table)
            ->select($fields)
            ->where(['id' => $id])
            ->andWhere('([[expire]] = ' . self::TTL_INFINITY . ' OR [[expire]] > ' . time() . ')')
            ->{$method}()
        ;
    }

    /**
     * @param array|string|true $id
     *
     * @throws CacheException
     */
    private function deleteData($id): void
    {
        if (empty($id)) {
            return;
        }

        try {
            $condition = $id === true ? '' : ['id' => $id];
            $this->getDb()->createCommand()->delete($this->table, $condition)->noCache()->execute();
        } catch (Throwable $e) {
            throw new CacheException('Unable to delete cache data.', 0, $e);
        }
    }

    /**
     * @param string $id
     * @param int $ttl
     * @param mixed $value
     * @param bool $associative
     * 
     * @return array
     */
    private function buildDataRow(string $id, int $ttl, $value, bool $associative): array
    {
        $expire = $ttl > 0 ? $ttl + time() : 0;
        $data = new PdoValue(serialize($value), PDO::PARAM_LOB);

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
            $this->getDb()->createCommand()
                ->delete($this->table, '[[expire]] > 0 AND [[expire]] < ' . time())
                ->execute()
            ;
        }
    }

    /**
     * Normalizes cache TTL handling `null` value, strings and {@see DateInterval} objects.
     *
     * @param DateInterval|int|string|null $ttl The raw TTL.
     *
     * @return int TTL value as UNIX timestamp.
     */
    private function normalizeTtl($ttl): int
    {
        if ($ttl === null) {
            return self::TTL_INFINITY;
        }

        if ($ttl instanceof DateInterval) {
            return (new DateTime('@0'))->add($ttl)->getTimestamp();
        }

        $ttl = (int) $ttl;
        return $ttl > 0 ? $ttl : self::TTL_EXPIRED;
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

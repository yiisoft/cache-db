<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db;

use RuntimeException;

final class InvalidArgumentException extends RuntimeException implements \Psr\SimpleCache\InvalidArgumentException {}

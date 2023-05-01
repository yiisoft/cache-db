/**
 * Database schema required by yiisoft/cache db for MySQL.
 */
CREATE TABLE `cache` (
    `id` VARCHAR(128) NOT NULL,
    `data` BLOB,
    `expire` INT,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
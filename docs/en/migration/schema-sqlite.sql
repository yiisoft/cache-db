/**
 * Database schema required by yiisoft/cache db for SQLite.
 */
CREATE TABLE "cache" (
    id VARCHAR(128) NOT NULL,
    data BLOB,
    expire INTEGER,
    PRIMARY KEY (id)
);

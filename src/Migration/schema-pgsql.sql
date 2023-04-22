/**
 * Database schema required by yiisoft/cache db for PostgreSQL.
 */
DROP TABLE IF EXISTS "cache" CASCADE;

CREATE TABLE "cache" (
    id VARCHAR(128) NOT NULL,
    data BYTEA,
    expire INTEGER,
    PRIMARY KEY (id)
);

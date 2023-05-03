/**
 * Database schema required by yiisoft/cache db for Oracle.
 */
CREATE TABLE "cache" (
    "id" VARCHAR2(128) NOT NULL,
    "data" BLOB,
    "expire" INTEGER,
    CONSTRAINT "PK_cache" PRIMARY KEY ("id") ENABLE
);

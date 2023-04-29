/**
 * Database schema required by yiisoft/cache db for Oracle.
 */
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "cache"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--

/* STATEMENTS */

CREATE TABLE "cache" (
    "id" VARCHAR2(128) NOT NULL,
    "data" BLOB,
    "expire" INTEGER,
    CONSTRAINT "PK_cache" PRIMARY KEY ("id") ENABLE
);

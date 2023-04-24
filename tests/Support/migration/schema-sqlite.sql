DROP TABLE IF EXISTS "cache";

CREATE TABLE "cache" (
    id VARCHAR(128) NOT NULL,
    data BLOB,
    expire INTEGER,
    PRIMARY KEY (id)
);

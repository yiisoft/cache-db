CREATE TABLE "yii_cache" (
	"id" VARCHAR2(128) NOT NULL,
	"data" BLOB,
	"expire" NUMBER(10),
	CONSTRAINT "PK_yii_cache" PRIMARY KEY ("id")
);

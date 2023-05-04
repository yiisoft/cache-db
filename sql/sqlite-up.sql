CREATE TABLE `yii_cache` (
	`id` varchar(128) NOT NULL,
	`data` blob,
	`expire` integer,
	CONSTRAINT `PK_yii_cache` PRIMARY KEY (`id`)
);

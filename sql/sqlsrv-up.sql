CREATE TABLE [yii_cache] (
	[id] nvarchar(128) NOT NULL,
	[data] varbinary(max),
	[expire] int,
	CONSTRAINT [PK_yii_cache] PRIMARY KEY ([id])
);

/**
 * Database schema required by yiisoft/cache db for MSSQL.
 */
IF OBJECT_ID('[dbo].[cache]', 'U') IS NOT NULL DROP TABLE [dbo].[cache];

CREATE TABLE [dbo].[cache] (
    [id] NVARCHAR(128) NOT NULL,
    [data] VARBINARY(MAX) NULL,
    [expire] INT NULL,
    CONSTRAINT [PK_cache] PRIMARY KEY ([id])
);

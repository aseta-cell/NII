-- Only for a database created from the OLD database.sql
-- (a fresh import of database.sql already has these columns).

ALTER TABLE users ADD COLUMN free_access TINYINT(1) NOT NULL DEFAULT 0 AFTER role;
ALTER TABLE submissions ADD COLUMN views INT NOT NULL DEFAULT 0 AFTER doi;
ALTER TABLE submissions ADD COLUMN downloads INT NOT NULL DEFAULT 0 AFTER views;

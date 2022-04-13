ALTER TABLE todos
    ADD completed TINYINT(1) NULL DEFAULT NULL AFTER description;
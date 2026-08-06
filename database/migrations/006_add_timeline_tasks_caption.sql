
ALTER TABLE `timeline_tasks`
    ADD COLUMN `caption` TEXT NULL AFTER `catatan`,
    ADD COLUMN `scheduler_active` TINYINT(1) NOT NULL DEFAULT 0 AFTER `caption`;







ALTER TABLE `notifications`
    ADD COLUMN `group_key` VARCHAR(100) NULL AFTER `icon`,
    ADD COLUMN `group_label` VARCHAR(255) NULL AFTER `group_key`,
    ADD INDEX `idx_notif_group` (`group_key`);


ALTER TABLE `timeline_tasks`
    ADD COLUMN `creator_id` INT NULL AFTER `created_by`,
    ADD COLUMN `assignee_id` INT NULL AFTER `creator_id`,
    ADD COLUMN `platform_id` INT UNSIGNED NULL AFTER `content_type`,
    ADD COLUMN `priority` ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium' AFTER `platform_id`,
    ADD COLUMN `description_update` TEXT NULL AFTER `catatan`,
    ADD COLUMN `user_notes` TEXT NULL AFTER `description_update`,
    ADD COLUMN `result_link` VARCHAR(500) NULL AFTER `edit_link`,
    ADD COLUMN `screenshot_attachment` VARCHAR(255) NULL AFTER `file_attachment`,
    ADD COLUMN `video_attachment` VARCHAR(255) NULL AFTER `screenshot_attachment`,
    ADD COLUMN `revision_notes` TEXT NULL AFTER `video_attachment`,
    ADD COLUMN `submitted_at` DATETIME NULL AFTER `revision_notes`,
    ADD COLUMN `approved_at` DATETIME NULL AFTER `submitted_at`,
    ADD COLUMN `approved_by` INT NULL AFTER `approved_at`,
    ADD INDEX `idx_tt_creator_status` (`creator_id`, `status`),
    ADD INDEX `idx_tt_assignee_status` (`assignee_id`, `status`),
    ADD INDEX `idx_tt_platform` (`platform_id`),
    ADD INDEX `idx_tt_submitted` (`submitted_at`);

UPDATE `timeline_tasks`
SET `creator_id` = `created_by`, `assignee_id` = `assigned_to`
WHERE `creator_id` IS NULL OR `assignee_id` IS NULL;

ALTER TABLE `timeline_tasks`
    MODIFY COLUMN `status` ENUM(
        'Assigned','In Progress','Pending Approval','Approved','Need Revision',
        'Selesai','Publish','Proses','Belum','Belum Selesai'
    ) NOT NULL DEFAULT 'Assigned';

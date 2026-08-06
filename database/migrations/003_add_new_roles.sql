





INSERT INTO `roles` (`slug`, `name`, `description`) VALUES
('admin_kmb', 'Admin KMB', 'Kepala Media Baru - dapat approve konten dengan re-check jika superadmin tidak ada'),
('magang', 'Magang', 'User magang dengan akses terbatas - hanya dapat melihat dan membuat draft')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`);


ALTER TABLE `planning_konten`
    ADD COLUMN `recheck_needed` TINYINT(1) DEFAULT 0 COMMENT '1 jika di-approve admin saat superadmin tidak ada, perlu re-check' AFTER `is_approved`,
    ADD COLUMN `rechecked_by` INT UNSIGNED NULL COMMENT 'Superadmin yang melakukan re-check' AFTER `recheck_needed`,
    ADD COLUMN `rechecked_at` TIMESTAMP NULL AFTER `rechecked_by`,
    ADD COLUMN `approved_by_role` VARCHAR(50) NULL COMMENT 'Role yang melakukan approval' AFTER `rechecked_at`,
    ADD INDEX `idx_pk_recheck` (`recheck_needed`);


ALTER TABLE `planning_konten`
    ADD COLUMN `group_id` VARCHAR(50) NULL COMMENT 'Group ID untuk grouping konten (excel sheet grouping)' AFTER `slug`,
    ADD COLUMN `group_name` VARCHAR(255) NULL COMMENT 'Nama grup (misal: CONTENT PLAN JULI, CONTENT UPLOAD)' AFTER `group_id`,
    ADD INDEX `idx_pk_group` (`group_id`);


ALTER TABLE `planning_konten`
    ADD COLUMN `source_import` VARCHAR(100) NULL COMMENT 'Sumber import (excel: nama file)' AFTER `group_name`,
    ADD COLUMN `source_row` INT NULL COMMENT 'Baris asal di Excel' AFTER `source_import`;


CREATE TABLE IF NOT EXISTS `import_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_size` BIGINT NOT NULL,
    `sheet_name` VARCHAR(255) NULL,
    `total_rows` INT DEFAULT 0,
    `imported_rows` INT DEFAULT 0,
    `skipped_rows` INT DEFAULT 0,
    `error_rows` INT DEFAULT 0,
    `status` ENUM('success','partial','failed') DEFAULT 'success',
    `error_message` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_import_user` (`user_id`),
    INDEX `idx_import_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `recheck_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `planning_id` INT UNSIGNED NOT NULL,
    `approved_by` INT UNSIGNED NOT NULL,
    `approved_by_role` VARCHAR(50) NOT NULL,
    `rechecked_by` INT UNSIGNED NULL,
    `rechecked_at` TIMESTAMP NULL,
    `status` ENUM('pending_recheck','approved','rejected') DEFAULT 'pending_recheck',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_rl_planning` (`planning_id`),
    INDEX `idx_rl_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `auto_post_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `planning_id` INT UNSIGNED NOT NULL,
    `platform_akun_id` INT UNSIGNED NOT NULL,
    `platform` VARCHAR(50) NOT NULL,
    `action` ENUM('posting','retry','success','failed') DEFAULT 'posting',
    `request_data` JSON NULL,
    `response_data` JSON NULL,
    `error_message` TEXT NULL,
    `http_status` INT NULL,
    `duration_ms` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_apl_planning` (`planning_id`),
    INDEX `idx_apl_platform` (`platform`),
    INDEX `idx_apl_action` (`action`),
    INDEX `idx_apl_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `settings` (`group`, `key`, `value`, `type`, `description`) VALUES
('auto_post', 'auto_post_enabled', '1', 'boolean', 'Aktifkan auto posting'),
('auto_post', 'auto_post_platforms', 'facebook,instagram,tiktok,youtube,twitter,threads', 'text', 'Platform yang diaktifkan untuk auto posting'),
('auto_post', 'auto_post_max_retry', '3', 'number', 'Maksimal retry auto posting'),
('auto_post', 'auto_post_interval_seconds', '30', 'number', 'Interval antar posting (detik)'),
('approval', 'admin_can_approve_without_superadmin', '1', 'boolean', 'Admin dapat approve jika superadmin tidak ada'),
('approval', 'recheck_required_after', '24', 'number', 'Re-check wajib dilakukan dalam X jam setelah approve admin')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

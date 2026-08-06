





CREATE TABLE IF NOT EXISTS `planning_konten_platform` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `planning_konten_id` INT UNSIGNED NOT NULL,
    `platform` ENUM('facebook', 'instagram', 'tiktok', 'youtube') NOT NULL,
    `status` ENUM('pending', 'publishing', 'success', 'failed') DEFAULT 'pending',
    `platform_post_id` VARCHAR(255) NULL COMMENT 'ID postingan dari provider social media',
    `published_at` TIMESTAMP NULL,
    `error_message` TEXT NULL,
    `retry_count` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_pkp_planning` (`planning_konten_id`),
    INDEX `idx_pkp_platform` (`platform`),
    INDEX `idx_pkp_status` (`status`),
    CONSTRAINT `fk_pkp_planning` FOREIGN KEY (`planning_konten_id`) REFERENCES `planning_konten`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

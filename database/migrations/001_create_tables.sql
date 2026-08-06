












CREATE TABLE `roles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(50) NOT NULL UNIQUE COMMENT 'super_admin, admin_sosmed, editor, kontributor',
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL COMMENT 'bcrypt hash',
    `avatar` VARCHAR(255) NULL,
    `phone` VARCHAR(30) NULL,
    `bio` TEXT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `last_login_at` TIMESTAMP NULL,
    `last_login_ip` VARCHAR(45) NULL,
    `twofa_secret` VARCHAR(255) NULL COMMENT 'Google Authenticator secret',
    `twofa_enabled` TINYINT(1) DEFAULT 0,
    `remember_token` VARCHAR(255) NULL,
    `password_reset_token` VARCHAR(255) NULL,
    `password_reset_expires` TIMESTAMP NULL,
    `session_timeout_minutes` INT DEFAULT 60,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL COMMENT 'Soft delete',
    INDEX `idx_users_role` (`role_id`),
    INDEX `idx_users_email` (`email`),
    INDEX `idx_users_username` (`username`),
    INDEX `idx_users_active` (`is_active`),
    CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `login_attempts` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(191) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT NULL,
    `success` TINYINT(1) DEFAULT 0,
    `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_login_email` (`email`),
    INDEX `idx_login_ip` (`ip_address`),
    INDEX `idx_login_time` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `user_sessions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `session_id` VARCHAR(255) NOT NULL UNIQUE,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT NULL,
    `payload` TEXT NULL,
    `last_activity` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_session_user` (`user_id`),
    INDEX `idx_session_id` (`session_id`),
    INDEX `idx_session_activity` (`last_activity`),
    CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;






CREATE TABLE `program_tv` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(200) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `category` ENUM('berita','hiburan','olahraga','budaya','religi','pendidikan','talkshow','musik','dokumenter','lainnya') DEFAULT 'lainnya',
    `cover_image` VARCHAR(255) NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_program_slug` (`slug`),
    INDEX `idx_program_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `kategori_konten` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `color` VARCHAR(7) DEFAULT '#3498db' COMMENT 'Hex color for calendar/card',
    `icon` VARCHAR(50) DEFAULT 'bi-tag' COMMENT 'Bootstrap icon class',
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_kat_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `jenis_konten` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `platform_type` VARCHAR(50) NULL COMMENT 'Untuk filter platform spesifik',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `platform_sosmed` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL COMMENT 'Facebook, Instagram, YouTube, TikTok, Twitter, Threads',
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `icon` VARCHAR(100) NOT NULL COMMENT 'Bootstrap icon class',
    `color` VARCHAR(7) NOT NULL COMMENT 'Brand color',
    `api_version` VARCHAR(20) NULL,
    `max_caption_length` INT DEFAULT 2200,
    `max_file_size_mb` INT DEFAULT 50,
    `supported_media` VARCHAR(255) DEFAULT 'image,video' COMMENT 'image,video,reels,shorts,story,carousel',
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `tags` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `color` VARCHAR(7) DEFAULT '#6c757d',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tag_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `hashtag` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `platform_id` INT UNSIGNED NULL COMMENT 'NULL = all platforms',
    `usage_count` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_hashtag_platform` (`platform_id`),
    CONSTRAINT `fk_hashtag_platform` FOREIGN KEY (`platform_id`) REFERENCES `platform_sosmed`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `template_caption` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200) NOT NULL,
    `content` TEXT NOT NULL,
    `platform_id` INT UNSIGNED NULL,
    `hashtag_placeholder` VARCHAR(100) DEFAULT '{hashtag}',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_temp_platform` (`platform_id`),
    CONSTRAINT `fk_temp_platform` FOREIGN KEY (`platform_id`) REFERENCES `platform_sosmed`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `lokasi_shooting` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200) NOT NULL,
    `address` TEXT NULL,
    `city` VARCHAR(100) NULL,
    `latitude` DECIMAL(10,8) NULL,
    `longitude` DECIMAL(11,8) NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `talent` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `photo` VARCHAR(255) NULL,
    `position` VARCHAR(100) NULL COMMENT 'Presenter, Reporter, Host, dll',
    `bio` TEXT NULL,
    `phone` VARCHAR(30) NULL,
    `email` VARCHAR(191) NULL,
    `instagram` VARCHAR(100) NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_talent_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;





CREATE TABLE `platform_akun` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `platform_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL COMMENT 'PIC yang mengelola akun ini',
    `account_name` VARCHAR(200) NOT NULL COMMENT 'Nama halaman/akun',
    `account_id` VARCHAR(255) NOT NULL COMMENT 'ID dari platform (page_id, user_id, channel_id)',
    `account_username` VARCHAR(200) NULL,
    `avatar_url` VARCHAR(255) NULL,
    `bio` TEXT NULL,
    `followers_count` INT DEFAULT 0,
    `following_count` INT DEFAULT 0,
    `access_token` TEXT NULL COMMENT 'Encrypted',
    `refresh_token` TEXT NULL COMMENT 'Encrypted',
    `token_expires_at` TIMESTAMP NULL,
    `token_status` ENUM('active','expired','revoked','error') DEFAULT 'active',
    `last_sync_at` TIMESTAMP NULL,
    `is_connected` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `webhook_verify_token` VARCHAR(255) NULL,
    `settings` JSON NULL COMMENT 'Platform-specific settings',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_pa_platform` (`platform_id`),
    INDEX `idx_pa_user` (`user_id`),
    INDEX `idx_pa_status` (`token_status`),
    CONSTRAINT `fk_pa_platform` FOREIGN KEY (`platform_id`) REFERENCES `platform_sosmed`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_pa_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;






CREATE TABLE `planning_konten` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `judul` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `program_id` INT UNSIGNED NULL,
    `kategori_id` INT UNSIGNED NULL,
    `jenis_konten_id` INT UNSIGNED NULL,
    `platform_id` INT UNSIGNED NULL,
    `platform_akun_id` INT UNSIGNED NULL,
    `caption` TEXT NULL,
    `hashtag_text` TEXT NULL COMMENT 'Space-separated or JSON array',
    `media_type` ENUM('image','video','reels','shorts','story','carousel','text') DEFAULT 'image',
    `media_path` VARCHAR(255) NULL COMMENT 'Main media file path',
    `media_thumbnail` VARCHAR(255) NULL,
    `media_duration` INT NULL COMMENT 'Duration in seconds (for video)',
    `media_size_bytes` BIGINT NULL,
    `media_mime_type` VARCHAR(100) NULL,
    `carousel_images` JSON NULL COMMENT 'Array of file paths for carousel',
    `tanggal_posting` DATE NULL,
    `jam_posting` TIME NULL,
    `scheduled_at` TIMESTAMP NULL,
    `posted_at` TIMESTAMP NULL,
    `status` ENUM('draft','review','approved','revision','scheduled','posting','success','failed','cancelled') DEFAULT 'draft',
    `priority` ENUM('low','medium','high','urgent') DEFAULT 'medium',
    `pic_id` INT UNSIGNED NULL COMMENT 'Penanggung jawab konten',
    `editor_id` INT UNSIGNED NULL COMMENT 'Editor yang mereview',
    `approved_by` INT UNSIGNED NULL COMMENT 'Admin yang approve final',
    `lokasi_shooting_id` INT UNSIGNED NULL,
    `talent_id` INT UNSIGNED NULL,
    `deadline` DATE NULL,
    `catatan` TEXT NULL,
    `revision_note` TEXT NULL,
    `revision_count` INT DEFAULT 0,
    `retry_count` INT DEFAULT 0,
    `max_retry` INT DEFAULT 3,
    `error_message` TEXT NULL,
    `post_id_platform` VARCHAR(255) NULL COMMENT 'ID posting dari platform',
    `post_url` VARCHAR(500) NULL COMMENT 'URL posting yang sudah live',
    `engagement_like` INT DEFAULT 0,
    `engagement_comment` INT DEFAULT 0,
    `engagement_share` INT DEFAULT 0,
    `engagement_save` INT DEFAULT 0,
    `reach_count` INT DEFAULT 0,
    `view_count` INT DEFAULT 0,
    `is_approved` TINYINT(1) DEFAULT 0,
    `approved_at` TIMESTAMP NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    INDEX `idx_pk_program` (`program_id`),
    INDEX `idx_pk_kategori` (`kategori_id`),
    INDEX `idx_pk_jenis` (`jenis_konten_id`),
    INDEX `idx_pk_platform` (`platform_id`),
    INDEX `idx_pk_akun` (`platform_akun_id`),
    INDEX `idx_pk_status` (`status`),
    INDEX `idx_pk_tanggal` (`tanggal_posting`),
    INDEX `idx_pk_pic` (`pic_id`),
    INDEX `idx_pk_editor` (`editor_id`),
    INDEX `idx_pk_creator` (`created_by`),
    INDEX `idx_pk_priority` (`priority`),
    INDEX `idx_pk_scheduled` (`scheduled_at`),
    INDEX `idx_pk_active` (`is_active`),
    CONSTRAINT `fk_pk_program` FOREIGN KEY (`program_id`) REFERENCES `program_tv`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_pk_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_konten`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_pk_jenis` FOREIGN KEY (`jenis_konten_id`) REFERENCES `jenis_konten`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_pk_platform` FOREIGN KEY (`platform_id`) REFERENCES `platform_sosmed`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_pk_akun` FOREIGN KEY (`platform_akun_id`) REFERENCES `platform_akun`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_pk_pic` FOREIGN KEY (`pic_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_pk_editor` FOREIGN KEY (`editor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_pk_approver` FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_pk_creator` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_pk_lokasi` FOREIGN KEY (`lokasi_shooting_id`) REFERENCES `lokasi_shooting`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_pk_talent` FOREIGN KEY (`talent_id`) REFERENCES `talent`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `planning_tags` (
    `planning_id` INT UNSIGNED NOT NULL,
    `tag_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`planning_id`, `tag_id`),
    CONSTRAINT `fk_pt_planning` FOREIGN KEY (`planning_id`) REFERENCES `planning_konten`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_pt_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `planning_media` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `planning_id` INT UNSIGNED NOT NULL,
    `media_type` ENUM('image','video','thumbnail','document','other') DEFAULT 'image',
    `file_path` VARCHAR(255) NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_size_bytes` BIGINT NULL,
    `mime_type` VARCHAR(100) NULL,
    `is_primary` TINYINT(1) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pm_planning` (`planning_id`),
    CONSTRAINT `fk_pm_planning` FOREIGN KEY (`planning_id`) REFERENCES `planning_konten`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;






CREATE TABLE `scheduler_queue` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `planning_id` INT UNSIGNED NOT NULL,
    `platform_akun_id` INT UNSIGNED NOT NULL,
    `status` ENUM('queued','processing','success','failed','cancelled') DEFAULT 'queued',
    `priority` TINYINT DEFAULT 0,
    `scheduled_at` TIMESTAMP NOT NULL,
    `started_at` TIMESTAMP NULL,
    `completed_at` TIMESTAMP NULL,
    `retry_count` INT DEFAULT 0,
    `max_retry` INT DEFAULT 3,
    `last_error` TEXT NULL,
    `last_error_at` TIMESTAMP NULL,
    `cron_job_id` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sq_planning` (`planning_id`),
    INDEX `idx_sq_akun` (`platform_akun_id`),
    INDEX `idx_sq_status` (`status`),
    INDEX `idx_sq_scheduled` (`scheduled_at`),
    INDEX `idx_sq_priority` (`priority`),
    CONSTRAINT `fk_sq_planning` FOREIGN KEY (`planning_id`) REFERENCES `planning_konten`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_sq_akun` FOREIGN KEY (`platform_akun_id`) REFERENCES `platform_akun`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `posting_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `planning_id` INT UNSIGNED NOT NULL,
    `platform_akun_id` INT UNSIGNED NOT NULL,
    `action` ENUM('posting','retry','schedule','cancel','approve','reject','review','revise','delete') NOT NULL,
    `status` ENUM('success','failed','pending') NOT NULL,
    `request_data` JSON NULL,
    `response_data` JSON NULL,
    `error_message` TEXT NULL,
    `http_code` INT NULL,
    `ip_address` VARCHAR(45) NULL,
    `performed_by` INT UNSIGNED NULL,
    `duration_ms` INT NULL COMMENT 'Execution time in milliseconds',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pl_planning` (`planning_id`),
    INDEX `idx_pl_akun` (`platform_akun_id`),
    INDEX `idx_pl_action` (`action`),
    INDEX `idx_pl_status` (`status`),
    INDEX `idx_pl_user` (`performed_by`),
    INDEX `idx_pl_created` (`created_at`),
    CONSTRAINT `fk_pl_planning` FOREIGN KEY (`planning_id`) REFERENCES `planning_konten`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_pl_akun` FOREIGN KEY (`platform_akun_id`) REFERENCES `platform_akun`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_pl_user` FOREIGN KEY (`performed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;





CREATE TABLE `activity_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `role_id` INT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL COMMENT 'login, logout, create, update, delete, approve, reject, post, dll',
    `module` VARCHAR(100) NOT NULL COMMENT 'users, planning, platform, settings, dll',
    `table_name` VARCHAR(100) NULL,
    `record_id` INT UNSIGNED NULL,
    `description` TEXT NULL,
    `old_data` JSON NULL,
    `new_data` JSON NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_al_user` (`user_id`),
    INDEX `idx_al_role` (`role_id`),
    INDEX `idx_al_action` (`action`),
    INDEX `idx_al_module` (`module`),
    INDEX `idx_al_record` (`record_id`),
    INDEX `idx_al_created` (`created_at`),
    CONSTRAINT `fk_al_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_al_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;





CREATE TABLE `notifications` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `type` ENUM('info','success','warning','error') DEFAULT 'info',
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `link` VARCHAR(500) NULL,
    `icon` VARCHAR(50) NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `read_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_notif_user` (`user_id`),
    INDEX `idx_notif_read` (`is_read`),
    INDEX `idx_notif_created` (`created_at`),
    CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;





CREATE TABLE `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group` VARCHAR(100) NOT NULL DEFAULT 'general' COMMENT 'general, scheduler, social, email, backup',
    `key` VARCHAR(191) NOT NULL UNIQUE,
    `value` TEXT NULL,
    `type` ENUM('text','textarea','number','boolean','select','json','file') DEFAULT 'text',
    `description` TEXT NULL,
    `is_encrypted` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_set_group` (`group`),
    INDEX `idx_set_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;





CREATE TABLE `backup_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `type` ENUM('database','full') DEFAULT 'database',
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_size_bytes` BIGINT NULL,
    `status` ENUM('success','failed','running') DEFAULT 'running',
    `error_message` TEXT NULL,
    `started_at` TIMESTAMP NULL,
    `completed_at` TIMESTAMP NULL,
    `performed_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_bl_status` (`status`),
    CONSTRAINT `fk_bl_user` FOREIGN KEY (`performed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;











ALTER TABLE `users`
    ADD COLUMN `jurusan` VARCHAR(150) NULL COMMENT 'Jurusan/program studi untuk user magang' AFTER `phone`,
    ADD COLUMN `approval_status` ENUM('pending', 'approved', 'rejected') DEFAULT 'approved' COMMENT 'Status persetujuan akun magang' AFTER `is_active`,
    ADD COLUMN `rejection_reason` TEXT NULL COMMENT 'Alasan penolakan registrasi akun' AFTER `approval_status`,
    ADD INDEX `idx_users_approval` (`approval_status`);


CREATE TABLE IF NOT EXISTS `otp_verifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(191) NOT NULL,
    `otp_code` VARCHAR(10) NOT NULL,
    `payload` JSON NULL COMMENT 'Data registrasi sementara: name, jurusan, phone',
    `expires_at` TIMESTAMP NOT NULL,
    `is_verified` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_otp_email` (`email`),
    INDEX `idx_otp_code` (`otp_code`),
    INDEX `idx_otp_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

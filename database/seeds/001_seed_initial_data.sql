







INSERT INTO `roles` (`slug`, `name`, `description`) VALUES
('super_admin', 'Super Admin', 'Memiliki akses penuh ke seluruh sistem'),
('admin_sosmed', 'Admin Sosmed', 'Membuat planning, approval, upload, scheduler, analytics'),
('editor', 'Editor', 'Membuat konten, upload media, edit caption, revisi'),
('kontributor', 'Kontributor', 'Submit ide konten, upload draft');





INSERT INTO `users` (`role_id`, `name`, `email`, `username`, `password`, `phone`, `is_active`) VALUES
(1, 'Super Admin TVRI', 'admin@tvri-jatim.id', 'superadmin', '$2y$12$LJ3m4ys3Lg3YOCwKkCpO7eYxHxHxHxHxHxHxHxHxHxHxHxHxHxH', '081234567890', 1);




INSERT INTO `platform_sosmed` (`name`, `slug`, `icon`, `color`, `api_version`, `max_caption_length`, `max_file_size_mb`, `supported_media`, `sort_order`) VALUES
('Facebook', 'facebook', 'bi-facebook', '#1877F2', 'v18.0', 63206, 100, 'image,video,carousel,story', 1),
('Instagram', 'instagram', 'bi-instagram', '#E4405F', 'v18.0', 2200, 100, 'image,video,reels,story,carousel', 2),
('YouTube', 'youtube', 'bi-youtube', '#FF0000', 'v3', 5000, 256, 'video,shorts', 3),
('TikTok', 'tiktok', 'bi-tiktok', '#000000', 'v2', 2200, 100, 'image,video', 4),
('Twitter / X', 'twitter', 'bi-twitter-x', '#000000', 'v2', 4000, 15, 'image,video', 5),
('Threads', 'threads', 'bi-threads', '#000000', 'v1', 500, 100, 'image,video', 6);




INSERT INTO `kategori_konten` (`name`, `slug`, `description`, `color`, `icon`, `sort_order`) VALUES
('Berita Terkini', 'berita-terkini', 'Berita dan informasi terkini Jawa Timur', '#3498db', 'bi-newspaper', 1),
('Info Publik', 'info-publik', 'Informasi layanan publik dan pengumuman', '#2ecc71', 'bi-megaphone', 2),
('Budaya & Tradisi', 'budaya-tradisi', 'Konten budaya dan tradisi Jawa Timur', '#e74c3c', 'bi-palette', 3),
('Wisata & Kuliner', 'wisata-kuliner', 'Destinasi wisata dan kuliner khas Jatim', '#f39c12', 'bi-geo-alt', 4),
('Olahraga', 'olahraga', 'Liputan olahraga dan atlit Jatim', '#9b59b6', 'bi-trophy', 5),
('Pendidikan', 'pendidikan', 'Konten edukasi dan pendidikan', '#1abc9c', 'bi-book', 6),
('Program TVRI', 'program-tvri', 'Promo dan cuplikan program TVRI Jatim', '#34495e', 'bi-tv', 7),
('Behind The Scene', 'behind-the-scene', 'Proses produksi dan BTS', '#e67e22', 'bi-camera-reels', 8);




INSERT INTO `jenis_konten` (`name`, `slug`, `description`, `sort_order`) VALUES
('Informasi', 'informasi', 'Konten informatif dan berita', 1),
('Hiburan', 'hiburan', 'Konten menghibur', 2),
('Edukasi', 'edukasi', 'Konten pendidikan', 3),
('Promosi', 'promosi', 'Konten promosi program TVRI', 4),
('Behind The Scene', 'behind-the-scene', 'Proses produksi konten', 5),
('Hard News', 'hard-news', 'Berita keras aktual', 6),
('Soft News', 'soft-news', 'Berita ringan dan feature', 7),
('Live Report', 'live-report', 'Laporan langsung dari lokasi', 8),
('Teaser', 'teaser', 'Cuplikan program yang akan tayang', 9),
('Highlight', 'highlight', 'Momen terbaik dari program', 10);




INSERT INTO `settings` (`group`, `key`, `value`, `type`, `description`) VALUES
('general', 'app_name', 'TVRI Jawa Timur - Social Media Planner', 'text', 'Nama aplikasi'),
('general', 'app_logo', '/assets/img/logo-tvri-jatim.png', 'file', 'Logo TVRI Jawa Timur'),
('general', 'app_favicon', '/assets/img/favicon-tvri.png', 'file', 'Favicon aplikasi'),
('general', 'app_timezone', 'Asia/Jakarta', 'select', 'Zona waktu aplikasi'),
('general', 'app_language', 'id', 'select', 'Bahasa aplikasi'),
('scheduler', 'scheduler_enabled', '1', 'boolean', 'Aktifkan auto scheduler'),
('scheduler', 'scheduler_interval', '1', 'number', 'Interval cron job (menit)'),
('scheduler', 'scheduler_retry_max', '3', 'number', 'Maksimal retry posting'),
('scheduler', 'scheduler_retry_delay', '5', 'number', 'Delay antar retry (menit)'),
('scheduler', 'scheduler_batch_size', '10', 'number', 'Jumlah posting per eksekusi'),
('social', 'token_refresh_interval', '24', 'number', 'Interval refresh token (jam)'),
('backup', 'backup_enabled', '1', 'boolean', 'Aktifkan backup otomatis'),
('backup', 'backup_interval_days', '7', 'number', 'Interval backup (hari)'),
('backup', 'backup_retention_days', '30', 'number', 'Retensi file backup (hari)'),
('backup', 'backup_path', '/backups/', 'text', 'Folder penyimpanan backup'),
('email', 'smtp_host', 'mail.tvri-jatim.id', 'text', 'SMTP server'),
('email', 'smtp_port', '587', 'text', 'SMTP port'),
('email', 'smtp_secure', 'tls', 'text', 'SMTP encryption'),
('email', 'smtp_username', 'noreply@tvri-jatim.id', 'text', 'SMTP username'),
('email', 'smtp_password', '', 'text', 'SMTP password (encrypted)'),
('email', 'notify_on_fail', '1', 'boolean', 'Notifikasi email saat posting gagal'),
('general', 'default_caption_template', 'Halo #followers! \n\n{content}\n\n#TVRIJatim #JawaTimur', 'textarea', 'Template caption default');




INSERT INTO `program_tv` (`name`, `slug`, `description`, `category`, `sort_order`) VALUES
('Jatim Hari Ini', 'jatim-hari-ini', 'Program berita utama TVRI Jawa Timur', 'berita', 1),
('Pojok Kampung', 'pojok-kampung', 'Liputan dari berbagai kampung di Jawa Timur', 'berita', 2),
('Jatim Sejahtera', 'jatim-sejahtera', 'Program pemberdayaan masyarakat', 'pendidikan', 3),
('Warung Ojo', 'warung-ojo', 'Program kuliner khas Jawa Timur', 'hiburan', 4),
('Bumi Nusantara', 'bumi-nusantara', 'Program budaya dan wisata', 'budaya', 5),
('Jatim Bersuara', 'jatim-bersuara', 'Talkshow interaktif dengan pemirsa', 'talkshow', 6),
('Religi Jatim', 'religi-jatim', 'Program religi dan kerohanian', 'religi', 7),
('Jatim Sport', 'jatim-sport', 'Liputan olahraga Jawa Timur', 'olahraga', 8),
('Gema Pendidikan', 'gema-pendidikan', 'Program pendidikan dan literasi', 'pendidikan', 9),
('Jatim Music', 'jatim-music', 'Program musik dan pertunjukan seni', 'musik', 10);




INSERT INTO `tags` (`name`, `slug`, `color`) VALUES
('Breaking News', 'breaking-news', '#e74c3c'),
('Hard News', 'hard-news', '#c0392b'),
('Feature', 'feature', '#3498db'),
('Human Interest', 'human-interest', '#2ecc71'),
('Viral', 'viral', '#f39c12'),
('Eksklusif', 'eksklusif', '#9b59b6'),
('Live', 'live', '#e67e22'),
('TNI-Polri', 'tni-polri', '#2c3e50'),
('Pembangunan', 'pembangunan', '#1abc9c'),
('Bencana', 'bencana', '#e74c3c'),
('Kesehatan', 'kesehatan', '#1abc9c'),
('Ekonomi', 'ekonomi', '#34495e'),
('Politik', 'politik', '#8e44ad'),
('Olahraga', 'olahraga', '#e74c3c'),
('Seni Budaya', 'seni-budaya', '#f39c12');




INSERT INTO `hashtag` (`name`, `slug`, `usage_count`) VALUES
('#TVRIJatim', 'tvrijatim', 0),
('#JawaTimur', 'jawatimur', 0),
('#TVRI', 'tvri', 0),
('#PojokKampung', 'pojokkampung', 0),
('#JatimHariIni', 'jatimhariini', 0),
('#InfoPublik', 'infopublik', 0),
('#JatimSejahtera', 'jatimsejahtera', 0),
('#BudayaJatim', 'budayajatim', 0),
('#WisataJatim', 'wisatajatim', 0),
('#KulinerJatim', 'kulinerjatim', 0),
('#BeritaDaerah', 'beritadaerah', 0),
('#Nusantara', 'nusantara', 0);





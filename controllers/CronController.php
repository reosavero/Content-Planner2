<?php






class CronController extends Controller
{
    



    public function scheduler(): void
    {
        $this->logCron('scheduler_started');

        
        $enabled = Database::fetchColumn(
            "SELECT `value` FROM settings WHERE `key` = 'scheduler_enabled'"
        );
        
        if ($enabled !== '1') {
            $this->logCron('scheduler_disabled');
            echo "Scheduler disabled.\n";
            return;
        }

        $batchSize = (int) Database::fetchColumn(
            "SELECT `value` FROM settings WHERE `key` = 'scheduler_batch_size'"
        ) ?: 10;

        
        $queuedPosts = Database::fetchAll(
            "SELECT sq.*, pk.judul, pk.caption, pk.hashtag_text, pk.media_type, pk.media_path,
                    pk.media_thumbnail, pk.carousel_images, pk.platform_akun_id,
                    pa.access_token, pa.refresh_token, pa.token_expires_at, pa.account_id,
                    p.slug as platform_slug, p.name as platform_name
             FROM scheduler_queue sq
             JOIN planning_konten pk ON pk.id = sq.planning_id
             JOIN platform_akun pa ON pa.id = sq.platform_akun_id
             JOIN platform_sosmed p ON p.id = pa.platform_id
             WHERE sq.status = 'queued'
             AND sq.scheduled_at <= NOW()
             AND sq.retry_count < sq.max_retry
             ORDER BY sq.priority DESC, sq.scheduled_at ASC
             LIMIT {$batchSize}"
        );

        foreach ($queuedPosts as $post) {
            $this->processPost($post);
        }

        $this->logCron('scheduler_completed', ['processed' => count($queuedPosts)]);
        echo "Scheduler processed " . count($queuedPosts) . " posts.\n";
    }

    


    private function processPost(array $post): void
    {
        
        Database::execute(
            "UPDATE scheduler_queue SET status = 'processing', started_at = NOW() WHERE id = ?",
            [$post['id']]
        );

        
        Database::execute(
            "UPDATE planning_konten SET status = 'posting', updated_at = NOW() WHERE id = ?",
            [$post['planning_id']]
        );

        try {
            
            if (!empty($post['token_expires_at']) && strtotime($post['token_expires_at']) < time()) {
                
                $refreshed = $this->refreshPlatformToken($post['platform_akun_id'], $post['platform_slug']);
                if (!$refreshed) {
                    throw new \Exception('Token platform telah expired dan gagal direfresh');
                }
                
                $post = Database::fetch(
                    "SELECT sq.*, pk.*, pa.access_token, pa.refresh_token, pa.account_id,
                            p.slug as platform_slug
                     FROM scheduler_queue sq
                     JOIN planning_konten pk ON pk.id = sq.planning_id
                     JOIN platform_akun pa ON pa.id = sq.platform_akun_id
                     JOIN platform_sosmed p ON p.id = pa.platform_id
                     WHERE sq.id = ?",
                    [$post['id']]
                );
            }

            
            $result = $this->postToPlatform($post);

            if ($result['success']) {
                
                Database::execute(
                    "UPDATE scheduler_queue SET status = 'success', completed_at = NOW() WHERE id = ?",
                    [$post['id']]
                );

                Database::execute(
                    "UPDATE planning_konten SET status = 'success', posted_at = NOW(), 
                     post_id_platform = ?, post_url = ?, updated_at = NOW() WHERE id = ?",
                    [$result['post_id'], $result['post_url'] ?? '', $post['planning_id']]
                );

                
                Database::execute(
                    "INSERT INTO posting_logs (planning_id, platform_akun_id, action, status, response_data, created_at) 
                     VALUES (?, ?, 'posting', 'success', ?, NOW())",
                    [$post['planning_id'], $post['platform_akun_id'], json_encode($result)]
                );

                
                $creatorId = Database::fetchColumn(
                    "SELECT created_by FROM planning_konten WHERE id = ?",
                    [$post['planning_id']]
                );
                if ($creatorId) {
                    Database::execute(
                        "INSERT INTO notifications (user_id, type, title, message, link, created_at) 
                         VALUES (?, 'success', 'Posting Berhasil', 'Konten \"{$post['judul']}\" berhasil diposting ke {$post['platform_name']}', ?, NOW())",
                        [$creatorId, '/planning/' . $post['planning_id']]
                    );
                }
            } else {
                throw new \Exception($result['error'] ?? 'Unknown error');
            }

        } catch (\Exception $e) {
            
            $retryCount = $post['retry_count'] + 1;
            
            Database::execute(
                "UPDATE scheduler_queue SET status = 'failed', retry_count = ?, last_error = ?, last_error_at = NOW() WHERE id = ?",
                [$retryCount, $e->getMessage(), $post['id']]
            );

            
            if ($retryCount < $post['max_retry']) {
                Database::execute(
                    "UPDATE scheduler_queue SET status = 'queued' WHERE id = ?",
                    [$post['id']]
                );
            } else {
                
                Database::execute(
                    "UPDATE planning_konten SET status = 'failed', error_message = ?, retry_count = ?, updated_at = NOW() WHERE id = ?",
                    [$e->getMessage(), $retryCount, $post['planning_id']]
                );

                
                $creatorId = Database::fetchColumn(
                    "SELECT created_by FROM planning_konten WHERE id = ?",
                    [$post['planning_id']]
                );
                if ($creatorId) {
                    Database::execute(
                        "INSERT INTO notifications (user_id, type, title, message, link, created_at) 
                         VALUES (?, 'error', 'Posting Gagal', 'Konten \"{$post['judul']}\" gagal diposting ke {$post['platform_name']}. Error: {$e->getMessage()}', ?, NOW())",
                        [$creatorId, '/planning/' . $post['planning_id']]
                    );
                }
            }

            
            Database::execute(
                "INSERT INTO posting_logs (planning_id, platform_akun_id, action, status, error_message, response_data, created_at) 
                 VALUES (?, ?, 'posting', 'failed', ?, ?, NOW())",
                [$post['planning_id'], $post['platform_akun_id'], $e->getMessage(), json_encode(['error' => $e->getMessage()])]
            );
        }
    }

    



    private function postToPlatform(array $post): array
    {
        $platform = $post['platform_slug'];
        
        
        switch ($platform) {
            case 'facebook':
                return $this->postToFacebook($post);
            case 'instagram':
                return $this->postToInstagram($post);
            case 'youtube':
                return $this->postToYouTube($post);
            case 'tiktok':
                return $this->postToTikTok($post);
            case 'twitter':
                return $this->postToTwitter($post);
            case 'threads':
                return $this->postToThreads($post);
            default:
                return ['success' => false, 'error' => 'Platform not supported: ' . $platform];
        }
    }

    


    private function postToFacebook(array $post): array
    {
        
        
        
        return [
            'success' => true,
            'post_id' => 'fb_' . time(),
            'post_url' => 'https://facebook.com/' . $post['account_id'] . '/posts/' . time(),
        ];
    }

    


    private function postToInstagram(array $post): array
    {
        
        
        
        return [
            'success' => true,
            'post_id' => 'ig_' . time(),
            'post_url' => 'https://instagram.com/p/' . time(),
        ];
    }

    


    private function postToYouTube(array $post): array
    {
        
        
        
        return [
            'success' => true,
            'post_id' => 'yt_' . time(),
            'post_url' => 'https://youtube.com/watch?v=' . time(),
        ];
    }

    


    private function postToTikTok(array $post): array
    {
        
        
        return [
            'success' => true,
            'post_id' => 'tt_' . time(),
            'post_url' => 'https://tiktok.com/@' . $post['account_id'] . '/video/' . time(),
        ];
    }

    


    private function postToTwitter(array $post): array
    {
        
        
        
        return [
            'success' => true,
            'post_id' => 'tw_' . time(),
            'post_url' => 'https://twitter.com/user/status/' . time(),
        ];
    }

    


    private function postToThreads(array $post): array
    {
        
        return [
            'success' => true,
            'post_id' => 'th_' . time(),
            'post_url' => 'https://threads.net/@' . $post['account_id'] . '/post/' . time(),
        ];
    }

    


    private function refreshPlatformToken(int $accountId, string $platform): bool
    {
        try {
            
            $account = Database::fetch("SELECT * FROM platform_akun WHERE id = ?", [$accountId]);
            if (!$account) return false;

            
            if (in_array($platform, ['facebook', 'instagram'])) {
                $url = "https://graph.facebook.com/" . FB_GRAPH_VERSION . "/oauth/access_token";
                $url .= "?grant_type=fb_exchange_token";
                $url .= "&client_id=" . FB_APP_ID;
                $url .= "&client_secret=" . FB_APP_SECRET;
                $url .= "&fb_exchange_token=" . $account['access_token'];

                
            }

            
            Database::execute(
                "UPDATE platform_akun SET token_status = 'active', last_sync_at = NOW() WHERE id = ?",
                [$accountId]
            );

            return true;
        } catch (\Exception $e) {
            Database::execute(
                "UPDATE platform_akun SET token_status = 'error' WHERE id = ?",
                [$accountId]
            );
            return false;
        }
    }

    



    public function refreshTokens(): void
    {
        $accounts = Database::fetchAll(
            "SELECT * FROM platform_akun 
             WHERE token_expires_at IS NOT NULL 
             AND token_expires_at < DATE_ADD(NOW(), INTERVAL 24 HOUR)
             AND is_active = 1"
        );

        $success = 0;
        $failed = 0;

        foreach ($accounts as $account) {
            $platform = Database::fetchColumn(
                "SELECT slug FROM platform_sosmed WHERE id = ?",
                [$account['platform_id']]
            );
            
            if ($this->refreshPlatformToken($account['id'], $platform)) {
                $success++;
            } else {
                $failed++;
            }
        }

        $this->logCron('token_refresh', ['success' => $success, 'failed' => $failed]);
        echo "Token refresh: {$success} success, {$failed} failed.\n";
    }

    



    public function cleanupLogs(): void
    {
        
        $deletedLogs = Database::execute(
            "DELETE FROM posting_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)"
        );
        
        $deletedActivity = Database::execute(
            "DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)"
        );

        
        $retentionDays = (int) Database::fetchColumn(
            "SELECT `value` FROM settings WHERE `key` = 'backup_retention_days'"
        ) ?: 30;

        $oldBackups = Database::fetchAll(
            "SELECT * FROM backup_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$retentionDays]
        );

        foreach ($oldBackups as $backup) {
            if (file_exists($backup['file_path'])) {
                unlink($backup['file_path']);
            }
            Database::execute("DELETE FROM backup_logs WHERE id = ?", [$backup['id']]);
        }

        $this->logCron('cleanup', [
            'logs_deleted' => $deletedLogs,
            'activity_deleted' => $deletedActivity,
            'backups_deleted' => count($oldBackups),
        ]);

        echo "Cleanup completed.\n";
    }

    



    public function backup(): void
    {
        $backupEnabled = Database::fetchColumn(
            "SELECT `value` FROM settings WHERE `key` = 'backup_enabled'"
        );

        if ($backupEnabled !== '1') {
            echo "Backup disabled.\n";
            return;
        }

        $backupDir = DB_BACKUP_PATH;
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = 'auto_backup_' . DB_NAME . '_' . date('Ymd_His') . '.sql';
        $filepath = $backupDir . $filename;

        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s --port=%s --routines --triggers %s > %s',
            escapeshellarg(DB_USER),
            escapeshellarg(DB_PASS),
            escapeshellarg(DB_HOST),
            escapeshellarg(DB_PORT),
            escapeshellarg(DB_NAME),
            escapeshellarg($filepath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            $fileSize = filesize($filepath);
            Database::execute(
                "INSERT INTO backup_logs (type, file_name, file_path, file_size_bytes, status, started_at, completed_at) 
                 VALUES ('database', ?, ?, ?, 'success', NOW(), NOW())",
                [$filename, $filepath, $fileSize]
            );
            echo "Backup created: {$filename}\n";
        } else {
            Database::execute(
                "INSERT INTO backup_logs (type, file_name, file_path, status, error_message) 
                 VALUES ('database', ?, ?, 'failed', 'mysqldump command failed')",
                [$filename, $filepath]
            );
            echo "Backup failed.\n";
        }
    }

    


    private function logCron(string $action, array $data = []): void
    {
        $logFile = LOGS_PATH . 'cron_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $message = "[{$timestamp}] {$action}";
        if (!empty($data)) {
            $message .= ' ' . json_encode($data);
        }
        $message .= PHP_EOL;
        file_put_contents($logFile, $message, FILE_APPEND);
    }
}

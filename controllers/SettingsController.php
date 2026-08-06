<?php





class SettingsController extends Controller
{
    


    public function index(): void
    {
        $settings = Database::fetchAll(
            "SELECT * FROM settings ORDER BY `group`, id ASC"
        );

        
        $grouped = [];
        foreach ($settings as $s) {
            $grouped[$s['group']][] = $s;
        }

        $backups = Database::fetchAll(
            "SELECT * FROM backup_logs ORDER BY created_at DESC LIMIT 10"
        );

        $this->view('settings/index', [
            'title' => 'Pengaturan Sistem',
            'grouped' => $grouped,
            'backups' => $backups,
            'breadcrumbs' => [
                ['label' => 'Pengaturan', 'url' => '#'],
            ],
        ]);
    }

    


    public function update(): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid.');
        }

        $settings = $_POST['settings'] ?? [];

        foreach ($settings as $key => $value) {
            Database::execute(
                "UPDATE settings SET `value` = ?, updated_at = NOW() WHERE `key` = ?",
                [$value, $key]
            );
        }

        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, description, ip_address) 
             VALUES (?, ?, 'update', 'settings', 'Mengupdate pengaturan sistem', ?)",
            [Session::get('user_id'), Session::get('user_role_id'), $_SERVER['REMOTE_ADDR']]
        );

        $this->redirectBackWith('success', 'Pengaturan berhasil disimpan.');
    }

    


    public function backup(): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid'], 403);
        }

        $backupDir = DB_BACKUP_PATH;
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = 'backup_' . DB_NAME . '_' . date('Ymd_His') . '.sql';
        $filepath = $backupDir . $filename;

        try {
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s --port=%s --routines --triggers --add-drop-database %s > %s',
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
                    "INSERT INTO backup_logs (type, file_name, file_path, file_size_bytes, status, started_at, completed_at, performed_by) 
                     VALUES ('database', ?, ?, ?, 'success', NOW(), NOW(), ?)",
                    [$filename, $filepath, $fileSize, Session::get('user_id')]
                );

                $this->success([
                    'filename' => $filename,
                    'size' => formatFileSize($fileSize),
                    'path' => $filepath,
                ], 'Backup database berhasil dibuat.');
            } else {
                throw new Exception('mysqldump command failed');
            }
        } catch (Exception $e) {
            Database::execute(
                "INSERT INTO backup_logs (type, file_name, file_path, status, error_message, performed_by) 
                 VALUES ('database', ?, ?, 'failed', ?, ?)",
                [$filename, $filepath, $e->getMessage(), Session::get('user_id')]
            );

            $this->error('Gagal membuat backup: ' . $e->getMessage(), 500);
        }
    }

    


    public function downloadBackup(string $id): void
    {
        $backup = Database::fetch("SELECT * FROM backup_logs WHERE id = ?", [$id]);
        if (!$backup || !file_exists($backup['file_path'])) {
            $this->redirectWith('/settings', 'error', 'File backup tidak ditemukan.');
        }

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $backup['file_name'] . '"');
        header('Content-Length: ' . $backup['file_size_bytes']);
        readfile($backup['file_path']);
        exit;
    }

    


    public function restore(): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid'], 403);
        }

        $backupId = $_POST['backup_id'] ?? 0;
        $backup = Database::fetch("SELECT * FROM backup_logs WHERE id = ? AND status = 'success'", [$backupId]);

        if (!$backup || !file_exists($backup['file_path'])) {
            $this->error('File backup tidak ditemukan.', 404);
        }

        try {
            $command = sprintf(
                'mysql --user=%s --password=%s --host=%s --port=%s %s < %s',
                escapeshellarg(DB_USER),
                escapeshellarg(DB_PASS),
                escapeshellarg(DB_HOST),
                escapeshellarg(DB_PORT),
                escapeshellarg(DB_NAME),
                escapeshellarg($backup['file_path'])
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                Database::execute(
                    "INSERT INTO activity_logs (user_id, role_id, action, module, description, ip_address) 
                     VALUES (?, ?, 'restore', 'settings', 'Merestore database dari backup: {$backup['file_name']}', ?)",
                    [Session::get('user_id'), Session::get('user_role_id'), $_SERVER['REMOTE_ADDR']]
                );

                $this->success(null, 'Database berhasil direstore.');
            } else {
                throw new Exception('mysql command failed');
            }
        } catch (Exception $e) {
            $this->error('Gagal merestore database: ' . $e->getMessage(), 500);
        }
    }
}

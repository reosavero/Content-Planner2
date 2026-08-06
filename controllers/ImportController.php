<?php










class ImportController extends Controller
{
    


    public function index(): void
    {
        
        $importLogs = Database::fetchAll(
            "SELECT il.*, u.name as user_name 
             FROM import_logs il 
             LEFT JOIN users u ON u.id = il.user_id 
             ORDER BY il.created_at DESC 
             LIMIT 20"
        );

        $this->view('import/index', [
            'title' => 'Import Excel Timeline',
            'importLogs' => $importLogs,
            'breadcrumbs' => [
                ['label' => 'Import', 'url' => '#'],
            ],
        ]);
    }

    


    public function upload(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/import');
        }

        
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            $this->redirectWith('/import', 'error', 'Gagal mengupload file. Silakan coba lagi.');
        }

        $file = $_FILES['excel_file'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmp = $file['tmp_name'];

        
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls'])) {
            $this->redirectWith('/import', 'error', 'Format file harus .xlsx atau .xls');
        }

        
        if ($fileSize > 10485760) {
            $this->redirectWith('/import', 'error', 'Ukuran file maksimal 10MB');
        }

        
        $uploadDir = UPLOADS_PATH . 'import/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $storedName = 'import_' . date('Ymd_His') . '_' . Security::generateToken(16) . '.' . $ext;
        $storedPath = $uploadDir . $storedName;
        move_uploaded_file($fileTmp, $storedPath);

        try {
            
            require_once HELPERS_PATH . 'Excel.php';
            $data = Excel::read($storedPath);
            
            $totalImported = 0;
            $totalSkipped = 0;
            $totalErrors = 0;
            $errors = [];

            
            foreach ($data as $sheetName => $rows) {
                if (empty($rows)) continue;

                
                if (stripos($sheetName, 'CONTENT PLAN') !== false || stripos($sheetName, 'Content Plan') !== false) {
                    $result = $this->importContentPlan($rows, $storedName, $sheetName);
                    $totalImported += $result['imported'];
                    $totalSkipped += $result['skipped'];
                    if (!empty($result['errors'])) {
                        $totalErrors += count($result['errors']);
                        $errors = array_merge($errors, $result['errors']);
                    }
                }
                
                elseif (stripos($sheetName, 'UPLOAD') !== false) {
                    $result = $this->importUploadSchedule($rows, $storedName, $sheetName);
                    $totalImported += $result['imported'];
                    $totalSkipped += $result['skipped'];
                }
                
                elseif (stripos($sheetName, 'MEDSOS') !== false) {
                    $result = $this->importMedsosSchedule($rows, $storedName, $sheetName);
                    $totalImported += $result['imported'];
                    $totalSkipped += $result['skipped'];
                }
            }

            
            $status = $totalErrors > 0 ? 'partial' : 'success';
            $errorMsg = !empty($errors) ? implode('; ', array_slice($errors, 0, 5)) : null;

            Database::insert(
                "INSERT INTO import_logs (user_id, file_name, file_size, total_rows, imported_rows, skipped_rows, error_rows, status, error_message)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    Session::get('user_id'),
                    $fileName,
                    $fileSize,
                    $totalImported + $totalSkipped + $totalErrors,
                    $totalImported,
                    $totalSkipped,
                    $totalErrors,
                    $status,
                    $errorMsg
                ]
            );

            
            if ($totalImported > 0) {
                $msg = "Berhasil import {$totalImported} konten dari Excel";
                if ($totalSkipped > 0) $msg .= " ({$totalSkipped} dilewati)";
                if ($totalErrors > 0) $msg .= " ({$totalErrors} error)";
                
                
                $admins = Database::fetchAll(
                    "SELECT id FROM users WHERE role_id IN (SELECT id FROM roles WHERE slug IN ('superadmin','admin')) AND is_active = 1"
                );
                $adminIds = array_column($admins, 'id');
                
                require_once HELPERS_PATH . 'Notification.php';
                Notification::createBulk(
                    $adminIds,
                    'Import Excel Selesai',
                    $msg,
                    $totalErrors > 0 ? 'warning' : 'success',
                    BASE_URL . '/planning',
                    'bi-file-earmark-excel',
                    'import_excel',
                    'Import Excel'
                );

                $this->redirectWith('/planning', 'success', $msg);
            } else {
                $this->redirectWith('/import', 'warning', 'Tidak ada data yang bisa diimport dari file tersebut');
            }

        } catch (Exception $e) {
            
            Database::insert(
                "INSERT INTO import_logs (user_id, file_name, file_size, total_rows, imported_rows, skipped_rows, error_rows, status, error_message)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'failed', ?)",
                [
                    Session::get('user_id'),
                    $fileName,
                    $fileSize,
                    0, 0, 0, 0,
                    $e->getMessage()
                ]
            );
            $this->redirectWith('/import', 'error', 'Gagal import: ' . $e->getMessage());
        }
    }

    


    private function importContentPlan(array $rows, string $sourceFile, string $sheetName): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $userId = Session::get('user_id');

        
        $headerRow = null;
        $headerIndex = -1;
        $colMap = [
            'konten' => 0, 'tgl produksi' => 1, 'hari' => 2,
            'jenis' => 3, 'status' => 4, 'pic' => 5,
            'link materi' => 6, 'link edit' => 7, 'catatan' => 8,
            'referensi' => 9, 'deadline' => 10
        ];

        
        foreach ($rows as $i => $row) {
            $rowText = implode(' ', array_slice($row, 0, 11));
            $rowTextUpper = strtoupper($rowText);
            if (strpos($rowTextUpper, 'KONTEN') !== false && 
                (strpos($rowTextUpper, 'JENIS') !== false || strpos($rowTextUpper, 'STATUS') !== false)) {
                $headerRow = $row;
                $headerIndex = $i;
                break;
            }
        }

        if (!$headerRow) {
            
            foreach ($rows as $i => $row) {
                $filtered = array_filter(array_slice($row, 0, 5), fn($v) => trim($v) !== '');
                if (count($filtered) >= 3) {
                    $headerRow = $row;
                    $headerIndex = $i;
                    break;
                }
            }
        }

        if (!$headerRow) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ['Tidak dapat menemukan header kolom']];
        }

        
        $colIdx = [
            'konten' => 0, 'tgl_produksi' => 1, 'hari' => 2,
            'jenis' => 3, 'status' => 4, 'pic' => 5,
            'link_materi' => 6, 'link_edit' => 7, 'catatan' => 8,
            'referensi' => 9, 'deadline' => 10
        ];

        
        $currentDate = null;
        $currentDay = null;
        $batchSize = 50;
        $batchData = [];

        for ($r = $headerIndex + 1; $r < count($rows); $r++) {
            $row = $rows[$r];
            $konten = trim($row[$colIdx['konten']] ?? '');

            
            if (empty($konten)) continue;

            
            if (stripos($konten, 'Jadwal Harian') !== false) {
                $dateVal = $row[$colIdx['tgl_produksi']] ?? '';
                
                if (!empty($dateVal)) {
                    
                    if (is_numeric($dateVal) && $dateVal > 40000) {
                        $currentDate = Excel::serialToDate($dateVal);
                    } else {
                        $currentDate = date('Y-m-d', strtotime($dateVal));
                    }
                    $currentDay = $row[$colIdx['hari']] ?? '';
                }
                continue;
            }

            
            if (stripos($konten, 'STAND BY') !== false) {
                continue;
            }

            
            if (stripos($konten, 'Jadwal') === 0 && stripos($konten, 'Jadwal Harian') === false) {
                
            }

            
            $jenis = $row[$colIdx['jenis']] ?? '';
            $status = $row[$colIdx['status']] ?? '';
            $pic = $row[$colIdx['pic']] ?? '';
            $linkMateri = $row[$colIdx['link_materi']] ?? '';
            $linkEdit = $row[$colIdx['link_edit']] ?? '';
            $catatan = $row[$colIdx['catatan']] ?? '';
            $referensi = $row[$colIdx['referensi']] ?? '';
            $deadlineVal = $row[$colIdx['deadline']] ?? '';

            
            $deadline = null;
            if (!empty($deadlineVal)) {
                if (is_numeric($deadlineVal) && $deadlineVal > 40000) {
                    $deadline = Excel::serialToDate($deadlineVal);
                } else {
                    $deadline = date('Y-m-d', strtotime($deadlineVal));
                }
                if ($deadline === '1970-01-01') $deadline = null;
            }

            
            $dbStatus = $this->mapStatus($status);

            
            $mediaType = $this->mapMediaType($jenis);

            
            $platformId = $this->mapPlatform($jenis);

            
            $picId = null;
            if (!empty($pic)) {
                $picUser = Database::fetch(
                    "SELECT id FROM users WHERE (name LIKE ? OR username LIKE ?) AND is_active = 1 LIMIT 1",
                    ["%{$pic}%", "%{$pic}%"]
                );
                if ($picUser) $picId = $picUser['id'];
            }

            
            $scheduledAt = null;
            $tanggalPosting = null;
            $jamPosting = null;
            if ($currentDate) {
                $tanggalPosting = $currentDate;
                $jamPosting = '08:00:00';
                $scheduledAt = $currentDate . ' ' . $jamPosting;
            }

            
            $slug = generateSlug(substr($konten, 0, 100)) . '-' . uniqid();
            
            $insertData = [
                'judul' => substr($konten, 0, 255),
                'slug' => $slug,
                'jenis_konten' => $jenis,
                'media_type' => $mediaType,
                'catatan' => $catatan ?: null,
                'tanggal_posting' => $tanggalPosting,
                'jam_posting' => $jamPosting,
                'scheduled_at' => $scheduledAt,
                'deadline' => $deadline,
                'status' => $dbStatus,
                'pic_id' => $picId,
                'priority' => 'medium',
                'created_by' => $userId,
                'group_id' => 'content_plan_' . date('Ym'),
                'group_name' => $sheetName,
                'source_import' => $sourceFile,
                'source_row' => $r + 1,
            ];

            $batchData[] = $insertData;

            
            if (count($batchData) >= $batchSize) {
                $imported += $this->insertBatchContent($batchData);
                $batchData = [];
            }
        }

        
        if (!empty($batchData)) {
            $imported += $this->insertBatchContent($batchData);
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }

    


    private function insertBatchContent(array $batchData): int
    {
        $count = 0;
        foreach ($batchData as $data) {
            try {
                
                $existing = Database::fetch(
                    "SELECT id FROM planning_konten WHERE slug = ? AND deleted_at IS NULL",
                    [$data['slug']]
                );
                if ($existing) {
                    
                    Database::execute(
                        "UPDATE planning_konten SET 
                            judul = ?, jenis_konten = ?, media_type = ?, catatan = ?,
                            tanggal_posting = ?, jam_posting = ?, scheduled_at = ?, deadline = ?,
                            status = ?, pic_id = ?, group_name = ?, updated_at = NOW()
                         WHERE id = ?",
                        [
                            $data['judul'], $data['jenis_konten'], $data['media_type'], $data['catatan'],
                            $data['tanggal_posting'], $data['jam_posting'], $data['scheduled_at'], $data['deadline'],
                            $data['status'], $data['pic_id'], $data['group_name'],
                            $existing['id']
                        ]
                    );
                    $count++;
                    continue;
                }

                
                $planningId = Database::insert(
                    "INSERT INTO planning_konten 
                        (judul, slug, jenis_konten, media_type, catatan, tanggal_posting, jam_posting, 
                         scheduled_at, deadline, status, pic_id, priority, created_by, 
                         group_id, group_name, source_import, source_row, created_at, updated_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                    [
                        $data['judul'], $data['slug'], $data['jenis_konten'], $data['media_type'],
                        $data['catatan'], $data['tanggal_posting'], $data['jam_posting'],
                        $data['scheduled_at'], $data['deadline'], $data['status'],
                        $data['pic_id'], $data['priority'], $data['created_by'],
                        $data['group_id'], $data['group_name'], $data['source_import'], $data['source_row']
                    ]
                );

                
                Database::execute(
                    "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
                     VALUES (?, ?, 'import', 'planning', 'planning_konten', ?, 'Import dari Excel: {$data['judul']}', ?)",
                    [Session::get('user_id'), Session::get('user_role_id'), $planningId, $_SERVER['REMOTE_ADDR']]
                );

                $count++;
            } catch (Exception $e) {
                
                continue;
            }
        }
        return $count;
    }

    


    private function importUploadSchedule(array $rows, string $sourceFile, string $sheetName): array
    {
        
        
        
        return ['imported' => 0, 'skipped' => count($rows)];
    }

    


    private function importMedsosSchedule(array $rows, string $sourceFile, string $sheetName): array
    {
        
        
        return ['imported' => 0, 'skipped' => count($rows)];
    }

    


    private function mapStatus(string $status): string
    {
        $map = [
            'Selesai' => 'success',
            'Publish' => 'success',
            'Proses' => 'draft',
            'Belum Selesai' => 'draft',
            'pending' => 'draft',
            'review' => 'review',
            'approved' => 'approved',
            'revisi' => 'revision',
            'dibatalkan' => 'cancelled',
        ];
        return $map[$status] ?? 'draft';
    }

    


    private function mapMediaType(string $jenis): string
    {
        $jenisUpper = strtoupper($jenis);
        if (strpos($jenisUpper, 'REELS') !== false) return 'reels';
        if (strpos($jenisUpper, 'VIDEO') !== false) return 'video';
        if (strpos($jenisUpper, 'FEED') !== false) return 'image';
        if (strpos($jenisUpper, 'STORY') !== false) return 'story';
        if (strpos($jenisUpper, 'THUMBNAIL') !== false) return 'image';
        if (strpos($jenisUpper, 'FLYER') !== false) return 'image';
        if (strpos($jenisUpper, 'POSTER') !== false) return 'image';
        if (strpos($jenisUpper, 'SKRIP') !== false) return 'text';
        if (strpos($jenisUpper, 'TAKE') !== false) return 'video';
        return 'image';
    }

    


    private function mapPlatform(string $jenis): ?int
    {
        $jenisUpper = strtoupper($jenis);
        $platforms = [
            'facebook' => ['FACEBOOK', 'FB'],
            'instagram' => ['INSTAGRAM', 'IG', 'FEED', 'REELS', 'STORY'],
            'youtube' => ['YOUTUBE', 'YT', 'SHORTS'],
            'tiktok' => ['TIKTOK', 'TT'],
            'twitter' => ['TWITTER', 'X'],
            'threads' => ['THREADS'],
        ];

        foreach ($platforms as $slug => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($jenisUpper, $keyword) !== false) {
                    $platform = Database::fetch(
                        "SELECT id FROM platform_sosmed WHERE slug = ? AND is_active = 1 LIMIT 1",
                        [$slug]
                    );
                    return $platform ? (int)$platform['id'] : null;
                }
            }
        }
        return null;
    }

    


    public function downloadTemplate(): void
    {
        require_once HELPERS_PATH . 'Excel.php';

        $sheets = [
            'CONTENT PLAN JULI' => [
                ['KONTEN', 'TGL PRODUKSI', 'HARI', 'JENIS', 'STATUS', 'PIC', 'LINK MATERI', 'LINK EDIT', 'CATATAN', 'REFERENSI', 'DEADLINE'],
                ['Contoh: Berita JHI', '2026-07-01', 'Rabu', 'Reels Berita', 'Selesai', 'Devan', 'https://...', 'https://...', 'Catatan', 'Referensi', '2026-07-01'],
            ],
            'CONTENT UPLOAD JULI' => [
                ['HARI', 'TANGGAL', 'FEED', 'REELS', 'STORY', 'NAMA / JUDUL KONTEN', 'STATUS', 'JAM POSTING'],
                ['Rabu', '2026-07-01', 'true', 'true', 'true', 'Konten Contoh', 'Selesai', '08.00 - 18.00'],
            ],
        ];

        $filePath = UPLOADS_PATH . 'template_content_plan.xlsx';
        Excel::write($sheets, $filePath);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="template_content_plan.xlsx"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        unlink($filePath);
        exit;
    }

    


    public function export(): void
    {
        require_once HELPERS_PATH . 'Excel.php';

        
        $userId = Session::get('user_id');
        $roleSlug = Session::get('user_role_slug');
        
        $where = "pk.deleted_at IS NULL";
        $params = [];

        if ($roleSlug === 'magang') {
            $where .= " AND pk.created_by = ?";
            $params[] = $userId;
        }

        $plannings = Database::fetchAll(
            "SELECT pk.*, 
                    p.name as platform_name,
                    creator.name as creator_name,
                    pic.name as pic_name
             FROM planning_konten pk
             LEFT JOIN platform_sosmed p ON p.id = pk.platform_id
             LEFT JOIN users creator ON creator.id = pk.created_by
             LEFT JOIN users pic ON pic.id = pk.pic_id
             WHERE {$where}
             ORDER BY pk.tanggal_posting ASC, pk.jam_posting ASC",
            $params
        );

        
        $contentPlan = [['KONTEN', 'TGL PRODUKSI', 'HARI', 'JENIS', 'STATUS', 'PIC', 'LINK MATERI', 'LINK EDIT', 'CATATAN', 'REFERENSI', 'DEADLINE']];
        
        $hariIndo = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
        
        $currentDate = null;
        foreach ($plannings as $p) {
            $tgl = $p['tanggal_posting'] ?? '';
            $hari = $tgl ? ($hariIndo[date('l', strtotime($tgl))] ?? '') : '';
            
            
            if ($tgl && $tgl !== $currentDate) {
                $currentDate = $tgl;
                $contentPlan[] = ['Jadwal Harian', $tgl, $hari, 'Story', 'Publish', '', 'EDIT JADWAL PROGRAM DISINI', '', '', '', $tgl];
            }
            
            $statusLabel = match($p['status']) {
                'draft' => 'Proses',
                'review' => 'Review',
                'approved' => 'Approved',
                'scheduled' => 'Terjadwal',
                'posting' => 'Posting',
                'success' => 'Selesai',
                'failed' => 'Gagal',
                'revision' => 'Revisi',
                'cancelled' => 'Dibatalkan',
                default => $p['status']
            };
            
            $contentPlan[] = [
                $p['judul'],
                $tgl ? '' : '',
                '',
                $p['jenis_konten'] ?? '',
                $statusLabel,
                $p['pic_name'] ?? '',
                '',
                '',
                '',
                '',
                $p['deadline'] ?? ''
            ];
        }

        
        $uploadSheet = [['HARI', 'TANGGAL POSTING', 'JENIS KONTEN', '', '', 'NAMA / JUDUL KONTEN', 'STATUS', 'JAM POSTING']];
        $uploadByDate = [];
        foreach ($plannings as $p) {
            $tgl = $p['tanggal_posting'] ?? '';
            if ($tgl) {
                if (!isset($uploadByDate[$tgl])) {
                    $hari = $hariIndo[date('l', strtotime($tgl))] ?? '';
                    $uploadByDate[$tgl] = [
                        'hari' => $hari,
                        'feed' => false,
                        'reels' => false,
                        'story' => false,
                        'names' => [],
                        'status' => 'Selesai'
                    ];
                }
                $mediaType = $p['media_type'] ?? '';
                if (in_array($mediaType, ['image', 'carousel', 'feed'])) $uploadByDate[$tgl]['feed'] = true;
                if (in_array($mediaType, ['reels', 'video', 'shorts'])) $uploadByDate[$tgl]['reels'] = true;
                if ($mediaType === 'story') $uploadByDate[$tgl]['story'] = true;
                $uploadByDate[$tgl]['names'][] = $p['judul'];
            }
        }

        foreach ($uploadByDate as $tgl => $data) {
            $uploadSheet[] = [
                $data['hari'],
                $tgl,
                $data['feed'] ? 'true' : 'false',
                $data['reels'] ? 'true' : 'false',
                $data['story'] ? 'true' : 'false',
                implode(', ', $data['names']),
                $data['status'],
                '08.00 - 18.00'
            ];
        }

        $sheets = [
            'CONTENT PLAN JULI' => $contentPlan,
            'CONTENT UPLOAD JULI' => $uploadSheet,
        ];

        $fileName = 'TIMELINE ' . strtoupper(date('F Y')) . '.xlsx';
        $filePath = UPLOADS_PATH . 'export/' . $fileName;
        
        $exportDir = UPLOADS_PATH . 'export/';
        if (!is_dir($exportDir)) mkdir($exportDir, 0755, true);

        Excel::write($sheets, $filePath);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        unlink($filePath);
        exit;
    }
}

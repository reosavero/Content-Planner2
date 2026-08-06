<?php






error_reporting(E_ERROR | E_PARSE);


if (!defined('BASE_PATH')) define('BASE_PATH', __DIR__ . '/');
if (!defined('CONTROLLERS_PATH')) define('CONTROLLERS_PATH', BASE_PATH . 'controllers/');
if (!defined('MODELS_PATH')) define('MODELS_PATH', BASE_PATH . 'models/');
if (!defined('HELPERS_PATH')) define('HELPERS_PATH', BASE_PATH . 'helpers/');
if (!defined('MIDDLEWARE_PATH')) define('MIDDLEWARE_PATH', BASE_PATH . 'middleware/');
if (!defined('VIEWS_PATH')) define('VIEWS_PATH', BASE_PATH . 'views/');
if (!defined('UPLOADS_PATH')) define('UPLOADS_PATH', BASE_PATH . 'uploads/');
if (!defined('LOGS_PATH')) define('LOGS_PATH', BASE_PATH . 'logs/');


require BASE_PATH . 'config/database.php';
require BASE_PATH . 'config/app.php';


spl_autoload_register(function (string $class) {
    $paths = [
        CONTROLLERS_PATH,
        MODELS_PATH,
        HELPERS_PATH,
        MIDDLEWARE_PATH,
    ];
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});


function generateSlug(string $text): string
{
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}


function serialToDate(mixed $serial): ?string
{
    if (empty($serial) || !is_numeric($serial)) return null;
    $num = (float)$serial;
    if ($num < 1) return null;
    $unix = ($num - 25569) * 86400;
    return gmdate('Y-m-d', $unix);
}


echo "Reading Excel file...\n";
require HELPERS_PATH . 'Excel.php';
$data = Excel::read(BASE_PATH . 'TIMELINE JULI 2026.xlsx');

if (!isset($data['CONTENT PLAN JULI'])) {
    die("Sheet 'CONTENT PLAN JULI' not found!\n");
}

$rows = $data['CONTENT PLAN JULI'];
echo "Total rows: " . count($rows) . "\n";


$headerRow = $rows[1] ?? [];
echo "Headers: " . implode(' | ', $headerRow) . "\n\n";


$uniqueJenis = [];
$currentDate = null;

for ($i = 2; $i < count($rows); $i++) {
    $row = $rows[$i];
    $konten = trim($row[0] ?? '');
    $jenis = trim($row[3] ?? '');
    
    if (stripos($konten, 'Jadwal Harian') !== false) {
        $currentDate = serialToDate($row[1] ?? '');
        continue;
    }
    if (empty($konten) || stripos($konten, 'STAND BY') !== false || stripos($konten, 'KONTEN') === 0) {
        continue;
    }
    if (!empty($jenis)) {
        $uniqueJenis[$jenis] = true;
    }
}

echo "Unique jenis found: " . count($uniqueJenis) . "\n";
foreach (array_keys($uniqueJenis) as $j) {
    echo "  - $j\n";
}


echo "\nPopulating jenis_konten table...\n";
$jenisMap = [];
$sortOrder = 1;

foreach (array_keys($uniqueJenis) as $jenisName) {
    
    $existing = Database::fetch(
        "SELECT id FROM jenis_konten WHERE name = ?",
        [$jenisName]
    );
    
    if ($existing) {
        $jenisMap[$jenisName] = (int)$existing['id'];
    } else {
        $slug = generateSlug($jenisName);
        $id = Database::insert(
            "INSERT INTO jenis_konten (name, slug, description, is_active, created_at, updated_at)
             VALUES (?, ?, ?, 1, NOW(), NOW())",
            [$jenisName, $slug, "Jenis konten: $jenisName"]
        );
        $jenisMap[$jenisName] = $id;
        echo "  Created: $jenisName (ID: $id)\n";
    }
}


$kategoriMap = [];
$kategoriAll = Database::fetchAll("SELECT id, name, slug FROM kategori_konten WHERE is_active = 1");
foreach ($kategoriAll as $k) {
    $kategoriMap[strtolower($k['name'])] = (int)$k['id'];
    $kategoriMap[strtolower($k['slug'])] = (int)$k['id'];
}


$programMap = [];
$programAll = Database::fetchAll("SELECT id, name, slug FROM program_tv WHERE is_active = 1");
foreach ($programAll as $p) {
    $programMap[strtolower($p['name'])] = (int)$p['id'];
    $programMap[strtolower($p['slug'])] = (int)$p['id'];
}


$defaultUserId = 1;


echo "\nImporting content plan...\n";
$imported = 0;
$skipped = 0;
$errors = [];

$currentDate = null;
$currentDay = null;
$lastKonten = null; 

for ($i = 2; $i < count($rows); $i++) {
    $row = $rows[$i];
    
    
    $konten = trim($row[0] ?? '');
    $tglProduksi = $row[1] ?? '';
    $hari = $row[2] ?? '';
    $jenis = trim($row[3] ?? '');
    $status = trim($row[4] ?? '');
    $pic = trim($row[5] ?? '');
    $linkMateri = trim($row[6] ?? '');
    $linkEdit = trim($row[7] ?? '');
    $catatan = trim($row[8] ?? '');
    $referensi = trim($row[9] ?? '');
    $deadlineVal = $row[10] ?? '';

    
    if (stripos($konten, 'Jadwal Harian') !== false) {
        $currentDate = serialToDate($tglProduksi);
        $currentDay = $hari;
        $lastKonten = null;
        continue;
    }

    
    if (empty($konten) && empty($jenis)) {
        $skipped++;
        continue;
    }

    
    if (stripos($konten, 'STAND BY') !== false) {
        $skipped++;
        continue;
    }

    
    if (stripos($konten, 'KONTEN') === 0 && stripos($jenis, 'JENIS') === 0) {
        $skipped++;
        continue;
    }

    
    if (empty($konten) && !empty($jenis) && $lastKonten) {
        $konten = $lastKonten . ' - ' . $jenis;
    }
    
    if (empty($konten)) {
        $skipped++;
        continue;
    }

    
    if (!empty($konten) && stripos($konten, ' - ') === false) {
        $lastKonten = $konten;
    }

    
    $tanggalPosting = $currentDate;
    $deadline = serialToDate($deadlineVal);

    
    $statusMap = [
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
    $dbStatus = $statusMap[$status] ?? 'draft';

    
    $jenisUpper = strtoupper($jenis);
    if (strpos($jenisUpper, 'REELS') !== false) $mediaType = 'reels';
    elseif (strpos($jenisUpper, 'VIDEO') !== false) $mediaType = 'video';
    elseif (strpos($jenisUpper, 'FEED') !== false) $mediaType = 'image';
    elseif (strpos($jenisUpper, 'STORY') !== false) $mediaType = 'story';
    elseif (strpos($jenisUpper, 'THUMBNAIL') !== false) $mediaType = 'image';
    elseif (strpos($jenisUpper, 'FLYER') !== false || strpos($jenisUpper, 'POSTER') !== false) $mediaType = 'image';
    elseif (strpos($jenisUpper, 'SKRIP') !== false) $mediaType = 'text';
    elseif (strpos($jenisUpper, 'TAKE') !== false) $mediaType = 'video';
    elseif (strpos($jenisUpper, 'CAROUSEL') !== false) $mediaType = 'carousel';
    elseif (strpos($jenisUpper, 'SINGLE POST') !== false) $mediaType = 'image';
    elseif (strpos($jenisUpper, 'IMAGE') !== false) $mediaType = 'image';
    else $mediaType = 'image';

    
    $platformId = null;
    if (stripos($jenis, 'TIKTOK') !== false) $platformId = 4; 
    elseif (stripos($jenis, 'YOUTUBE') !== false || stripos($jenis, 'SHORTS') !== false || stripos($jenis, 'YT') !== false) $platformId = 3; 
    elseif (stripos($jenis, 'FACEBOOK') !== false || stripos($jenis, 'FB') !== false) $platformId = 1; 
    elseif (stripos($jenis, 'INSTAGRAM') !== false || stripos($jenis, 'IG') !== false || stripos($jenis, 'REELS') !== false || stripos($jenis, 'FEED') !== false || stripos($jenis, 'STORY') !== false) $platformId = 2; 
    elseif (stripos($jenis, 'TWITTER') !== false || stripos($jenis, 'THREADS') !== false) $platformId = 5; 

    
    $kategoriId = $kategoriMap['berita terkini'] ?? 1; 
    if (stripos($konten, 'bts') !== false || stripos($konten, 'behind') !== false || stripos($konten, 'proses') !== false) {
        $kategoriId = $kategoriMap['behind the scene'] ?? 8;
    } elseif (stripos($konten, 'budaya') !== false || stripos($konten, 'tradisi') !== false) {
        $kategoriId = $kategoriMap['budaya & tradisi'] ?? 3;
    } elseif (stripos($konten, 'wisata') !== false || stripos($konten, 'kuliner') !== false) {
        $kategoriId = $kategoriMap['wisata & kuliner'] ?? 4;
    } elseif (stripos($konten, 'olahraga') !== false || stripos($konten, 'sport') !== false || stripos($konten, 'pertandingan') !== false) {
        $kategoriId = $kategoriMap['olahraga'] ?? 5;
    } elseif (stripos($konten, 'pendidikan') !== false || stripos($konten, 'edukasi') !== false) {
        $kategoriId = $kategoriMap['pendidikan'] ?? 6;
    } elseif (stripos($konten, 'program') !== false || stripos($konten, 'promo') !== false || stripos($konten, 'teaser') !== false) {
        $kategoriId = $kategoriMap['program tvri'] ?? 7;
    } elseif (stripos($konten, 'info publik') !== false || stripos($konten, 'pengumuman') !== false) {
        $kategoriId = $kategoriMap['info publik'] ?? 2;
    }

    
    $programId = $programMap['jatim hari ini'] ?? 1; 
    if (stripos($konten, 'pojok') !== false) $programId = $programMap['pojok kampung'] ?? 2;
    elseif (stripos($konten, 'sejahtera') !== false) $programId = $programMap['jatim sejahtera'] ?? 3;
    elseif (stripos($konten, 'warung ojo') !== false || stripos($konten, 'kuliner') !== false) $programId = $programMap['warung ojo'] ?? 4;
    elseif (stripos($konten, 'bumi nusantara') !== false || stripos($konten, 'budaya') !== false || stripos($konten, 'wisata') !== false) $programId = $programMap['bumi nusantara'] ?? 5;
    elseif (stripos($konten, 'bersuara') !== false || stripos($konten, 'talkshow') !== false) $programId = $programMap['jatim bersuara'] ?? 6;
    elseif (stripos($konten, 'religi') !== false) $programId = $programMap['religi jatim'] ?? 7;
    elseif (stripos($konten, 'sport') !== false || stripos($konten, 'olahraga') !== false || stripos($konten, 'pertandingan') !== false) $programId = $programMap['jatim sport'] ?? 8;
    elseif (stripos($konten, 'gema pendidikan') !== false || stripos($konten, 'pendidikan') !== false) $programId = $programMap['gema pendidikan'] ?? 9;
    elseif (stripos($konten, 'music') !== false || stripos($konten, 'musik') !== false) $programId = $programMap['jatim music'] ?? 10;

    
    $picId = null;
    if (!empty($pic)) {
        $picUser = Database::fetch(
            "SELECT id FROM users WHERE (name LIKE ? OR username LIKE ?) AND is_active = 1 LIMIT 1",
            ["%{$pic}%", "%{$pic}%"]
        );
        if ($picUser) $picId = (int)$picUser['id'];
    }

    
    $jenisKontenId = $jenisMap[$jenis] ?? null;

    
    $scheduledAt = null;
    $jamPosting = '08:00:00';
    if ($tanggalPosting) {
        $scheduledAt = $tanggalPosting . ' ' . $jamPosting;
    }

    
    $slug = generateSlug(substr($konten, 0, 100)) . '-' . uniqid();

    
    try {
        
        $existing = Database::fetch(
            "SELECT id FROM planning_konten WHERE slug = ? AND deleted_at IS NULL",
            [$slug]
        );
        
        if ($existing) {
            
            Database::execute(
                "UPDATE planning_konten SET 
                    judul = ?, program_id = ?, kategori_id = ?, jenis_konten_id = ?, platform_id = ?,
                    media_type = ?, catatan = ?, 
                    tanggal_posting = ?, jam_posting = ?, scheduled_at = ?, deadline = ?,
                    status = ?, pic_id = ?, group_name = ?, updated_at = NOW()
                 WHERE id = ?",
                [
                    substr($konten, 0, 255), $programId, $kategoriId, $jenisKontenId, $platformId,
                    $mediaType, $catatan ?: null,
                    $tanggalPosting, $jamPosting, $scheduledAt, $deadline,
                    $dbStatus, $picId, 'Content Plan Juli', $existing['id']
                ]
            );
        } else {
            
            $planningId = Database::insert(
                "INSERT INTO planning_konten 
                    (judul, slug, program_id, kategori_id, jenis_konten_id, platform_id, 
                     media_type, catatan, 
                     tanggal_posting, jam_posting, scheduled_at, deadline, 
                     status, pic_id, priority, created_by, 
                     group_id, group_name, source_import, source_row, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                [
                    substr($konten, 0, 255), $slug, $programId, $kategoriId, $jenisKontenId, $platformId,
                    $mediaType, $catatan ?: null,
                    $tanggalPosting, $jamPosting, $scheduledAt, $deadline,
                    $dbStatus, $picId, 'medium', $defaultUserId,
                    'content_plan_' . date('Ym'), 'Content Plan Juli', 'TIMELINE JULI 2026.xlsx', $i + 1
                ]
            );

            
            Database::execute(
                "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
                 VALUES (?, ?, 'import', 'planning', 'planning_konten', ?, 'Import dari Excel: {$konten}', '127.0.0.1')",
                [$defaultUserId, 1, $planningId]
            );
        }
        $imported++;
    } catch (Exception $e) {
        $errors[] = "Row $i ($konten): " . $e->getMessage();
        $skipped++;
    }

    
    if ($imported % 50 === 0) {
        echo "Imported: $imported, Skipped: $skipped\n";
    }
}

echo "\n=== IMPORT COMPLETE ===\n";
echo "Imported: $imported\n";
echo "Skipped: $skipped\n";
echo "Errors: " . count($errors) . "\n";

if (!empty($errors)) {
    echo "\nErrors:\n";
    foreach (array_slice($errors, 0, 10) as $err) {
        echo "  - $err\n";
    }
}


try {
    Database::insert(
        "INSERT INTO import_logs (user_id, file_name, file_size, total_rows, imported_rows, skipped_rows, error_rows, status, error_message, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'success', ?, NOW())",
        [$defaultUserId, 'TIMELINE JULI 2026.xlsx', filesize(BASE_PATH . 'TIMELINE JULI 2026.xlsx'), $imported + $skipped, $imported, $skipped, count($errors), implode('; ', array_slice($errors, 0, 5))]
    );
} catch (Exception $e) {
    
}

echo "\nDone!\n";

<style>
.import-dropzone {
    border: 2px dashed var(--border);
    border-radius: var(--radius-lg);
    padding: 60px 40px;
    text-align: center;
    transition: all var(--transition-normal);
    cursor: pointer;
    background: var(--surface);
}
.import-dropzone:hover,
.import-dropzone.dragover {
    border-color: var(--tvri-blue);
    background: var(--tvri-blue-50);
    box-shadow: var(--shadow-floating);
}
.import-dropzone-icon {
    font-size: 3rem;
    color: var(--tvri-blue);
    margin-bottom: 16px;
}
.import-dropzone p {
    color: var(--text-secondary);
    margin: 8px 0;
    font-size: var(--text-md);
}
.import-dropzone .btn {
    margin-top: 12px;
}
.import-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--space-4);
    margin-top: var(--space-6);
}
.import-info-card {
    background: var(--surface-secondary);
    border-radius: var(--radius-md);
    padding: var(--space-4);
    border: 1px solid var(--border-light);
}
.import-info-card h6 {
    font-size: var(--text-sm);
    color: var(--text-tertiary);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 8px;
}
.import-info-card p {
    font-size: var(--text-md);
    color: var(--text-primary);
    margin: 0;
}
.file-name-display {
    display: none;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-3) var(--space-4);
    background: var(--success-50);
    border-radius: var(--radius-md);
    border: 1px solid var(--success-100);
    margin-top: var(--space-3);
}
.file-name-display.show {
    display: flex;
}
.file-name-display i {
    font-size: 1.5rem;
    color: var(--success);
}
</style>

<div class="row stagger">
    <div class="col-12 col-lg-8">
        
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-file-earmark-excel" style="color: #217346;"></i> Import Excel Timeline</h5>
                <a href="<?= BASE_URL ?>/planning" class="btn btn-sm btn-ghost">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/import/upload" enctype="multipart/form-data" id="importForm">
                    <?= Session::csrfField() ?>
                    
                    <div class="import-dropzone" id="dropzone" onclick="document.getElementById('fileInput').click()">
                        <div class="import-dropzone-icon">
                            <i class="bi bi-file-earmark-arrow-up"></i>
                        </div>
                        <h5>Upload File Excel Timeline</h5>
                        <p>Drag & drop file .xlsx atau klik untuk memilih file</p>
                        <p style="font-size: var(--text-xs); color: var(--text-tertiary);">
                            Format: TIMELINE JULI 2026.xlsx (maks 10MB)
                        </p>
                        <input type="file" name="excel_file" id="fileInput" 
                               accept=".xlsx,.xls" style="display: none;"
                               onchange="handleFileSelect(this)">
                        <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                            <i class="bi bi-folder2-open"></i> Pilih File
                        </button>
                    </div>

                    
                    <div class="file-name-display" id="fileDisplay">
                        <i class="bi bi-file-earmark-excel"></i>
                        <div style="flex: 1;">
                            <div style="font-weight: var(--font-weight-medium); font-size: var(--text-sm);" id="fileName"></div>
                            <div style="font-size: var(--text-xs); color: var(--text-tertiary);" id="fileSize"></div>
                        </div>
                        <button type="button" class="btn btn-sm btn-ghost" onclick="removeFile()">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>

                    
                    <div style="margin-top: var(--space-5); padding: var(--space-4); background: var(--surface-tertiary); border-radius: var(--radius-md);">
                        <h6 style="font-size: var(--text-sm); margin-bottom: var(--space-3);">
                            <i class="bi bi-gear"></i> Opsi Import
                        </h6>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <label class="form-checkbox">
                                    <input type="checkbox" name="overwrite_existing" value="1" checked>
                                    <span>Update konten yang sudah ada (berdasarkan judul)</span>
                                </label>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-checkbox">
                                    <input type="checkbox" name="create_schedule" value="1" checked>
                                    <span>Buat jadwal scheduler otomatis</span>
                                </label>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-checkbox">
                                    <input type="checkbox" name="notify_pic" value="1" checked>
                                    <span>Kirim notifikasi ke PIC terkait</span>
                                </label>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-checkbox">
                                    <input type="checkbox" name="import_upload" value="1">
                                    <span>Import jadwal upload (sheet CONTENT UPLOAD)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: var(--space-4); display: flex; gap: var(--space-3);">
                        <button type="submit" class="btn btn-primary btn-lg" id="importBtn" disabled>
                            <i class="bi bi-upload"></i> Import Sekarang
                        </button>
                        <a href="<?= BASE_URL ?>/import/template" class="btn btn-outline btn-lg">
                            <i class="bi bi-download"></i> Download Template
                        </a>
                    </div>
                </form>
            </div>
        </div>

        
        <div class="card mt-4">
            <div class="card-header">
                <h5><i class="bi bi-info-circle" style="color: var(--tvri-blue);"></i> Format Excel yang Didukung</h5>
            </div>
            <div class="card-body">
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sheet</th>
                                <th>Fungsi</th>
                                <th>Kolom Wajib</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>CONTENT PLAN</code></td>
                                <td>Data planning konten utama</td>
                                <td>KONTEN, JENIS, STATUS, PIC</td>
                            </tr>
                            <tr>
                                <td><code>CONTENT UPLOAD</code></td>
                                <td>Jadwal upload ke platform</td>
                                <td>TANGGAL, FEED, REELS, STORY</td>
                            </tr>
                            <tr>
                                <td><code>CONTENT MEDSOS</code></td>
                                <td>Jadwal TikTok & FB</td>
                                <td>TANGGAL, JENIS KONTEN</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-clock-history"></i> Riwayat Import</h5>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($importLogs)): ?>
                    <div style="max-height: 480px; overflow-y: auto;">
                        <?php foreach ($importLogs as $log): ?>
                            <div style="padding: var(--space-3) var(--space-5); border-bottom: 1px solid var(--border-light);">
                                <div style="display: flex; align-items: center; gap: var(--space-2);">
                                    <span class="status-dot status-<?= $log['status'] === 'success' ? 'success' : ($log['status'] === 'partial' ? 'warning' : 'danger') ?>"></span>
                                    <span style="font-weight: var(--font-weight-medium); font-size: var(--text-sm);">
                                        <?= htmlspecialchars(basename($log['file_name'])) ?>
                                    </span>
                                </div>
                                <div style="font-size: var(--text-xs); color: var(--text-tertiary); margin-top: 4px;">
                                    <?= number_format($log['imported_rows']) ?> konten diimport
                                    <?php if ($log['skipped_rows'] > 0): ?>, <?= $log['skipped_rows'] ?> dilewati<?php endif; ?>
                                    · <?= waktuLalu($log['created_at']) ?>
                                </div>
                                <div style="font-size: var(--text-xs); color: var(--text-tertiary);">
                                    oleh <?= htmlspecialchars($log['user_name'] ?? 'System') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <h5>Belum Ada Import</h5>
                        <p>Riwayat import Excel akan muncul di sini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="card mt-4">
            <div class="card-header">
                <h5><i class="bi bi-bar-chart"></i> Statistik Konten</h5>
            </div>
            <div class="card-body">
                <?php
                $stats = Database::fetchAll(
                    "SELECT group_name, COUNT(*) as total,
                            SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as done
                     FROM planning_konten 
                     WHERE deleted_at IS NULL AND group_id IS NOT NULL
                     GROUP BY group_name
                     ORDER BY MAX(created_at) DESC
                     LIMIT 5"
                );
                ?>
                <?php if (!empty($stats)): ?>
                    <?php foreach ($stats as $s): ?>
                        <div style="margin-bottom: var(--space-3);">
                            <div style="display: flex; justify-content: space-between; font-size: var(--text-xs); margin-bottom: 4px;">
                                <span style="color: var(--text-secondary);"><?= htmlspecialchars(substr($s['group_name'], 0, 30)) ?></span>
                                <span style="font-weight: var(--font-weight-medium);"><?= $s['done'] ?>/<?= $s['total'] ?></span>
                            </div>
                            <div class="progress-bar" style="height: 6px;">
                                <div class="progress-bar-fill" style="width: <?= $s['total'] > 0 ? ($s['done']/$s['total']*100) : 0 ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="font-size: var(--text-xs); color: var(--text-tertiary);">Belum ada data grup</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function handleFileSelect(input) {
    const file = input.files[0];
    if (!file) return;
    
    const display = document.getElementById('fileDisplay');
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent = formatFileSize(file.size);
    display.classList.add('show');
    document.getElementById('importBtn').disabled = false;
    
    
    document.querySelector('.import-dropzone').style.borderColor = 'var(--success)';
    document.querySelector('.import-dropzone-icon i').className = 'bi bi-check-circle text-success';
}

function removeFile() {
    document.getElementById('fileInput').value = '';
    document.getElementById('fileDisplay').classList.remove('show');
    document.getElementById('importBtn').disabled = true;
    document.querySelector('.import-dropzone').style.borderColor = '';
    document.querySelector('.import-dropzone-icon i').className = 'bi bi-file-earmark-arrow-up';
}

function formatFileSize(bytes) {
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
    if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return bytes + ' bytes';
}


const dropzone = document.getElementById('dropzone');
dropzone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropzone.classList.add('dragover');
});
dropzone.addEventListener('dragleave', () => {
    dropzone.classList.remove('dragover');
});
dropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzone.classList.remove('dragover');
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('fileInput').files = files;
        handleFileSelect({ files: [files[0]] });
    }
});


document.getElementById('importForm').addEventListener('submit', function() {
    const btn = document.getElementById('importBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spinner"></i> Importing...';
});
</script>

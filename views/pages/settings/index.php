<?php
$grouped = $grouped ?? [];
$backups = $backups ?? [];

$sectionMeta = [
    'scheduler' => [
        'title' => 'Penjadwalan Otomatis',
        'icon'  => 'bi-calendar2-check',
        'desc'  => 'Mesin penjadwalan yang memproses task secara otomatis sesuai waktunya.',
    ],
    'auto_post' => [
        'title' => 'Auto Posting',
        'icon'  => 'bi-send',
        'desc'  => 'Pengiriman konten otomatis ke platform media sosial saat jadwal tiba.',
    ],
    'backup' => [
        'title' => 'Backup Otomatis',
        'icon'  => 'bi-shield-check',
        'desc'  => 'Pengamanan data dengan pencadangan database secara berkala.',
    ],
];

$settingMeta = [
    'scheduler_enabled' => [
        'label' => 'Aktifkan Scheduler',
        'hint'  => 'Nyalakan mesin penjadwalan otomatis untuk memproses task terjadwal.',
        'unit'  => '',
    ],
    'scheduler_batch_size' => [
        'label' => 'Jumlah Task per Eksekusi',
        'hint'  => 'Berapa banyak task yang diproses dalam satu kali eksekusi.',
        'unit'  => 'task',
    ],
    'scheduler_retry_delay' => [
        'label' => 'Jeda Antar Percobaan Ulang',
        'hint'  => 'Jeda waktu sebelum sistem mencoba ulang posting yang gagal.',
        'unit'  => 'menit',
    ],
    'auto_post_enabled' => [
        'label' => 'Aktifkan Auto Posting',
        'hint'  => 'Konten otomatis dikirim ke platform saat status task sudah terjadwal.',
        'unit'  => '',
    ],
    'auto_post_interval_seconds' => [
        'label' => 'Interval Antar Posting',
        'hint'  => 'Jeda antara satu posting ke posting berikutnya.',
        'unit'  => 'detik',
    ],
    'auto_post_max_retry' => [
        'label' => 'Maksimal Percobaan Ulang',
        'hint'  => 'Berapa kali sistem mencoba ulang saat posting gagal dikirim.',
        'unit'  => 'kali',
    ],
    'backup_enabled' => [
        'label' => 'Aktifkan Backup Otomatis',
        'hint'  => 'Backup database dibuat otomatis oleh cron sesuai jadwal.',
        'unit'  => '',
    ],
    'backup_retention_days' => [
        'label' => 'Masa Simpan Backup',
        'hint'  => 'Lama file backup disimpan sebelum otomatis dihapus.',
        'unit'  => 'hari',
    ],
];
?>

<div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
    <div>
        <h4 class="fw-bold mb-1 d-flex align-items-center"><i class="bi bi-gear-wide-connected text-primary fs-4" style="margin-right:10px;"></i><span>Pengaturan Sistem</span></h4>
        <p class="text-tertiary mb-0" style="font-size:13px;">Kelola otomatisasi penjadwalan, auto posting, dan keamanan data aplikasi.</p>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-toggles text-primary"></i> Pengaturan Aplikasi</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/settings/update" id="settingsForm">
                    <?= Session::csrfField() ?>

                    <?php foreach ($grouped as $group => $settings): ?>
                        <?php $meta = $sectionMeta[$group] ?? ['title' => ucfirst($group), 'icon' => 'bi-gear', 'desc' => '']; ?>
                        <div style="margin-bottom: 28px; padding-bottom: 28px; <?= $group !== array_key_last($grouped) ? 'border-bottom: 1px solid var(--gray-200);' : '' ?>">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div style="width: 38px; height: 38px; border-radius: 12px; background: rgba(0, 51, 153, 0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="bi <?= $meta['icon'] ?> text-primary" style="font-size: 1.05rem;"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0" style="font-weight: 700; font-size: 0.95rem;"><?= $meta['title'] ?></h6>
                                    <small class="text-tertiary" style="font-size: 0.75rem;"><?= $meta['desc'] ?></small>
                                </div>
                            </div>

                            <?php foreach ($settings as $setting): ?>
                                <?php
                                $key = $setting['key'];
                                $sm = $settingMeta[$key] ?? ['label' => ucwords(str_replace('_', ' ', $key)), 'hint' => $setting['description'] ?? '', 'unit' => ''];
                                ?>
                                <?php if ($setting['type'] === 'boolean'): ?>
                                    <div class="d-flex align-items-center justify-content-between gap-3 py-3" style="border-bottom: 1px solid var(--gray-100);">
                                        <div>
                                            <label class="form-label mb-0" style="font-weight: 600; font-size: 0.875rem; cursor: pointer;"><?= Security::escape($sm['label']) ?></label>
                                            <?php if (!empty($sm['hint'])): ?>
                                                <div class="form-text mb-0" style="font-size: 0.75rem; color: var(--text-tertiary);"><?= Security::escape($sm['hint']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <label class="form-switch mb-0" style="flex-shrink: 0;">
                                            <input type="hidden" name="settings[<?= Security::escape($key) ?>]" value="0">
                                            <input type="checkbox" name="settings[<?= Security::escape($key) ?>]" value="1" <?= $setting['value'] === '1' ? 'checked' : '' ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                <?php else: ?>
                                    <div class="mb-3" style="max-width: 360px;">
                                        <label class="form-label" style="font-weight: 600; font-size: 0.875rem;"><?= Security::escape($sm['label']) ?></label>
                                        <div style="position: relative;">
                                            <input type="number" name="settings[<?= Security::escape($key) ?>]" class="form-control" value="<?= Security::escape($setting['value']) ?>" min="1" style="padding-right: <?= !empty($sm['unit']) ? '64px' : '12px' ?>;">
                                            <?php if (!empty($sm['unit'])): ?>
                                                <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: var(--text-tertiary); pointer-events: none;"><?= $sm['unit'] ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($sm['hint'])): ?>
                                            <div class="form-text" style="font-size: 0.75rem; color: var(--text-tertiary);"><?= Security::escape($sm['hint']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Simpan Pengaturan
                        </button>
                        <small class="text-tertiary" style="font-size: 0.75rem;">Perubahan langsung diterapkan pada proses otomatis.</small>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="bi bi-cloud-arrow-down text-primary"></i> Backup Database</h5>
            </div>
            <div class="card-body">
                <p style="font-size: 0.8125rem; color: var(--text-tertiary);">
                    Buat backup database secara manual untuk keamanan data. File disimpan di folder <code>database/backups</code>.
                </p>
                <form method="POST" action="<?= BASE_URL ?>/settings/backup">
                    <?= Session::csrfField() ?>
                    <button type="submit" class="btn btn-primary w-100" onclick="this.disabled=true; this.innerHTML='<span class=\'spinner\'></span> Memproses...'; this.form.submit();">
                        <i class="bi bi-cloud-arrow-down"></i> Backup Sekarang
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-clock-history text-primary"></i> Riwayat Backup</h5>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($backups)): ?>
                    <div style="max-height: 340px; overflow-y: auto;">
                        <?php foreach ($backups as $backup): ?>
                            <div style="display: flex; align-items: center; gap: 8px; padding: 12px 16px; border-bottom: 1px solid var(--gray-100);">
                                <div style="width: 34px; height: 34px; border-radius: 10px; background: var(--gray-100); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="bi bi-file-earmark-zip" style="color: var(--gray-500);"></i>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 0.75rem; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?= Security::escape($backup['file_name']) ?>
                                    </div>
                                    <div style="font-size: 0.6875rem; color: var(--text-tertiary);">
                                        <?= formatTanggal($backup['created_at'], 'd M Y H:i') ?>
                                        <?php if ($backup['file_size_bytes']): ?> · <?= formatFileSize($backup['file_size_bytes']) ?><?php endif; ?>
                                    </div>
                                </div>
                                <span class="badge badge-<?= $backup['status'] === 'success' ? 'success' : ($backup['status'] === 'failed' ? 'danger' : 'warning') ?>" style="font-size: 0.625rem;">
                                    <?= $backup['status'] === 'success' ? 'Sukses' : ($backup['status'] === 'failed' ? 'Gagal' : 'Proses') ?>
                                </span>
                                <?php if ($backup['status'] === 'success'): ?>
                                    <a href="<?= BASE_URL ?>/settings/backup/download/<?= $backup['id'] ?>" class="btn btn-icon btn-sm btn-outline" title="Download backup">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <button type="button" class="btn btn-icon btn-sm btn-outline" title="Restore backup" onclick="restoreBackup(<?= (int)$backup['id'] ?>, '<?= htmlspecialchars($backup['file_name'], ENT_QUOTES) ?>')">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state py-3">
                        <i class="bi bi-cloud-slash" style="font-size: 2rem;"></i>
                        <p style="font-size: 0.75rem;">Belum ada backup</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function restoreBackup(id, name) {
    if (!confirm('Yakin ingin merestore database dari backup "' + name + '"?\n\nSemua data saat ini akan DIGANTI dengan data pada backup tersebut. Proses ini tidak dapat dibatalkan.')) {
        return;
    }
    var formData = new FormData();
    formData.append('backup_id', id);
    formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= Session::csrfToken() ?>');
    fetch('<?= BASE_URL ?>/settings/restore', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            alert(res.message || (res.success ? 'Restore berhasil.' : 'Restore gagal.'));
            if (res.success) location.reload();
        })
        .catch(function() { alert('Terjadi kesalahan saat merestore.'); });
}
</script>

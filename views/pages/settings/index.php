<?php
$grouped = $grouped ?? [];
$backups = $backups ?? [];
?>

<div class="row">
    <div class="col-12 col-lg-8">
        
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-gear text-primary"></i> Pengaturan Sistem</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/settings/update">
                    <?= Session::csrfField() ?>
                    
                    <?php foreach ($grouped as $group => $settings): ?>
                        <div style="margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid var(--gray-200);">
                            <h6 style="font-weight: 600; color: var(--tvri-blue); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; margin-bottom: 16px;">
                                <?= Security::escape(ucfirst($group)) ?>
                            </h6>
                            
                            <?php foreach ($settings as $setting): ?>
                                <div class="form-group">
                                    <label class="form-label">
                                        <?= Security::escape(ucwords(str_replace('_', ' ', $setting['key']))) ?>
                                        <?php if (!empty($setting['description'])): ?>
                                            <i class="bi bi-info-circle" style="color: var(--gray-400); cursor: help;" title="<?= Security::escape($setting['description']) ?>"></i>
                                        <?php endif; ?>
                                    </label>
                                    
                                    <?php if ($setting['type'] === 'boolean'): ?>
                                        <label class="form-switch">
                                            <input type="hidden" name="settings[<?= Security::escape($setting['key']) ?>]" value="0">
                                            <input type="checkbox" name="settings[<?= Security::escape($setting['key']) ?>]" value="1" <?= $setting['value'] === '1' ? 'checked' : '' ?>>
                                            <span class="slider"></span>
                                            <span style="font-size: 0.8125rem; color: var(--gray-500);">
                                                <?= $setting['value'] === '1' ? 'Aktif' : 'Nonaktif' ?>
                                            </span>
                                        </label>
                                    <?php elseif ($setting['type'] === 'textarea'): ?>
                                        <textarea name="settings[<?= Security::escape($setting['key']) ?>]" class="form-control" rows="3"><?= Security::escape($setting['value']) ?></textarea>
                                    <?php elseif ($setting['type'] === 'number'): ?>
                                        <input type="number" name="settings[<?= Security::escape($setting['key']) ?>]" class="form-control" value="<?= Security::escape($setting['value']) ?>">
                                    <?php else: ?>
                                        <input type="text" name="settings[<?= Security::escape($setting['key']) ?>]" class="form-control" value="<?= Security::escape($setting['value']) ?>">
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($setting['description'])): ?>
                                        <div class="form-text"><?= Security::escape($setting['description']) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Simpan Pengaturan
                    </button>
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
                <form method="POST" action="<?= BASE_URL ?>/settings/backup">
                    <?= Session::csrfField() ?>
                    <p style="font-size: 0.8125rem; color: var(--gray-500);">
                        Buat backup database untuk keamanan data. Backup akan disimpan di folder database/backups.
                    </p>
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
                    <div style="max-height: 300px; overflow-y: auto;">
                        <?php foreach ($backups as $backup): ?>
                            <div style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; border-bottom: 1px solid var(--gray-100);">
                                <i class="bi bi-file-earmark-zip" style="color: var(--gray-400);"></i>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 0.75rem; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?= Security::escape($backup['file_name']) ?>
                                    </div>
                                    <div style="font-size: 0.6875rem; color: var(--gray-400);">
                                        <?= formatTanggal($backup['created_at'], 'd M Y H:i') ?>
                                        <?php if ($backup['file_size_bytes']): ?>
                                            · <?= formatFileSize($backup['file_size_bytes']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="badge badge-<?= $backup['status'] === 'success' ? 'success' : ($backup['status'] === 'failed' ? 'danger' : 'warning') ?>" style="font-size: 0.625rem;">
                                    <?= $backup['status'] ?>
                                </span>
                                <?php if ($backup['status'] === 'success'): ?>
                                    <a href="<?= BASE_URL ?>/settings/backup/download/<?= $backup['id'] ?>" class="btn btn-icon btn-sm btn-outline" title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
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

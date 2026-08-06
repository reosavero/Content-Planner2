<?php




?>
<style>





.sm-header-card {
    border-left: 4px solid var(--tvri-blue);
    background: linear-gradient(135deg, var(--surface) 0%, var(--surface-secondary) 100%);
}


.sm-stats .col-4 {
    flex-basis: 100%;
}
@media (min-width: 640px) {
    .sm-stats .col-4 {
        flex-basis: calc(33.333% - var(--space-5));
    }
}
.sm-stat {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-4) var(--space-5);
    background: var(--surface);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-xs);
    height: 100%;
}
.sm-stat-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--text-lg);
    flex-shrink: 0;
}
.sm-stat-value {
    font-family: var(--font-heading);
    font-size: var(--text-xl);
    font-weight: var(--font-weight-bold);
    line-height: 1.1;
    color: var(--text-primary);
}
.sm-stat-label {
    font-size: var(--text-xs);
    color: var(--text-tertiary);
    font-weight: var(--font-weight-medium);
    margin-top: 2px;
}


.sm-grid .col-12 {
    flex-basis: 100%;
}
@media (min-width: 640px) {
    .sm-grid .col-12 {
        flex-basis: calc(50% - var(--space-5));
    }
}
@media (min-width: 1024px) {
    .sm-grid .col-12 {
        flex-basis: calc(25% - var(--space-5));
    }
}

.sm-card {
    display: flex;
    flex-direction: column;
    height: 100%;
    background: var(--surface);
    border: 1px solid var(--border-light);
    border-top: 4px solid transparent;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-card);
    transition: transform var(--transition-base), box-shadow var(--transition-base), border-color var(--transition-base);
}
.sm-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-floating);
    border-color: var(--border);
}
.sm-card-body {
    display: flex;
    flex-direction: column;
    flex: 1;
    padding: var(--space-5);
}
.sm-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-2);
    margin-bottom: var(--space-4);
}
.sm-card-brand {
    display: flex;
    align-items: center; 
    gap: 14px; 
    min-width: 0;
}
.sm-card-logo {
    width: 42px;
    height: 42px;
    object-fit: contain;
    flex-shrink: 0;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.08));
}
.sm-card-name {
    font-family: var(--font-heading);
    font-size: var(--text-md);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
    margin: 0;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sm-card-head .badge {
    flex-shrink: 0; 
}


.sm-account-box {
    background: var(--surface-tertiary);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
    padding: var(--space-3) var(--space-4);
    min-height: 88px;
    margin-bottom: var(--space-4);
    flex: 1;
}
.sm-account-label {
    font-size: var(--text-xs);
    color: var(--text-tertiary);
    font-weight: var(--font-weight-medium);
    margin-bottom: 2px;
}
.sm-account-name {
    font-size: var(--text-md);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sm-account-meta {
    font-size: var(--text-xs);
    color: var(--text-tertiary);
    margin-top: 4px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.sm-empty-info {
    font-size: var(--text-sm);
    color: var(--text-tertiary);
    display: flex;
    align-items: flex-start;
    gap: var(--space-2);
    padding: var(--space-2) 0;
    line-height: 1.6;
}
.sm-empty-info .bi {
    color: var(--text-tertiary);
    flex-shrink: 0;
    margin-top: 2px;
}


.sm-actions {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}
.btn-soft {
    background: transparent;
    color: var(--tvri-blue);
    border: 1px solid var(--tvri-blue-200);
    width: 100%;
}
.btn-soft:hover {
    background: var(--tvri-blue);
    color: var(--white);
    border-color: var(--tvri-blue);
    box-shadow: 0 4px 12px rgba(0, 51, 153, 0.2);
    transform: translateY(-1px);
}
.btn-soft-danger {
    background: transparent;
    color: var(--danger);
    border: 1px solid var(--danger-200);
    width: 100%;
}
.btn-soft-danger:hover {
    background: var(--danger);
    color: var(--white);
    border-color: var(--danger);
    box-shadow: 0 4px 12px rgba(217, 45, 45, 0.2);
    transform: translateY(-1px);
}


.sm-note {
    background: var(--surface-tertiary);
    border: 1px dashed var(--border);
}


[data-theme="dark"] .sm-card {
    border-color: rgba(255, 255, 255, 0.08);
}
[data-theme="dark"] .sm-card[data-accent="#000000"] {
    border-top-color: #4a4a4a;
}
[data-theme="dark"] .sm-account-box {
    background: rgba(255, 255, 255, 0.04);
    border-color: rgba(255, 255, 255, 0.08);
}
</style>


<div class="card sm-header-card mb-4">
    <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h4 class="mb-1 font-bold d-flex align-items-center gap-2" style="color: var(--text-primary);">
                <i class="bi bi-share-fill" style="color: var(--tvri-blue); font-size: 1.1em;"></i>Integrasi & Koneksi Sosial Media
            </h4>
            <p class="text-tertiary mb-0 text-sm">
                Kelola koneksi akun sosial media resmi TVRI Jawa Timur. Akun terhubung secara global dan digunakan oleh Admin &amp; Superadmin untuk fitur Auto Post &amp; Publish Now.
            </p>
        </div>
        <span class="badge badge-primary px-3 py-2 text-xs">
            <i class="bi bi-shield-check me-1"></i> Akun Global Sistem
        </span>
    </div>
</div>

<?php
$platformList = [
    [
        'slug' => 'instagram',
        'name' => 'Instagram',
        'icon' => 'bi-instagram',
        'color' => '#E4405F',
        'bg' => 'rgba(228, 64, 95, 0.12)',
        'implemented' => true
    ],
    [
        'slug' => 'facebook',
        'name' => 'Facebook',
        'icon' => 'bi-facebook',
        'color' => '#1877F2',
        'bg' => 'rgba(24, 119, 242, 0.12)',
        'implemented' => true
    ],
    [
        'slug' => 'tiktok',
        'name' => 'TikTok',
        'icon' => 'bi-tiktok',
        'color' => '#000000',
        'bg' => 'rgba(0, 0, 0, 0.12)',
        'implemented' => true
    ],
    [
        'slug' => 'youtube',
        'name' => 'YouTube',
        'icon' => 'bi-youtube',
        'color' => '#FF0000',
        'bg' => 'rgba(255, 0, 0, 0.12)',
        'implemented' => true
    ],
];


$totalPlatforms = count($platformList);
$connectedCount = 0;
$pendingCount = 0;
foreach ($platformList as $p) {
    $acc = $connectedAccounts[$p['slug']] ?? null;
    $isCon = !empty($acc) && (int)$acc['is_connected'] === 1 && $acc['token_status'] === 'active';
    if ($isCon) {
        $connectedCount++;
    } elseif ($p['implemented']) {
        $pendingCount++;
    }
}
?>


<div class="row sm-stats">
    <div class="col-4">
        <div class="sm-stat">
            <div class="sm-stat-icon" style="background: var(--tvri-blue-50); color: var(--tvri-blue);">
                <i class="bi bi-share"></i>
            </div>
            <div>
                <div class="sm-stat-value"><?= $totalPlatforms ?></div>
                <div class="sm-stat-label">Total Platform</div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="sm-stat">
            <div class="sm-stat-icon" style="background: var(--success-50); color: var(--success);">
                <i class="bi bi-plug-fill"></i>
            </div>
            <div>
                <div class="sm-stat-value"><?= $connectedCount ?></div>
                <div class="sm-stat-label">Akun Terhubung</div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="sm-stat">
            <div class="sm-stat-icon" style="background: var(--warning-50); color: var(--warning);">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <div class="sm-stat-value"><?= $pendingCount ?></div>
                <div class="sm-stat-label">Belum Terhubung</div>
            </div>
        </div>
    </div>
</div>


<div class="row sm-grid">
    <?php foreach ($platformList as $p): ?>
        <?php
        $slug = $p['slug'];
        $account = $connectedAccounts[$slug] ?? null;
        $isConnected = !empty($account) && (int)$account['is_connected'] === 1 && $account['token_status'] === 'active';
        $isImplemented = $p['implemented'];
        ?>
        <div class="col-12">
            <div class="sm-card" data-accent="<?= $p['color'] ?>" style="border-top-color: <?= $p['color'] ?>;">
                <div class="sm-card-body">

                    
                    <div class="sm-card-head">
                        <div class="sm-card-brand">
                            <div class="sm-card-icon" style="width: 44px; height: 44px; border-radius: var(--radius-md, 10px); background: <?= $p['bg'] ?>; color: <?= $p['color'] ?>; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; flex-shrink: 0;">
                                <i class="bi <?= $p['icon'] ?>"></i>
                            </div>
                            <h5 class="sm-card-name"><?= Security::escape($p['name']) ?></h5>
                        </div>
                        <?php if ($isConnected): ?>
                            <span class="badge badge-success" title="Token API aktif">
                                <i class="bi bi-check-circle-fill me-1"></i> Terhubung
                            </span>
                        <?php elseif ($isImplemented): ?>
                            <span class="badge" style="background: var(--gray-100); color: var(--gray-500);">
                                Belum Terhubung
                            </span>
                        <?php else: ?>
                            <span class="badge badge-warning">
                                <i class="bi bi-hourglass-split me-1"></i> Segera Hadir
                            </span>
                        <?php endif; ?>
                    </div>

                    
                    <div class="sm-account-box">
                        <?php if ($isConnected): ?>
                            <div class="sm-account-label">Akun Terhubung</div>
                            <div class="sm-account-name"><?= Security::escape($account['account_username'] ?: $account['account_name']) ?></div>
                            <div class="sm-account-meta">
                                <i class="bi bi-person-check"></i> Oleh: <?= Security::escape($account['connected_by_name'] ?? 'Admin') ?>
                            </div>
                        <?php elseif ($isImplemented): ?>
                            <div class="sm-empty-info">
                                <i class="bi bi-info-circle"></i>
                                <span>Belum ada akun <?= Security::escape($p['name']) ?> terhubung ke sistem.</span>
                            </div>
                        <?php else: ?>
                            <div class="sm-empty-info">
                                <i class="bi bi-clock"></i>
                                <span>Modul API <?= Security::escape($p['name']) ?> siap dikembangkan untuk tahap berikutnya.</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    
                    <div class="sm-actions">
                        <?php if ($isConnected): ?>
                            <a href="<?= BASE_URL ?>/social-media/<?= $slug ?>/connect" class="btn btn-soft btn-sm">
                                <i class="bi bi-arrow-repeat me-1"></i> Hubungkan Ulang
                            </a>
                            <button type="button" onclick="disconnectPlatform('<?= $slug ?>', '<?= Security::escape($p['name']) ?>')" class="btn btn-soft-danger btn-sm">
                                <i class="bi bi-plug-fill me-1"></i> Putuskan Koneksi
                            </button>
                        <?php elseif ($isImplemented): ?>
                            <a href="<?= BASE_URL ?>/social-media/<?= $slug ?>/connect" class="btn btn-primary btn-sm">
                                <i class="bi bi-link-45deg me-1"></i> Hubungkan <?= Security::escape($p['name']) ?>
                            </a>
                        <?php else: ?>
                            <button type="button" disabled class="btn btn-sm" style="background: var(--surface-tertiary); color: var(--text-tertiary); border: 1px dashed var(--border); width: 100%; cursor: not-allowed;">
                                <i class="bi bi-lock me-1"></i> Belum Diimplementasikan
                            </button>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>


<div class="card sm-note">
    <div class="card-body d-flex align-items-start gap-3">
        <i class="bi bi-info-circle-fill" style="color: var(--info); font-size: var(--text-lg); flex-shrink: 0; margin-top: 1px;"></i>
        <div class="text-sm text-secondary">
            <span class="font-semibold">Catatan:</span> Koneksi akun bersifat <span class="font-semibold">global sistem</span> — satu akun sosial media yang terhubung akan dipakai untuk seluruh proses Auto Post &amp; Publish Now, sehingga hanya Admin &amp; Superadmin yang dapat mengelola koneksi ini. Proses menghubungkan akun akan membuka halaman otorisasi resmi dari masing-masing platform.
        </div>
    </div>
</div>

<script>
function disconnectPlatform(slug, name) {
    if (!confirm(`Apakah Anda yakin ingin memutuskan koneksi akun ${name}? Konten yang ada tidak akan terhapus.`)) {
        return;
    }

    $.ajax({
        url: BASE_URL + `/social-media/${slug}/disconnect`,
        type: 'POST',
        data: {
            <?= CSRF_TOKEN_NAME ?>: CSRF_TOKEN
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                window.location.reload();
            } else {
                alert('Gagal: ' + (response.message || 'Terjadi kesalahan'));
            }
        },
        error: function(xhr) {
            alert('Error HTTP ' + xhr.status + ': ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem'));
        }
    });
}
</script>

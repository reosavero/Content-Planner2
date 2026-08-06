<?php
$accounts = $accounts ?? [];
$platforms = $platforms ?? [];
?>
<style>
.platform-card {
    display: flex;
    align-items: center;
    gap: 16px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 16px 20px;
    transition: all var(--transition-normal);
}
.platform-card:hover {
    border-color: var(--tvri-blue);
    box-shadow: var(--shadow-floating);
}
.platform-card-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
}
.platform-card-info { flex: 1; min-width: 0; }
.platform-card-name { font-weight: 600; font-size: var(--text-md); }
.platform-card-username { font-size: var(--text-sm); color: var(--text-tertiary); }
</style>


<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Akun Terhubung</h6>
        <div class="dropdown-trigger">
            <button class="btn btn-primary btn-sm" onclick="toggleDropdown(this.nextElementSibling)">
                <i class="bi bi-plus"></i> Hubungkan Akun
            </button>
            <div class="dropdown-menu dropdown-menu-end">
                <?php foreach ($platforms as $p): ?>
                    <a href="<?= BASE_URL ?>/platform-accounts/connect/<?= $p['slug'] ?>" class="dropdown-item">
                        <i class="<?= iconClass($p['icon'] ?? 'bi-globe') ?>" style="color:<?= $p['color'] ?? 'var(--text-secondary)' ?>"></i>
                        <?= htmlspecialchars($p['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($accounts)): ?>
            <div class="empty-state">
                <i class="bi bi-link-45deg" style="font-size:2rem;color:var(--text-tertiary);"></i>
                <p style="color:var(--text-tertiary);margin-top:8px;">Belum ada akun terhubung</p>
                <p class="text-xs text-tertiary">Klik "Hubungkan Akun" untuk menambahkan akun sosial media</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($accounts as $acc): ?>
                    <div class="col-md-6">
                        <div class="platform-card">
                            <div class="platform-card-icon" style="background:<?= $acc['platform_color'] ?? '#003399' ?>;">
                                <i class="<?= iconClass($acc['platform_icon'] ?? 'bi-globe') ?>"></i>
                            </div>
                            <div class="platform-card-info">
                                <div class="platform-card-name"><?= htmlspecialchars($acc['account_name'] ?? '-') ?></div>
                                <div class="platform-card-username">@<?= htmlspecialchars($acc['account_username'] ?? '-') ?></div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="badge <?= ($acc['is_connected'] ?? 0) ? 'badge-success' : 'badge-secondary' ?>" style="font-size:10px;">
                                        <?= ($acc['is_connected'] ?? 0) ? 'Terhubung' : 'Putus' ?>
                                    </span>
                                    <span class="badge badge-soft" style="font-size:10px;"><?= htmlspecialchars($acc['platform_name']) ?></span>
                                    <?php if (!empty($acc['pic_name'])): ?>
                                        <small class="text-tertiary">PIC: <?= htmlspecialchars($acc['pic_name']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-ghost" onclick="syncAccount(<?= $acc['id'] ?>)" title="Sinkron">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                                <?php if ($acc['is_connected']): ?>
                                    <button class="btn btn-sm btn-ghost text-danger" onclick="if(confirm('Putuskan akun ini?')){disconnectAccount(<?= $acc['id'] ?>)}" title="Putuskan">
                                        <i class="bi bi-link-45deg"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function syncAccount(id) {
    window.location.href = '<?= BASE_URL ?>/platform-accounts/' + id + '/sync';
}

function disconnectAccount(id) {
    var form = new FormData();
    form.append('<?= CSRF_TOKEN_NAME ?>', '<?= Session::csrfToken() ?>');
    fetch('<?= BASE_URL ?>/platform-accounts/' + id + '/disconnect', { method: 'POST', body: form })
        .then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); })
        .catch(() => location.reload());
}
</script>

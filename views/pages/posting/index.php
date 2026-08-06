<?php




$logs = $logs ?? [];
$platforms = $platforms ?? [];
$pagination = $pagination ?? [];
$filters = $filters ?? [];
?>
<style>
.log-status-badge {
    padding: 6px 12px;
    font-size: 0.78rem;
    font-weight: 600;
    border-radius: var(--radius-md, 6px);
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.log-status-success {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.25);
}
.log-status-failed {
    background: rgba(239, 68, 68, 0.12);
    color: #dc2626;
    border: 1px solid rgba(239, 68, 68, 0.25);
}
.log-status-pending {
    background: rgba(245, 158, 11, 0.12);
    color: #d97706;
    border: 1px solid rgba(245, 158, 11, 0.25);
}
.platform-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
    font-size: 0.85rem;
}
.error-box {
    background: var(--surface-tertiary, #f8fafc);
    border-left: 3px solid #dc2626;
    padding: 6px 10px;
    font-size: 0.78rem;
    color: #991b1b;
    border-radius: 4px;
    max-width: 280px;
    word-break: break-word;
}
</style>


<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/posting" id="filterForm">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <div class="form-group mb-0">
                        <label class="form-label font-semibold text-xs">Cari Judul / Akun / Error</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari postingan..." value="<?= Security::escape($filters['search'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="form-group mb-0">
                        <label class="form-label font-semibold text-xs">Filter Status Log</label>
                        <select name="status" class="form-control" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="success" <?= ($filters['status'] ?? '') === 'success' ? 'selected' : '' ?>>✓ Berhasil (Success)</option>
                            <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>✕ Gagal (Failed)</option>
                            <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>⏳ Dalam Proses</option>
                        </select>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="form-group mb-0">
                        <label class="form-label font-semibold text-xs">Filter Platform</label>
                        <select name="platform" class="form-control" onchange="this.form.submit()">
                            <option value="">Semua Platform</option>
                            <?php foreach ($platforms as $pf): ?>
                                <option value="<?= $pf['slug'] ?>" <?= ($filters['platform'] ?? '') === $pf['slug'] ? 'selected' : '' ?>>
                                    <?= Security::escape($pf['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-2 text-end">
                    <a href="<?= BASE_URL ?>/posting" class="btn btn-outline btn-sm w-100">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>


<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 font-bold d-flex align-items-center gap-2">
            <i class="bi bi-journal-text" style="color: var(--tvri-blue);"></i> 
            Riwayat Log Auto Post &amp; Publish Now
        </h6>
        <span class="badge badge-secondary">
            Total: <?= (int)($pagination['total_records'] ?? count($logs)) ?> Log
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 140px;">Status Log</th>
                        <th>Judul Konten &amp; Media</th>
                        <th>Platform &amp; Akun</th>
                        <th>Waktu Eksekusi</th>
                        <th>Detail &amp; Link Post</th>
                        <th>Pelaksana</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-journal-x" style="font-size: 2.5rem; color: var(--text-tertiary);"></i>
                                    <p class="text-tertiary mt-2 mb-0 font-medium">Belum ada riwayat log auto post</p>
                                    <small class="text-tertiary">Log postingan dari Scheduler &amp; Publish Now akan otomatis tercatat di sini.</small>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <?php
                            $status = strtolower($log['status'] ?? 'pending');
                            $platformSlug = strtolower($log['platform_slug'] ?? 'social');
                            ?>
                            <tr>
                                
                                <td>
                                    <?php if ($status === 'success'): ?>
                                        <span class="log-status-badge log-status-success">
                                            <i class="bi bi-check-circle-fill"></i> Berhasil
                                        </span>
                                    <?php elseif ($status === 'failed'): ?>
                                        <span class="log-status-badge log-status-failed">
                                            <i class="bi bi-x-circle-fill"></i> Gagal
                                        </span>
                                    <?php else: ?>
                                        <span class="log-status-badge log-status-pending">
                                            <i class="bi bi-hourglass-split"></i> Proses
                                        </span>
                                    <?php endif; ?>
                                </td>

                                
                                <td>
                                    <div class="fw-bold text-dark mb-1" style="font-size: 0.9rem;">
                                        <?= Security::escape($log['judul'] ?? 'Konten #' . $log['planning_id']) ?>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge badge-soft text-xs">
                                            <i class="bi bi-film me-1"></i><?= ucfirst($log['media_type'] ?? 'post') ?>
                                        </span>
                                        <?php if (!empty($log['caption'])): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-secondary py-0 px-2 text-xs rounded-pill" 
                                                    data-judul="<?= htmlspecialchars($log['judul'] ?? 'Caption', ENT_QUOTES, 'UTF-8') ?>"
                                                    data-caption="<?= htmlspecialchars($log['caption'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                    onclick="openCaptionModalFromBtn(this)">
                                                <i class="bi bi-text-paragraph"></i> Lihat Caption
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                
                                <td>
                                    <div class="platform-pill mb-1">
                                        <i class="<?= iconClass($log['platform_icon'] ?? 'bi-share') ?>" style="color: <?= $log['platform_color'] ?? '#003399' ?>; font-size: 1.1rem;"></i>
                                        <span><?= Security::escape($log['platform_name'] ?? ucfirst($platformSlug)) ?></span>
                                    </div>
                                    <?php if (!empty($log['account_name'])): ?>
                                        <div class="text-xs text-tertiary">
                                            <i class="bi bi-at"></i><?= Security::escape($log['account_username'] ?: $log['account_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                
                                <td>
                                    <div class="fw-semibold text-sm">
                                        <?= date('d M Y', strtotime($log['created_at'])) ?>
                                    </div>
                                    <div class="text-xs text-tertiary">
                                        <?= date('H:i:s', strtotime($log['created_at'])) ?> WIB
                                        <?php if (!empty($log['duration_ms'])): ?>
                                            <span class="ms-1">(<?= $log['duration_ms'] ?>ms)</span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                
                                <td>
                                    <?php if ($status === 'success'): ?>
                                        <?php if (!empty($log['post_url'])): ?>
                                            <a href="<?= Security::escape($log['post_url']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-soft text-xs d-inline-flex align-items-center gap-1">
                                                <i class="bi bi-box-arrow-up-right"></i> Lihat Postingan 🔗
                                            </a>
                                        <?php else: ?>
                                            <span class="text-xs text-success font-medium">
                                                <i class="bi bi-check2"></i> Terkirim ke API
                                            </span>
                                        <?php endif; ?>
                                    <?php elseif ($status === 'failed'): ?>
                                        <div class="error-box">
                                            <div class="font-semibold mb-1">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Error:
                                            </div>
                                            <?= Security::escape($log['error_message'] ?? 'Gagal publikasi ke API platform') ?>
                                            <?php if (!empty($log['http_code'])): ?>
                                                <div class="mt-1 font-mono text-xs opacity-75">HTTP Code: <?= (int)$log['http_code'] ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs text-tertiary">Sedang diproses oleh scheduler...</span>
                                    <?php endif; ?>
                                </td>

                                
                                <td>
                                    <div class="text-sm font-medium text-dark">
                                        <?= Security::escape($log['user_name'] ?? 'System Scheduler') ?>
                                    </div>
                                    <div class="text-xs text-tertiary">
                                        IP: <?= Security::escape($log['ip_address'] ?? '127.0.0.1') ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
        <div class="card-footer d-flex justify-content-between align-items-center py-3">
            <div class="text-xs text-tertiary">
                Halaman <?= (int)$pagination['current_page'] ?> dari <?= (int)$pagination['total_pages'] ?>
            </div>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                    <li class="page-item <?= $p === (int)$pagination['current_page'] ? 'active' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>/posting?page=<?= $p ?>&status=<?= urlencode($filters['status'] ?? '') ?>&platform=<?= urlencode($filters['platform'] ?? '') ?>&search=<?= urlencode($filters['search'] ?? '') ?>">
                            <?= $p ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>


<div class="modal fade" id="captionModal" tabindex="-1" aria-hidden="true" onclick="if(event.target===this) closeCaptionModal();">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15);">
            <div class="modal-header border-bottom">
                <h6 class="modal-title fw-bold" id="captionModalTitle">
                    <i class="bi bi-card-text text-primary me-2"></i> Detail Caption Postingan
                </h6>
                <button type="button" class="btn-close" onclick="closeCaptionModal()"></button>
            </div>
            <div class="modal-body">
                <div class="p-3 bg-light rounded-3 text-sm text-dark mb-3" id="captionModalText" style="white-space: pre-wrap; word-break: break-word; max-height: 350px; overflow-y: auto; line-height: 1.6; border: 1px solid #cbd5e1; font-family: inherit;">
                </div>
            </div>
            <div class="modal-footer border-top d-flex justify-content-between">
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="copyCaptionText()">
                    <i class="bi bi-clipboard me-1"></i> Salin Caption
                </button>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" onclick="closeCaptionModal()">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function esc(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function openCaptionModalFromBtn(btn) {
    var title = btn.getAttribute('data-judul') || '';
    var caption = btn.getAttribute('data-caption') || '';
    
    document.getElementById('captionModalTitle').innerHTML = '<i class="bi bi-card-text text-primary me-2"></i> Caption: ' + esc(title);
    document.getElementById('captionModalText').innerText = caption;
    
    var modalEl = document.getElementById('captionModal');
    if (window.bootstrap && bootstrap.Modal) {
        try {
            var myModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            myModal.show();
            return;
        } catch(e) {}
    }
    
    
    modalEl.style.display = 'flex';
    modalEl.classList.add('show');
    modalEl.style.background = 'rgba(15, 23, 42, 0.6)';
    document.body.style.overflow = 'hidden';
}

function closeCaptionModal() {
    var modalEl = document.getElementById('captionModal');
    if (window.bootstrap && bootstrap.Modal) {
        try {
            var myModal = bootstrap.Modal.getInstance(modalEl);
            if (myModal) {
                myModal.hide();
                return;
            }
        } catch(e) {}
    }
    modalEl.style.display = 'none';
    modalEl.classList.remove('show');
    document.body.style.overflow = '';
}

function copyCaptionText() {
    var text = document.getElementById('captionModalText').innerText;
    navigator.clipboard.writeText(text).then(function() {
        alert('✓ Caption berhasil disalin ke clipboard!');
    });
}
</script>

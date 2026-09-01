<?php
$data = $data ?? [];
$roles = $roles ?? [];
$filters = $filters ?? [];
$pagination = $pagination ?? [];
?>

<div class="card">
    <div class="card-header">
        <h5><i class="bi bi-people" style="color: var(--tvri-blue);"></i> Manajemen User</h5>
        <a href="<?= BASE_URL ?>/users/create" class="btn btn-primary btn-sm">
            <i class="bi bi-plus"></i> Tambah User
        </a>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($data)): ?>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Planning</th>
                            <th>Terakhir Login</th>
                            <th style="width: 80px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $user): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: var(--space-3);">
                                        <img src="<?= !empty($user['avatar']) ? BASE_URL . '/uploads/' . $user['avatar'] : 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect fill=%22%23e4e7ed%22 width=%22100%22 height=%22100%22/%3E%3Ctext x=%2250%22 y=%2265%22 font-size=%2240%22 fill=%22%239ca0a8%22 text-anchor=%22middle%22 font-weight=%22bold%22%3E' . strtoupper(substr($user['name'] ?? 'U', 0, 1)) . '%3C/text%3E%3C/svg%3E' ?>"
                                             alt="" class="avatar">
                                        <div>
                                            <div style="font-weight: var(--font-weight-medium); font-size: var(--text-sm);"><?= htmlspecialchars($user['name'] ?? '') ?></div>
                                            <div style="font-size: var(--text-xs); color: var(--text-tertiary);"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size: var(--text-sm);"><?= htmlspecialchars($user['username'] ?? '') ?></td>
                                <td><span class="badge-status assigned"><i class="bi bi-person-badge"></i> <?= htmlspecialchars($user['role_name'] ?? '') ?></span></td>
                                <td>
                                    <?php if (!empty($user['is_active'])): ?>
                                        <span class="badge-status approved"><i class="bi bi-check-circle-fill"></i> Active</span>
                                    <?php else: ?>
                                        <span class="badge-status revision"><i class="bi bi-x-circle-fill"></i> Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: var(--text-sm);"><?= number_format($user['total_planning'] ?? 0) ?></td>
                                <td style="font-size: var(--text-xs); color: var(--text-tertiary); white-space: nowrap;">
                                    <?= !empty($user['last_login_at']) ? waktuLalu($user['last_login_at']) : '<span style="color: var(--text-tertiary);">Never</span>' ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="<?= BASE_URL ?>/users/<?= $user['id'] ?>/edit" class="btn btn-icon btn-sm btn-ghost" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if ($user['id'] != Session::get('user_id')): ?>
                                            <button class="btn btn-icon btn-sm btn-ghost" style="color: var(--danger);" 
                                                    onclick="deleteUser('<?= $user['id'] ?>', '<?= htmlspecialchars(addslashes($user['name'])) ?>')"
                                                    title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-people"></i>
                <h5>Belum Ada User</h5>
                <p>Tambahkan user baru untuk mulai menggunakan sistem.</p>
            </div>
        <?php endif; ?>
    </div>
</div>


<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px; width: 90%; margin: 1.75rem auto;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; padding: 12px;">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <div style="width: 60px; height: 60px; background: #fee2e2; color: #dc2626; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            <line x1="10" y1="11" x2="10" y2="17"></line>
                            <line x1="14" y1="11" x2="14" y2="17"></line>
                        </svg>
                    </div>
                </div>
                <h5 style="font-weight: 700; color: #1e293b; font-size: 18px; margin-bottom: 8px;">Konfirmasi Hapus</h5>
                <p id="deleteUserMessage" style="font-size: 13.5px; color: #64748b; margin-bottom: 24px; line-height: 1.5;">Apakah Anda yakin ingin menghapus user ini?</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn" onclick="closeDeleteUserModal()" style="flex: 1; height: 42px; font-weight: 600; font-size: 14px; background: #f1f5f9; color: #475569; border: none; border-radius: 10px;">Batal</button>
                    <button type="button" class="btn" id="btnConfirmDeleteUser" onclick="executeDeleteUser()" style="flex: 1; height: 42px; font-weight: 600; font-size: 14px; background: #dc2626; color: #ffffff; border: none; border-radius: 10px;">Hapus User</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge-status {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 600;
    white-space: nowrap;
    flex-shrink: 0;
}
.badge-status.pending { background: #fff3e0; color: #e65100; border: 1px solid #ffe0b2; }
.badge-status.approved { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.badge-status.revision { background: #fff5f5; color: #c53030; border: 1px solid #feb2b2; }
.badge-status.assigned { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.badge-status.progress { background: #faf5ff; color: #7e22ce; border: 1px solid #e9d5ff; }
.badge-status.selesai { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
</style>

<script>
var targetUserDeleteId = null;

function deleteUser(id, name) {
    targetUserDeleteId = id;
    var msgEl = document.getElementById('deleteUserMessage');
    if (msgEl) {
        msgEl.innerHTML = 'Apakah Anda yakin ingin menghapus user <strong>"' + (name || '') + '"</strong>?';
    }

    var modalEl = document.getElementById('deleteUserModal');
    if (!modalEl) return;

    if (window.bootstrap && window.bootstrap.Modal) {
        var modal = window.bootstrap.Modal.getInstance(modalEl) || new window.bootstrap.Modal(modalEl);
        modal.show();
    } else {
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
        document.body.classList.add('modal-open');
    }
}

function closeDeleteUserModal() {
    var modalEl = document.getElementById('deleteUserModal');
    if (modalEl) {
        if (window.bootstrap && window.bootstrap.Modal) {
            var modal = window.bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }
        modalEl.classList.remove('show');
        modalEl.style.display = 'none';
    }
    document.body.classList.remove('modal-open');
    var backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(function(b) { b.remove(); });
}

function executeDeleteUser() {
    if (!targetUserDeleteId) return;

    var btn = document.getElementById('btnConfirmDeleteUser');
    btn.disabled = true;

    var formData = new FormData();
    formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= Session::csrfToken() ?>');

    fetch('<?= BASE_URL ?>/users/' + targetUserDeleteId + '/delete', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        closeDeleteUserModal();
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Gagal menghapus user.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        closeDeleteUserModal();
        window.location.href = '<?= BASE_URL ?>/users/' + targetUserDeleteId + '/delete?csrf_token=<?= Session::csrfToken() ?>';
    });
}
</script>

<div class="container-fluid">
    
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><?= Session::get('user_role_slug') === 'superadmin' ? 'Kelola User' : 'Kelola User Magang' ?></h4>
            <p class="text-muted small mb-0">Kelola dan konfirmasi pendaftaran akun user <?= Session::get('user_role_slug') === 'superadmin' ? 'Admin & Magang' : 'Magang' ?> TVRI Jawa Timur.</p>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <?php if (Session::get('user_role_slug') === 'superadmin'): ?>
                <div class="d-flex align-items-center gap-2">
                    <div class="btn-group shadow-sm" role="group" aria-label="Filter Role">
                        <a href="<?= BASE_URL ?>/intern-users?role=all<?= !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '' ?>" 
                           class="btn btn-sm btn-outline-primary <?= ($filters['role'] ?? 'all') === 'all' ? 'active' : '' ?>">
                            Semua
                        </a>
                        <a href="<?= BASE_URL ?>/intern-users?role=admin<?= !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '' ?>" 
                           class="btn btn-sm btn-outline-primary <?= ($filters['role'] ?? '') === 'admin' ? 'active' : '' ?>">
                            Admin
                        </a>
                        <a href="<?= BASE_URL ?>/intern-users?role=magang<?= !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '' ?>" 
                           class="btn btn-sm btn-outline-primary <?= ($filters['role'] ?? '') === 'magang' ? 'active' : '' ?>">
                            Magang
                        </a>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary shadow-sm" onclick="openAddAdminModal()">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Admin
                    </button>
                </div>
            <?php endif; ?>
            <div style="min-width: 250px; max-width: 320px;">
                <input type="text" id="liveSearchInput" class="form-control shadow-sm" placeholder="Cari nama, email, username, jurusan..." value="<?= htmlspecialchars($filters['search']) ?>" oninput="filterTableLive(this.value)">
            </div>
        </div>
    </div>

    <?php if (!empty($pendingUsers)): ?>
    
    <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid var(--warning, #f59e0b) !important; background: #fffdf7;">
        <div class="card-header bg-transparent border-0 pt-3 pb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-person-plus me-1"></i> Pendaftaran Akun Magang Baru
                </h6>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="padding-left: 68px;">Nama</th>
                            <th>Jurusan</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>NO. HP</th>
                            <th class="text-center" style="width: 220px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingUsers as $pUser): 
                            $avatarUrl = !empty($pUser['avatar']) ? BASE_URL . '/uploads/' . $pUser['avatar'] : null;
                            $initial = mb_strtoupper(mb_substr($pUser['name'] ?? 'M', 0, 1));
                        ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if ($avatarUrl): ?>
                                            <img src="<?= $avatarUrl ?>" alt="<?= htmlspecialchars($pUser['name']) ?>" class="sidebar-user-avatar">
                                        <?php else: ?>
                                            <div class="sidebar-user-avatar sidebar-user-avatar-initials">
                                                <?= $initial ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($pUser['name']) ?></div>
                                            <span class="badge bg-warning text-dark px-2 py-0.5" style="font-size: 11px;">Pending</span>
                                        </div>
                                    </div>
                                </td>
                                <td style="vertical-align: middle;">
                                    <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-bold" style="font-size: 12px;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-right: 4px;">
                                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                                            <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                                        </svg><?= htmlspecialchars($pUser['jurusan'] ?? '-') ?>
                                    </span>
                                </td>
                                <td style="vertical-align: middle;">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1.5 fw-bold" style="font-size: 12.5px;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-right: 4px;">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg><?= htmlspecialchars($pUser['username']) ?>
                                    </span>
                                </td>
                                <td style="vertical-align: middle;">
                                    <div class="small text-dark d-flex align-items-center">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-right: 6px;" class="text-primary">
                                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                            <polyline points="22,6 12,13 2,6"></polyline>
                                        </svg><?= htmlspecialchars($pUser['email']) ?>
                                    </div>
                                </td>
                                <td style="vertical-align: middle;">
                                    <div class="small text-dark d-flex align-items-center">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-right: 6px;" class="text-success">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                        </svg><?= htmlspecialchars($pUser['phone'] ?? '-') ?>
                                    </div>
                                </td>
                                <td class="text-center align-middle" style="min-width: 220px; text-align: center;">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-success shadow-sm d-inline-flex align-items-center gap-1" onclick="confirmApprove('<?= $pUser['id'] ?>', '<?= htmlspecialchars(addslashes($pUser['name'])) ?>')" title="Terima Pendaftaran">
                                            <i class="bi bi-check-circle-fill"></i> Terima
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1" onclick="openRejectModal('<?= $pUser['id'] ?>', '<?= htmlspecialchars(addslashes($pUser['name'])) ?>')" title="Tolak Pendaftaran">
                                            <i class="bi bi-x-circle"></i> Tolak
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="padding-left: 68px;">Nama</th>
                            <th>Jurusan</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>NO. HP</th>
                            <th class="text-center" style="width: 140px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($interns)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                    Tidak ada data user magang yang ditemukan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($interns as $intern): 
                                $avatarUrl = !empty($intern['avatar']) ? BASE_URL . '/uploads/' . $intern['avatar'] : null;
                                $initial = mb_strtoupper(mb_substr($intern['name'] ?? 'M', 0, 1));
                            ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <?php if ($avatarUrl): ?>
                                                <img src="<?= $avatarUrl ?>" alt="<?= htmlspecialchars($intern['name']) ?>" class="sidebar-user-avatar">
                                            <?php else: ?>
                                                <div class="sidebar-user-avatar sidebar-user-avatar-initials">
                                                    <?= $initial ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($intern['name']) ?></div>
                                                <?php if (($intern['approval_status'] ?? '') === 'pending'): ?>
                                                    <span class="badge bg-warning text-dark px-2 py-0.5" style="font-size: 11px;">Pending</span>
                                                <?php elseif (($intern['approval_status'] ?? '') === 'rejected'): ?>
                                                    <span class="badge bg-danger text-white px-2 py-0.5" style="font-size: 11px;">Ditolak</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-bold" style="font-size: 12px;">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-right: 4px;">
                                                <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                                                <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                                            </svg><?= htmlspecialchars($intern['jurusan'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1.5 fw-bold" style="font-size: 12.5px;">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-right: 4px;">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg><?= htmlspecialchars($intern['username']) ?>
                                        </span>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div class="small text-dark d-flex align-items-center">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-right: 6px;" class="text-primary">
                                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                                <polyline points="22,6 12,13 2,6"></polyline>
                                            </svg><?= htmlspecialchars($intern['email']) ?>
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div class="small text-dark d-flex align-items-center">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-right: 6px;" class="text-success">
                                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                            </svg><?= htmlspecialchars($intern['phone'] ?? '-') ?>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle" style="min-width: 140px; text-align: center;">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <?php if (($intern['approval_status'] ?? '') === 'pending'): ?>
                                                <button type="button" class="btn btn-sm btn-success px-2 py-1" onclick="confirmApprove('<?= $intern['id'] ?>', '<?= htmlspecialchars(addslashes($intern['name'])) ?>')" title="Terima Pendaftaran"><i class="bi bi-check-lg"></i></button>
                                                <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1" onclick="openRejectModal('<?= $intern['id'] ?>', '<?= htmlspecialchars(addslashes($intern['name'])) ?>')" title="Tolak Pendaftaran"><i class="bi bi-x-lg"></i></button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-icon btn-sm btn-ghost" style="color:#e53935;" onclick="deleteIntern('<?= $intern['id'] ?>', '<?= htmlspecialchars(addslashes($intern['name'])) ?>')" title="Hapus"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <tr id="liveNoDataRow" class="no-data-row" style="display: none;">
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-search fs-2 d-block mb-2 text-secondary"></i>
                                Tidak ada data user magang yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php if ($pagination['total_pages'] > 1): ?>
            <div class="card-footer bg-white border-0 py-3">
                <nav aria-label="Pagination user magang">
                    <ul class="pagination pagination-sm justify-content-end mb-0">
                        <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                            <li class="page-item <?= $p === $pagination['current_page'] ? 'active' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/intern-users?page=<?= $p ?>&role=<?= $filters['role'] ?? 'all' ?><?= !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '' ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>


<div class="modal fade" id="approveConfirmModal" tabindex="-1" aria-labelledby="approveConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px; width: 90%; margin: 1.75rem auto;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; padding: 12px;">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <div style="width: 60px; height: 60px; background: #dcfce7; color: #16a34a; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">
                        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                </div>
                <h5 style="font-weight: 700; color: #1e293b; font-size: 18px; margin-bottom: 8px;">Konfirmasi Terima User</h5>
                <p id="approveConfirmMessage" style="font-size: 13.5px; color: #64748b; margin-bottom: 24px; line-height: 1.5;">Apakah Anda yakin ingin menerima dan mengaktifkan akun magang ini?</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn" onclick="closeApproveConfirmModal()" style="flex: 1; height: 42px; font-weight: 600; font-size: 14px; background: #f1f5f9; color: #475569; border: none; border-radius: 10px;">Batal</button>
                    <button type="button" class="btn" id="btnExecuteApprove" onclick="executeApprove()" style="flex: 1; height: 42px; font-weight: 600; font-size: 14px; background: #16a34a; color: #ffffff; border: none; border-radius: 10px;">Terima & Aktifkan</button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px; width: 90%; margin: 1.75rem auto;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; padding: 12px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark fs-5" id="rejectModalLabel">Tolak Pendaftaran User Magang</h5>
                <button type="button" class="btn-close" onclick="closeRejectModal()" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <p class="text-secondary small mb-3">Anda akan menolak pendaftaran akun magang atas nama <strong id="rejectTargetName" class="text-dark">-</strong>.</p>
                <div class="form-group mb-0">
                    <label class="form-label fw-semibold small text-dark" for="rejectReason">Alasan Penolakan (akan dikirim via email):</label>
                    <textarea class="form-control" id="rejectReason" rows="3" placeholder="Alasan Penolakan..." style="border-radius: 10px; font-size: 13.5px;"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn" onclick="closeRejectModal()" style="height: 40px; font-weight: 600; font-size: 14px; background: #f1f5f9; color: #475569; border: none; border-radius: 10px; padding: 0 18px;">Batal</button>
                <button type="button" class="btn btn-danger" id="btnSubmitReject" onclick="submitReject()" style="height: 40px; font-weight: 600; font-size: 14px; border-radius: 10px; padding: 0 18px;">Tolak Pendaftaran</button>
            </div>
        </div>
    </div>
</div>

<script>
var targetRejectId = null;
var targetApproveId = null;

function filterTableLive(query) {
    var filter = query.toLowerCase().trim();
    var rows = document.querySelectorAll('tbody tr:not(.no-data-row)');
    var matchCount = 0;

    rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        if (filter === '' || text.indexOf(filter) > -1) {
            row.style.display = '';
            matchCount++;
        } else {
            row.style.display = 'none';
        }
    });

    var noDataRow = document.getElementById('liveNoDataRow');
    if (noDataRow) {
        noDataRow.style.display = (matchCount === 0 && rows.length > 0) ? '' : 'none';
    }
}

function confirmApprove(id, name) {
    targetApproveId = id;
    var msgEl = document.getElementById('approveConfirmMessage');
    if (msgEl) {
        msgEl.innerHTML = 'Apakah Anda yakin ingin menerima dan mengaktifkan akun magang atas nama<br><strong>"' + (name || '') + '"</strong>?<br><br><span style="font-size: 12px; color: #64748b;">Sistem akan mengaktifkan akun dan mengirimkan email konfirmasi ke user.</span>';
    }

    var modalEl = document.getElementById('approveConfirmModal');
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

function closeApproveConfirmModal() {
    var modalEl = document.getElementById('approveConfirmModal');
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

function getCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return (meta && meta.content) ? meta.content : '<?= Session::csrfToken() ?>';
}

function executeApprove() {
    if (!targetApproveId) return;

    var btn = document.getElementById('btnExecuteApprove');
    btn.disabled = true;

    var token = getCsrfToken();
    var formData = new FormData();
    formData.append('<?= CSRF_TOKEN_NAME ?>', token);
    formData.append('csrf_token', token);
    formData.append('_csrf_token', token);

    fetch('<?= BASE_URL ?>/intern-users/' + targetApproveId + '/approve', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token
        }
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        closeApproveConfirmModal();
        if (data.success) {
            showWebNotification(data.message, 'success');
            setTimeout(function() { window.location.reload(); }, 1000);
        } else {
            showWebNotification(data.message || 'Gagal mengonfirmasi akun.', 'danger');
        }
    })
    .catch(err => {
        btn.disabled = false;
        closeApproveConfirmModal();
        showWebNotification('Terjadi kesalahan koneksi server.', 'danger');
    });
}

function openRejectModal(id, name) {
    targetRejectId = id;
    document.getElementById('rejectTargetName').textContent = name;
    document.getElementById('rejectReason').value = '';
    
    var modalEl = document.getElementById('rejectModal');
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

function closeRejectModal() {
    var modalEl = document.getElementById('rejectModal');
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

function submitReject() {
    if (!targetRejectId) return;

    var reason = document.getElementById('rejectReason').value.trim();
    var token = getCsrfToken();
    var formData = new FormData();
    formData.append('reason', reason);
    formData.append('<?= CSRF_TOKEN_NAME ?>', token);
    formData.append('csrf_token', token);
    formData.append('_csrf_token', token);

    var btn = document.getElementById('btnSubmitReject');
    btn.disabled = true;

    fetch('<?= BASE_URL ?>/intern-users/' + targetRejectId + '/reject', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token
        }
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        closeRejectModal();
        if (data.success) {
            showWebNotification(data.message, 'success');
            setTimeout(function() { window.location.reload(); }, 1000);
        } else {
            showWebNotification(data.message || 'Gagal menolak pendaftaran.', 'danger');
        }
    })
    .catch(err => {
        btn.disabled = false;
        closeRejectModal();
        showWebNotification('Terjadi kesalahan koneksi server.', 'danger');
    });
}

var targetDeleteId = null;

function deleteIntern(id, name) {
    targetDeleteId = id;
    var msgEl = document.getElementById('deleteConfirmMessage');
    if (msgEl) {
        msgEl.innerHTML = 'Apakah Anda yakin ingin menghapus user <strong>"' + (name || '') + '"</strong>?';
    }

    var modalEl = document.getElementById('deleteConfirmModal');
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

function closeDeleteConfirmModal() {
    var modalEl = document.getElementById('deleteConfirmModal');
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

function executeDelete() {
    if (!targetDeleteId) return;

    var btn = document.getElementById('btnExecuteDelete');
    btn.disabled = true;

    var token = getCsrfToken();
    var formData = new FormData();
    formData.append('<?= CSRF_TOKEN_NAME ?>', token);
    formData.append('csrf_token', token);
    formData.append('_csrf_token', token);

    fetch('<?= BASE_URL ?>/intern-users/' + targetDeleteId + '/delete', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token
        }
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        closeDeleteConfirmModal();

        if (data.success) {
            showWebNotification(data.message, 'success');
            setTimeout(function() { window.location.reload(); }, 1000);
        } else {
            showWebNotification(data.message || 'Gagal menghapus user.', 'danger');
        }
    })
    .catch(err => {
        btn.disabled = false;
        closeDeleteConfirmModal();
        showWebNotification('Terjadi kesalahan koneksi server.', 'danger');
    });
}

function showWebNotification(message, type) {
    type = type || 'success';
    var icon = type === 'success' ? 'check-circle-fill' : (type === 'danger' || type === 'error' ? 'x-circle-fill' : 'info-circle-fill');
    var toastClass = (type === 'danger' || type === 'error') ? 'error' : type;

    var container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    var toast = document.createElement('div');
    toast.className = 'sonner-toast sonner-toast-' + toastClass;
    toast.innerHTML = 
        '<span class="sonner-icon"><i class="bi bi-' + icon + '"></i></span>' +
        '<div class="sonner-content"><div class="sonner-message">' + message + '</div></div>';

    container.appendChild(toast);

    setTimeout(function() {
        if (!toast || !toast.parentNode) return;
        toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-24px) scale(0.95)';
        setTimeout(function() {
            if (toast && toast.parentNode) toast.parentNode.removeChild(toast);
        }, 350);
    }, 3500);
}
</script>


<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
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
                <p id="deleteConfirmMessage" style="font-size: 13.5px; color: #64748b; margin-bottom: 24px; line-height: 1.5;">Apakah Anda yakin ingin menghapus user ini?</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn" onclick="closeDeleteConfirmModal()" style="flex: 1; height: 42px; font-weight: 600; font-size: 14px; background: #f1f5f9; color: #475569; border: none; border-radius: 10px; transition: all 0.2s ease;">Batal</button>
                    <button type="button" class="btn" id="btnExecuteDelete" onclick="executeDelete()" style="flex: 1; height: 42px; font-weight: 600; font-size: 14px; background: #dc2626; color: #ffffff; border: none; border-radius: 10px; transition: all 0.2s ease;">Hapus User</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>

#addAdminModal .step-indicator {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    padding: 0 10px;
}

#addAdminModal .step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    position: relative;
    z-index: 2;
}

#addAdminModal .step-number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #64748b;
    font-weight: bold;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

#addAdminModal .step-label {
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
    transition: all 0.3s ease;
}

#addAdminModal .step-item.active .step-number {
    background: #003399;
    color: #ffffff;
    box-shadow: 0 0 0 4px rgba(0, 51, 153, 0.15);
}

#addAdminModal .step-item.active .step-label {
    color: #003399;
}

#addAdminModal .step-item.completed .step-number {
    background: #2e7d32;
    color: #ffffff;
}

#addAdminModal .step-line {
    flex: 1;
    height: 2px;
    background: #e2e8f0;
    margin: 0 8px;
    transform: translateY(-10px);
}

#addAdminModal .step-line.active {
    background: #003399;
}
</style>


<div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px; width: 100%; margin: 1.75rem auto;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; padding: 12px 16px;">
            <div class="modal-header border-bottom-0 pb-0 pt-2 px-2">
                <h5 class="modal-title fw-bold fs-5" id="addAdminModalLabel">Tambah User Admin</h5>
                <button type="button" class="btn-close" onclick="closeAddAdminModal()" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                
                <div class="step-indicator mb-4" id="adminStepIndicator">
                    <div class="step-item active" id="adminStepItem1">
                        <div class="step-number" id="adminStepNum1">1</div>
                        <span class="step-label" id="adminStepLabel1">Data Diri</span>
                    </div>
                    <div class="step-line" id="adminStepLine1"></div>
                    <div class="step-item" id="adminStepItem2">
                        <div class="step-number" id="adminStepNum2">2</div>
                        <span class="step-label" id="adminStepLabel2">Verifikasi OTP</span>
                    </div>
                    <div class="step-line" id="adminStepLine2"></div>
                    <div class="step-item" id="adminStepItem3">
                        <div class="step-number" id="adminStepNum3">3</div>
                        <span class="step-label" id="adminStepLabel3">Kredensial</span>
                    </div>
                </div>

                <div id="modalAdminAlert" class="alert alert-danger p-2 small mb-3" style="display: none;"></div>

                
                <div id="adminStep1">
                    <form id="formAdminStep1" autocomplete="off">
                        <div class="form-group mb-3">
                            <label class="form-label" for="adminName">Nama Lengkap</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <input type="text" id="adminName" class="form-control" placeholder="Nama Lengkap" required>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label" for="adminJurusan">Divisi</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                                    <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                                </svg>
                                <input type="text" id="adminJurusan" class="form-control" placeholder="Divisi" required>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label" for="adminEmail">Email</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                                <input type="email" id="adminEmail" class="form-control" placeholder="email@gmail.com" required>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label" for="adminPhone">NO. HP</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                                <input type="tel" id="adminPhone" class="form-control" placeholder="08xxxxxxxxxx" required>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-2" style="margin-top: 24px;">
                            <button type="button" class="btn btn-secondary w-100" onclick="closeAddAdminModal()">Batal</button>
                            <button type="button" class="btn btn-primary w-100" id="btnAdminStep1Next" onclick="submitAdminStep1()">
                                <span class="btn-text">Selanjutnya</span>
                                <span class="btn-loader" style="display: none;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="spin">
                                        <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
                                    </svg> Memproses…
                                </span>
                            </button>
                        </div>
                    </form>
                </div>

                
                <div id="adminStep2" style="display: none;">
                    <div class="text-center mb-4">
                        <div class="otp-icon-wrapper mb-3" style="width: 56px; height: 56px; margin: 0 auto; background: #e8f0fe; color: #003399; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </div>
                        <h5 style="margin: 0 0 6px 0; font-weight: 600;">Verifikasi Email</h5>
                        <p style="font-size: 13px; color: #666; margin: 0;">Masukkan 6 digit kode verifikasi yang telah dikirim ke <br><strong id="adminOtpTargetEmail" style="color: #003399;">-</strong></p>
                    </div>
                    <form id="formAdminStep2" autocomplete="off">
                        <div class="form-group mb-4">
                            <div class="otp-input-container">
                                <input type="text" id="adminOtp" class="form-control text-center text-tracking-widest" placeholder="0 0 0 0 0 0" maxlength="6" style="font-size: 24px; font-weight: bold; letter-spacing: 12px; height: 56px;" required>
                            </div>
                        </div>
                        <div class="text-center mb-4">
                            <span class="text-muted" style="font-size: 13px;">Tidak menerima kode? </span>
                            <button type="button" class="btn-link-action" onclick="resendAdminOtp()" style="background: none; border: none; color: #003399; font-weight: 600; font-size: 13px; cursor: pointer; text-decoration: underline;">Kirim Ulang</button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center" style="margin-top: 24px;">
                            <button type="button" class="btn btn-secondary" onclick="goAdminStep(1)">Kembali</button>
                            <button type="button" class="btn btn-primary" id="btnAdminStep2Next" onclick="submitAdminStep2()">
                                <span class="btn-text">Verifikasi</span>
                                <span class="btn-loader" style="display: none;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="spin">
                                        <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
                                    </svg> Memproses…
                                </span>
                            </button>
                        </div>
                    </form>
                </div>

                
                <div id="adminStep3" style="display: none;">
                    <form id="formAdminStep3" autocomplete="off">
                        <div class="form-group mb-3">
                            <label class="form-label" for="adminUsername">Username</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <input type="text" id="adminUsername" class="form-control" placeholder="Username" required>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label" for="adminPassword">Password</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                <input type="password" id="adminPassword" class="form-control" placeholder="Minimal 8 karakter" required>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label" for="adminPasswordConfirm">Konfirmasi Password</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                <input type="password" id="adminPasswordConfirm" class="form-control" placeholder="Ulangi Password" required>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-2" style="margin-top: 24px;">
                            <button type="button" class="btn btn-secondary w-100" onclick="goAdminStep(2)">Kembali</button>
                            <button type="button" class="btn btn-success w-100" id="btnAdminStep3Finish" onclick="submitAdminStep3()">
                                <span class="btn-text">Selesai (Buat Admin)</span>
                                <span class="btn-loader" style="display: none;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="spin">
                                        <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
                                    </svg> Memproses…
                                </span>
                            </button>
                        </div>
                    </form>
                </div>

                
                <div id="adminStep4" style="display: none;" class="text-center py-3">
                    <div class="text-success mb-3">
                        <i class="bi bi-check-circle-fill" style="font-size: 56px;"></i>
                    </div>
                    <h5 class="fw-bold mb-2">User Admin Berhasil Ditambahkan!</h5>
                    <p class="text-muted small mb-4">Akun Admin baru telah terdaftar dan otomatis aktif secara langsung.</p>
                    <button type="button" class="btn btn-primary w-100" onclick="window.location.reload()">Tutup & Refresh Data</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openAddAdminModal() {
    goAdminStep(1);
    document.getElementById('modalAdminAlert').style.display = 'none';
    document.getElementById('adminName').value = '';
    document.getElementById('adminJurusan').value = '';
    document.getElementById('adminEmail').value = '';
    document.getElementById('adminPhone').value = '';
    document.getElementById('adminOtp').value = '';
    document.getElementById('adminUsername').value = '';
    document.getElementById('adminPassword').value = '';
    document.getElementById('adminPasswordConfirm').value = '';

    var modalEl = document.getElementById('addAdminModal');
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    } else {
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
    }
}

function closeAddAdminModal() {
    var modalEl = document.getElementById('addAdminModal');
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
            modal.hide();
        }
    }
    modalEl.classList.remove('show');
    modalEl.style.display = 'none';
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
    var backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(function(b) { b.remove(); });
}

function goAdminStep(step) {
    document.getElementById('adminStep1').style.display = (step === 1) ? 'block' : 'none';
    document.getElementById('adminStep2').style.display = (step === 2) ? 'block' : 'none';
    document.getElementById('adminStep3').style.display = (step === 3) ? 'block' : 'none';
    document.getElementById('adminStep4').style.display = (step === 4) ? 'block' : 'none';

    var item1 = document.getElementById('adminStepItem1');
    var item2 = document.getElementById('adminStepItem2');
    var item3 = document.getElementById('adminStepItem3');
    var num1 = document.getElementById('adminStepNum1');
    var num2 = document.getElementById('adminStepNum2');
    var num3 = document.getElementById('adminStepNum3');
    var line1 = document.getElementById('adminStepLine1');
    var line2 = document.getElementById('adminStepLine2');

    [item1, item2, item3].forEach(function(el) {
        if (el) el.classList.remove('active', 'completed');
    });
    [line1, line2].forEach(function(el) {
        if (el) el.classList.remove('active');
    });

    if (num1) num1.innerHTML = '1';
    if (num2) num2.innerHTML = '2';
    if (num3) num3.innerHTML = '3';

    var checkSvg = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';

    if (step === 1) {
        if (item1) item1.classList.add('active');
    } else if (step === 2) {
        if (item1) {
            item1.classList.add('completed');
            if (num1) num1.innerHTML = checkSvg;
        }
        if (line1) line1.classList.add('active');
        if (item2) item2.classList.add('active');
    } else if (step === 3 || step === 4) {
        if (item1) {
            item1.classList.add('completed');
            if (num1) num1.innerHTML = checkSvg;
        }
        if (item2) {
            item2.classList.add('completed');
            if (num2) num2.innerHTML = checkSvg;
        }
        if (line1) line1.classList.add('active');
        if (line2) line2.classList.add('active');
        if (item3) item3.classList.add('active');
    }
}

function submitAdminStep1() {
    var name = document.getElementById('adminName').value.trim();
    var jurusan = document.getElementById('adminJurusan').value.trim();
    var email = document.getElementById('adminEmail').value.trim();
    var phone = document.getElementById('adminPhone').value.trim();
    var alertEl = document.getElementById('modalAdminAlert');
    alertEl.style.display = 'none';

    if (!name || !jurusan || !email || !phone) {
        alertEl.textContent = 'Semua bidang data diri wajib diisi.';
        alertEl.style.display = 'block';
        return;
    }

    if (!email.toLowerCase().endsWith('@gmail.com')) {
        alertEl.textContent = 'Pendaftaran email wajib menggunakan domain @gmail.com.';
        alertEl.style.display = 'block';
        return;
    }

    var formData = new FormData();
    formData.append('name', name);
    formData.append('jurusan', jurusan);
    formData.append('email', email);
    formData.append('phone', phone);
    formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= Session::csrfToken() ?>');

    var btn = document.getElementById('btnAdminStep1Next');
    btn.disabled = true;
    btn.querySelector('.btn-text').style.display = 'none';
    btn.querySelector('.btn-loader').style.display = 'inline-block';

    fetch('<?= BASE_URL ?>/register/send-otp', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.querySelector('.btn-text').style.display = 'inline-block';
        btn.querySelector('.btn-loader').style.display = 'none';

        if (data.success) {
            document.getElementById('adminOtpTargetEmail').textContent = email;
            goAdminStep(2);
        } else {
            alertEl.textContent = data.message || 'Gagal mengirimkan OTP.';
            alertEl.style.display = 'block';
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.querySelector('.btn-text').style.display = 'inline-block';
        btn.querySelector('.btn-loader').style.display = 'none';
        alertEl.textContent = 'Terjadi kesalahan jaringan.';
        alertEl.style.display = 'block';
    });
}

function resendAdminOtp() {
    submitAdminStep1();
}

function submitAdminStep2() {
    var otp = document.getElementById('adminOtp').value.trim();
    var email = document.getElementById('adminEmail').value.trim();
    var alertEl = document.getElementById('modalAdminAlert');
    alertEl.style.display = 'none';

    if (!otp) {
        alertEl.textContent = 'Masukkan kode verifikasi OTP.';
        alertEl.style.display = 'block';
        return;
    }

    var formData = new FormData();
    formData.append('otp', otp);
    formData.append('email', email);
    formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= Session::csrfToken() ?>');

    var btn = document.getElementById('btnAdminStep2Next');
    btn.disabled = true;
    btn.querySelector('.btn-text').style.display = 'none';
    btn.querySelector('.btn-loader').style.display = 'inline-block';

    fetch('<?= BASE_URL ?>/register/verify-otp', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.querySelector('.btn-text').style.display = 'inline-block';
        btn.querySelector('.btn-loader').style.display = 'none';

        if (data.success) {
            goAdminStep(3);
        } else {
            alertEl.textContent = data.message || 'Kode OTP tidak valid.';
            alertEl.style.display = 'block';
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.querySelector('.btn-text').style.display = 'inline-block';
        btn.querySelector('.btn-loader').style.display = 'none';
        alertEl.textContent = 'Terjadi kesalahan jaringan.';
        alertEl.style.display = 'block';
    });
}

function submitAdminStep3() {
    var username = document.getElementById('adminUsername').value.trim();
    var password = document.getElementById('adminPassword').value;
    var passwordConfirm = document.getElementById('adminPasswordConfirm').value;
    var email = document.getElementById('adminEmail').value.trim();
    var alertEl = document.getElementById('modalAdminAlert');
    alertEl.style.display = 'none';

    if (!username || !password || !passwordConfirm) {
        alertEl.textContent = 'Username dan Password wajib diisi.';
        alertEl.style.display = 'block';
        return;
    }

    if (password !== passwordConfirm) {
        alertEl.textContent = 'Konfirmasi password tidak cocok.';
        alertEl.style.display = 'block';
        return;
    }

    var formData = new FormData();
    formData.append('username', username);
    formData.append('password', password);
    formData.append('password_confirm', passwordConfirm);
    formData.append('email', email);
    formData.append('target_role', 'admin');
    formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= Session::csrfToken() ?>');

    var btn = document.getElementById('btnAdminStep3Finish');
    btn.disabled = true;
    btn.querySelector('.btn-text').style.display = 'none';
    btn.querySelector('.btn-loader').style.display = 'inline-block';

    fetch('<?= BASE_URL ?>/register/complete', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.querySelector('.btn-text').style.display = 'inline-block';
        btn.querySelector('.btn-loader').style.display = 'none';

        if (data.success) {
            goAdminStep(4);
        } else {
            alertEl.textContent = data.message || 'Gagal mendaftarkan Admin.';
            alertEl.style.display = 'block';
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.querySelector('.btn-text').style.display = 'inline-block';
        btn.querySelector('.btn-loader').style.display = 'none';
        alertEl.textContent = 'Terjadi kesalahan jaringan.';
        alertEl.style.display = 'block';
    });
}
</script>

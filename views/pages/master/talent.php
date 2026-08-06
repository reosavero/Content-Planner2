<?php
$data = $data ?? [];
?>

<div class="modal" id="modalTalent">
    <div class="modal-backdrop" onclick="closeModal('modalTalent')"></div>
    <div class="modal-content" style="max-width:500px;">
        <div class="modal-header">
            <h6 class="mb-0">Tambah Talent</h6>
            <button type="button" class="modal-close" onclick="closeModal('modalTalent')">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/master/talent/store">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Session::csrfToken() ?>">
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label class="form-label">Nama Talent</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Posisi/Jabatan</label>
                    <input type="text" name="position" class="form-control" placeholder="Presenter, Reporter, dll">
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-control" rows="3"></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-group mt-3">
                    <label class="form-label">Instagram</label>
                    <input type="text" name="instagram" class="form-control" placeholder="@username">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modalTalent')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Posisi</th>
                        <th>Kontak</th>
                        <th>Instagram</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="bi bi-person-badge" style="font-size:2rem;color:var(--text-tertiary);"></i>
                                    <p style="color:var(--text-tertiary);margin-top:8px;">Belum ada talent</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($row['name']) ?></td>
                                <td><small class="text-tertiary"><?= htmlspecialchars($row['position'] ?? '-') ?></small></td>
                                <td>
                                    <small>
                                        <?php if (!empty($row['phone'])): ?><i class="bi bi-telephone"></i> <?= htmlspecialchars($row['phone']) ?><br><?php endif; ?>
                                        <?php if (!empty($row['email'])): ?><i class="bi bi-envelope"></i> <?= htmlspecialchars($row['email']) ?><?php endif; ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if (!empty($row['instagram'])): ?>
                                        <a href="https://instagram.com/<?= ltrim($row['instagram'], '@') ?>" target="_blank" class="text-link">
                                            <i class="bi bi-instagram"></i> <?= htmlspecialchars($row['instagram']) ?>
                                        </a>
                                    <?php else: ?>
                                        <small class="text-tertiary">-</small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-ghost" onclick="editTalent(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['position'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($row['bio'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($row['phone'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($row['email'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($row['instagram'] ?? '', ENT_QUOTES) ?>')" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-ghost text-danger" onclick="if(confirm('Hapus talent ini?')){deleteTalent(<?= $row['id'] ?>)}" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function editTalent(id, name, position, bio, phone, email, instagram) {
    const div = document.createElement('div');
    div.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);';
    div.innerHTML = '<div style="background:var(--surface);border-radius:var(--radius-lg);padding:24px;max-width:500px;width:90%;">' +
        '<h6 style="margin-bottom:16px;">Edit Talent</h6>' +
        '<form method="POST" action="<?= BASE_URL ?>/master/talent/' + id + '/update">' +
        '<input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Session::csrfToken() ?>">' +
        '<div class="form-group mb-3"><label class="form-label">Nama</label><input type="text" name="name" class="form-control" value="' + name + '" required></div>' +
        '<div class="form-group mb-3"><label class="form-label">Posisi</label><input type="text" name="position" class="form-control" value="' + position + '"></div>' +
        '<div class="form-group mb-3"><label class="form-label">Bio</label><textarea name="bio" class="form-control" rows="2">' + bio + '</textarea></div>' +
        '<div class="row g-3"><div class="col-6"><div class="form-group"><label class="form-label">Telepon</label><input type="text" name="phone" class="form-control" value="' + phone + '"></div></div>' +
        '<div class="col-6"><div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="' + email + '"></div></div></div>' +
        '<div class="form-group mt-3"><label class="form-label">Instagram</label><input type="text" name="instagram" class="form-control" value="' + instagram + '"></div>' +
        '<div class="d-flex gap-2 justify-content-end mt-3">' +
        '<button type="button" class="btn btn-ghost" onclick="this.closest(\'div\').closest(\'div\').remove()">Batal</button>' +
        '<button type="submit" class="btn btn-primary">Update</button>' +
        '</div></form></div>';
    document.body.appendChild(div);
}

function deleteTalent(id) {
    var form = new FormData();
    form.append('<?= CSRF_TOKEN_NAME ?>', '<?= Session::csrfToken() ?>');
    fetch('<?= BASE_URL ?>/master/talent/' + id + '/delete', { method: 'POST', body: form })
        .then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); })
        .catch(() => location.reload());
}
</script>

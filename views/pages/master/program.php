<?php
$data = $data ?? [];
?>

<div class="modal" id="modalProgram">
    <div class="modal-backdrop" onclick="closeModal('modalProgram')"></div>
    <div class="modal-content" style="max-width:500px;">
        <div class="modal-header">
            <h6 class="mb-0">Tambah Program TV</h6>
            <button type="button" class="modal-close" onclick="closeModal('modalProgram')">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/master/program/store">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Session::csrfToken() ?>">
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label class="form-label">Nama Program</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="category" class="form-control" required>
                        <option value="">Pilih Kategori</option>
                        <option value="news">News</option>
                        <option value="entertainment">Entertainment</option>
                        <option value="sports">Sports</option>
                        <option value="religious">Religious</option>
                        <option value="education">Education</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modalProgram')">Batal</button>
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
                        <th>Nama Program</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <i class="bi bi-tv" style="font-size:2rem;color:var(--text-tertiary);"></i>
                                    <p style="color:var(--text-tertiary);margin-top:8px;">Belum ada program TV</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($row['name']) ?></td>
                                <td><span class="badge badge-soft"><?= htmlspecialchars(ucfirst($row['category'] ?? '-')) ?></span></td>
                                <td>
                                    <span class="badge <?= ($row['is_active'] ?? 1) ? 'badge-success' : 'badge-secondary' ?>">
                                        <?= ($row['is_active'] ?? 1) ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-ghost" onclick="editProgram(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['category'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($row['description'] ?? '', ENT_QUOTES) ?>')" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-ghost text-danger" onclick="if(confirm('Hapus program ini?')){deleteProgram(<?= $row['id'] ?>)}" title="Hapus">
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
function editProgram(id, name, category, description) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= BASE_URL ?>/master/program/' + id + '/update';
    form.innerHTML = '<input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Session::csrfToken() ?>">' +
        '<div style="position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);">' +
        '<div style="background:var(--surface);border-radius:var(--radius-lg);padding:24px;max-width:500px;width:90%;">' +
        '<h6 style="margin-bottom:16px;">Edit Program TV</h6>' +
        '<div class="form-group mb-3"><label class="form-label">Nama Program</label><input type="text" name="name" class="form-control" value="' + name + '" required></div>' +
        '<div class="form-group mb-3"><label class="form-label">Kategori</label><select name="category" class="form-control" required>' +
        '<option value="news" ' + (category === 'news' ? 'selected' : '') + '>News</option>' +
        '<option value="entertainment" ' + (category === 'entertainment' ? 'selected' : '') + '>Entertainment</option>' +
        '<option value="sports" ' + (category === 'sports' ? 'selected' : '') + '>Sports</option>' +
        '<option value="religious" ' + (category === 'religious' ? 'selected' : '') + '>Religious</option>' +
        '<option value="education" ' + (category === 'education' ? 'selected' : '') + '>Education</option>' +
        '<option value="other" ' + (category === 'other' ? 'selected' : '') + '>Lainnya</option>' +
        '</select></div>' +
        '<div class="form-group mb-3"><label class="form-label">Deskripsi</label><textarea name="description" class="form-control" rows="3">' + description + '</textarea></div>' +
        '<div class="d-flex gap-2 justify-content-end">' +
        '<button type="button" class="btn btn-ghost" onclick="this.closest(\'form\').remove()">Batal</button>' +
        '<button type="submit" class="btn btn-primary">Update</button>' +
        '</div></div></div>';
    document.body.appendChild(form);
}

function deleteProgram(id) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= BASE_URL ?>/master/program/' + id + '/delete';
    form.innerHTML = '<input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Session::csrfToken() ?>">';
    document.body.appendChild(form);
    form.submit();
}
</script>

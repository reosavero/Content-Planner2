<?php
$data = $data ?? [];
$platforms = $platforms ?? [];
?>

<div class="modal" id="modalTemplate">
    <div class="modal-backdrop" onclick="closeModal('modalTemplate')"></div>
    <div class="modal-content" style="max-width:600px;">
        <div class="modal-header">
            <h6 class="mb-0">Tambah Template Caption</h6>
            <button type="button" class="modal-close" onclick="closeModal('modalTemplate')">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/master/template/store">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Session::csrfToken() ?>">
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label class="form-label">Nama Template</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Platform</label>
                    <select name="platform_id" class="form-control">
                        <option value="">Semua Platform</option>
                        <?php foreach ($platforms as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Konten Caption</label>
                    <textarea name="content" class="form-control" rows="5" required placeholder="Gunakan {hashtag} untuk placeholder hashtag"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Hashtag Placeholder</label>
                    <input type="text" name="hashtag_placeholder" class="form-control" value="{hashtag}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modalTemplate')">Batal</button>
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
                        <th>Nama Template</th>
                        <th>Platform</th>
                        <th>Isi Caption</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <i class="bi bi-file-text" style="font-size:2rem;color:var(--text-tertiary);"></i>
                                    <p style="color:var(--text-tertiary);margin-top:8px;">Belum ada template caption</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($row['name']) ?></td>
                                <td>
                                    <?php if (!empty($row['platform_icon'])): ?>
                                        <i class="<?= iconClass($row['platform_icon']) ?>"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($row['platform_name'] ?? 'Semua') ?>
                                </td>
                                <td>
                                    <small class="text-tertiary" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                        <?= nl2br(htmlspecialchars($row['content'])) ?>
                                    </small>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-ghost" onclick="editTemplate(<?= $row['id'] ?>, <?= htmlspecialchars(json_encode($row['name']), ENT_QUOTES) ?>, <?= $row['platform_id'] ?? 'null' ?>, <?= htmlspecialchars(json_encode($row['content'] ?? ''), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($row['hashtag_placeholder'] ?? '{hashtag}'), ENT_QUOTES) ?>)" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-ghost text-danger" onclick="if(confirm('Hapus template ini?')){deleteTemplate(<?= $row['id'] ?>)}" title="Hapus">
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
function editTemplate(id, name, platformId, content, placeholder) {
    var platformOptions = '<option value="">Semua Platform</option>';
    <?php foreach ($platforms as $p): ?>
        platformOptions += '<option value="<?= $p['id'] ?>" ' + (platformId == <?= $p['id'] ?> ? 'selected' : '') + '><?= htmlspecialchars($p['name'], ENT_QUOTES) ?></option>';
    <?php endforeach; ?>
    
    const div = document.createElement('div');
    div.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);';
    div.innerHTML = '<div style="background:var(--surface);border-radius:var(--radius-lg);padding:24px;max-width:600px;width:90%;">' +
        '<h6 style="margin-bottom:16px;">Edit Template</h6>' +
        '<form method="POST" action="<?= BASE_URL ?>/master/template/' + id + '/update">' +
        '<input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Session::csrfToken() ?>">' +
        '<div class="form-group mb-3"><label class="form-label">Nama Template</label><input type="text" name="name" class="form-control" value="' + name.replace(/"/g, '&quot;') + '" required></div>' +
        '<div class="form-group mb-3"><label class="form-label">Platform</label><select name="platform_id" class="form-control">' + platformOptions + '</select></div>' +
        '<div class="form-group mb-3"><label class="form-label">Konten Caption</label><textarea name="content" class="form-control" rows="5" required>' + content.replace(/"/g, '&quot;') + '</textarea></div>' +
        '<div class="form-group"><label class="form-label">Hashtag Placeholder</label><input type="text" name="hashtag_placeholder" class="form-control" value="' + placeholder.replace(/"/g, '&quot;') + '"></div>' +
        '<div class="d-flex gap-2 justify-content-end mt-3">' +
        '<button type="button" class="btn btn-ghost" onclick="this.closest(\'div\').closest(\'div\').remove()">Batal</button>' +
        '<button type="submit" class="btn btn-primary">Update</button>' +
        '</div></form></div>';
    document.body.appendChild(div);
}

function deleteTemplate(id) {
    var form = new FormData();
    form.append('<?= CSRF_TOKEN_NAME ?>', '<?= Session::csrfToken() ?>');
    fetch('<?= BASE_URL ?>/master/template/' + id + '/delete', { method: 'POST', body: form })
        .then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); })
        .catch(() => location.reload());
}
</script>

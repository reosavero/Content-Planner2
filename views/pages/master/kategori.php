<?php
$data = $data ?? [];
$tags = $tags ?? [];
$hashtags = $hashtags ?? [];
?>
<style>

    @keyframes masterCardIn {
        from {
            opacity: 0;
            transform: translateY(18px);
        }
        to {
            opacity: 1;
            transform: none;
        }
    }
    .kategori-card {
        animation: masterCardIn 0.55s cubic-bezier(0.22, 0.61, 0.36, 1) both;
    }
    .tag-card {
        animation: masterCardIn 0.55s cubic-bezier(0.22, 0.61, 0.36, 1) 0.15s both;
    }
    .hashtag-card {
        animation: masterCardIn 0.55s cubic-bezier(0.22, 0.61, 0.36, 1) 0.3s both;
    }
</style>

<div class="modal" id="modalKategori">
    <div class="modal-backdrop" onclick="closeModal('modalKategori')"></div>
    <div class="modal-content" style="max-width:500px;">
        <div class="modal-header">
            <h6 class="mb-0">Tambah Kategori</h6>
            <button type="button" class="modal-close" onclick="closeModal('modalKategori')">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/master/kategori/store">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Session::csrfToken() ?>">
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label class="form-label">Nama Kategori</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Warna</label>
                    <input type="color" name="color" class="form-control" style="height:40px;padding:4px;" value="#3498db">
                </div>
                <div class="form-group">
                    <label class="form-label">Icon</label>
                    <input type="text" name="icon" class="form-control" value="bi-tag" placeholder="bi-tag">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modalKategori')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>


<div class="card kategori-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Kategori Konten <span class="badge badge-secondary ms-1"><?= count($data) ?></span></h6>
        <button class="btn btn-primary btn-sm" onclick="openModal('modalKategori')">
            <i class="bi bi-plus"></i> Tambah
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th>Warna</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="bi bi-tags-fill" style="font-size:2rem;color:var(--text-tertiary);"></i>
                                    <p style="color:var(--text-tertiary);margin-top:8px;">Belum ada kategori</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($row['name']) ?></td>
                                <td><small class="text-tertiary"><?= htmlspecialchars($row['description'] ?? '-') ?></small></td>
                                <td><span style="display:inline-block;width:24px;height:24px;border-radius:50%;background:<?= htmlspecialchars($row['color'] ?? '#3498db') ?>;"></span></td>
                                <td>
                                    <span class="badge <?= ($row['is_active'] ?? 1) ? 'badge-success' : 'badge-secondary' ?>">
                                        <?= ($row['is_active'] ?? 1) ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button class="btn btn-icon btn-sm btn-ghost" onclick="editKategori(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['description'] ?? '', ENT_QUOTES) ?>', '<?= $row['color'] ?? '#3498db' ?>', '<?= htmlspecialchars($row['icon'] ?? 'bi-tag', ENT_QUOTES) ?>', <?= ($row['is_active'] ?? 1) ? 1 : 0 ?>)" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-icon btn-sm btn-ghost" style="color:#e53935;" onclick="if(confirm('Hapus kategori ini?')){deleteKategori(<?= $row['id'] ?>)}" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="row g-4 mt-4">

    <div class="col-md-6">
        <div class="card tag-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Tags <span class="badge badge-secondary ms-1"><?= count($tags) ?></span></h6>
                <button class="btn btn-primary btn-sm" onclick="showAddTag()">
                    <i class="bi bi-plus"></i> Tambah
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nama Tag</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tags)): ?>
                                <tr>
                                    <td colspan="2" class="text-center text-tertiary py-4">Belum ada tag</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tags as $row): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-soft" style="background:<?= htmlspecialchars($row['color'] ?? '#6c757d') ?>20;color:<?= htmlspecialchars($row['color'] ?? '#6c757d') ?>;">
                                                <?= htmlspecialchars($row['name']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="table-actions">
                                                <button class="btn btn-icon btn-sm btn-ghost" style="color:#e53935;" onclick="if(confirm('Hapus tag ini?')){deleteTag(<?= $row['id'] ?>)}" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <div class="col-md-6">
        <div class="card hashtag-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Hashtag <span class="badge badge-secondary ms-1"><?= count($hashtags) ?></span></h6>
                <button class="btn btn-primary btn-sm" onclick="showAddHashtag()">
                    <i class="bi bi-plus"></i> Tambah
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Hashtag</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($hashtags)): ?>
                                <tr>
                                    <td colspan="2" class="text-center text-tertiary py-4">Belum ada hashtag</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($hashtags as $row): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($row['name']) ?></code></td>
                                        <td>
                                            <div class="table-actions">
                                                <button class="btn btn-icon btn-sm btn-ghost" style="color:#e53935;" onclick="if(confirm('Hapus hashtag ini?')){deleteHashtag(<?= $row['id'] ?>)}" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editKategori(id, name, description, color, icon, isActive) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= BASE_URL ?>/master/kategori/' + id + '/update';
    form.innerHTML = '<input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Session::csrfToken() ?>">' +
        '<div style="position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);">' +
        '<div style="background:var(--surface);border-radius:var(--radius-lg);padding:24px;max-width:500px;width:90%;">' +
        '<h6 style="margin-bottom:16px;">Edit Kategori</h6>' +
        '<div class="form-group mb-3"><label class="form-label">Nama</label><input type="text" name="name" class="form-control" value="' + name + '" required></div>' +
        '<div class="form-group mb-3"><label class="form-label">Deskripsi</label><textarea name="description" class="form-control" rows="2">' + description + '</textarea></div>' +
        '<div class="form-group mb-3"><label class="form-label">Warna</label><input type="color" name="color" class="form-control" style="height:40px;padding:4px;" value="' + color + '"></div>' +
        '<div class="form-group mb-3"><label class="form-label">Icon</label><input type="text" name="icon" class="form-control" value="' + icon + '" placeholder="bi-tag"></div>' +
        '<div class="form-group mb-3"><label class="form-label">Status</label><select name="is_active" class="form-control">' +
        '<option value="1"' + (isActive ? ' selected' : '') + '>Aktif</option>' +
        '<option value="0"' + (!isActive ? ' selected' : '') + '>Nonaktif</option>' +
        '</select></div>' +
        '<div class="d-flex gap-2 justify-content-end">' +
        '<button type="button" class="btn btn-ghost" onclick="this.closest(\'form\').remove()">Batal</button>' +
        '<button type="submit" class="btn btn-primary">Update</button>' +
        '</div></div></div>';
    document.body.appendChild(form);
}

function deleteKategori(id) {
    var form = new FormData();
    form.append('<?= CSRF_TOKEN_NAME ?>', '<?= Session::csrfToken() ?>');
    fetch('<?= BASE_URL ?>/master/kategori/' + id + '/delete', { method: 'POST', body: form })
        .then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); })
        .catch(() => location.reload());
}

function showAddTag() {
    showAddPopup('Tambah Tag', '<?= BASE_URL ?>/master/tags/store', [
        { label: 'Nama Tag', name: 'name', type: 'text', required: true },
        { label: 'Warna', name: 'color', type: 'color', value: '#6c757d' }
    ]);
}

function showAddHashtag() {
    showAddPopup('Tambah Hashtag', '<?= BASE_URL ?>/master/hashtag/store', [
        { label: 'Hashtag', name: 'name', type: 'text', placeholder: '#tvrijatim', required: true }
    ]);
}

function showAddPopup(title, action, fields) {
    var div = document.createElement('div');
    div.setAttribute('data-popup', '');
    div.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);';
    var html = '<div style="background:var(--surface);border-radius:var(--radius-lg);padding:24px;max-width:400px;width:90%;">' +
        '<h6 style="margin-bottom:16px;">' + title + '</h6>' +
        '<form method="POST" action="' + action + '">' +
        '<input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Session::csrfToken() ?>">';
    fields.forEach(function(f) {
        html += '<div class="form-group mb-3"><label class="form-label">' + f.label + '</label>' +
            '<input type="' + f.type + '" name="' + f.name + '" class="form-control"' +
            (f.type === 'color' ? ' style="height:40px;padding:4px;"' : '') +
            (f.value ? ' value="' + f.value + '"' : '') +
            (f.placeholder ? ' placeholder="' + f.placeholder + '"' : '') +
            (f.required ? ' required' : '') + '></div>';
    });
    html += '<div class="d-flex gap-2 justify-content-end">' +
        '<button type="button" class="btn btn-ghost" onclick="this.closest(\'[data-popup]\').remove()">Batal</button>' +
        '<button type="submit" class="btn btn-primary">Simpan</button>' +
        '</div></form></div>';
    div.innerHTML = html;
    document.body.appendChild(div);
}

function deleteTag(id) {
    var form = new FormData();
    form.append('<?= CSRF_TOKEN_NAME ?>', '<?= Session::csrfToken() ?>');
    fetch('<?= BASE_URL ?>/master/tags/' + id + '/delete', { method: 'POST', body: form })
        .then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); })
        .catch(() => location.reload());
}

function deleteHashtag(id) {
    var form = new FormData();
    form.append('<?= CSRF_TOKEN_NAME ?>', '<?= Session::csrfToken() ?>');
    fetch('<?= BASE_URL ?>/master/hashtag/' + id + '/delete', { method: 'POST', body: form })
        .then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); })
        .catch(() => location.reload());
}
</script>

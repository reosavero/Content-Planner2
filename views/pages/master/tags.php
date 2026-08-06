<?php
$data = $data ?? [];
$hashtags = $hashtags ?? [];
?>
<div class="row g-4">
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Tags</h6>
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
                            <?php if (empty($data)): ?>
                                <tr>
                                    <td colspan="2" class="text-center text-tertiary py-4">Belum ada tag</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data as $row): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-soft" style="background:<?= htmlspecialchars($row['color'] ?? '#6c757d') ?>20;color:<?= htmlspecialchars($row['color'] ?? '#6c757d') ?>;">
                                                <?= htmlspecialchars($row['name']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-ghost text-danger" onclick="if(confirm('Hapus tag ini?')){deleteTag(<?= $row['id'] ?>)}" title="Hapus">
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
    </div>

    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Hashtag</h6>
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
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-ghost text-danger" onclick="if(confirm('Hapus hashtag ini?')){deleteHashtag(<?= $row['id'] ?>)}" title="Hapus">
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
    </div>
</div>

<script>
function showAddTag() {
    const div = document.createElement('div');
    div.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);';
    div.innerHTML = '<div style="background:var(--surface);border-radius:var(--radius-lg);padding:24px;max-width:400px;width:90%;">' +
        '<h6 style="margin-bottom:16px;">Tambah Tag</h6>' +
        '<form method="POST" action="<?= BASE_URL ?>/master/tags/store">' +
        '<input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Session::csrfToken() ?>">' +
        '<div class="form-group mb-3"><label class="form-label">Nama Tag</label><input type="text" name="name" class="form-control" required></div>' +
        '<div class="form-group mb-3"><label class="form-label">Warna</label><input type="color" name="color" class="form-control" style="height:40px;padding:4px;" value="#6c757d"></div>' +
        '<div class="d-flex gap-2 justify-content-end">' +
        '<button type="button" class="btn btn-ghost" onclick="this.closest(\'div\').closest(\'div\').remove()">Batal</button>' +
        '<button type="submit" class="btn btn-primary">Simpan</button>' +
        '</div></form></div>';
    document.body.appendChild(div);
}

function showAddHashtag() {
    const div = document.createElement('div');
    div.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);';
    div.innerHTML = '<div style="background:var(--surface);border-radius:var(--radius-lg);padding:24px;max-width:400px;width:90%;">' +
        '<h6 style="margin-bottom:16px;">Tambah Hashtag</h6>' +
        '<form method="POST" action="<?= BASE_URL ?>/master/hashtag/store">' +
        '<input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Session::csrfToken() ?>">' +
        '<div class="form-group mb-3"><label class="form-label">Hashtag</label><input type="text" name="name" class="form-control" placeholder="#tvrijatim" required></div>' +
        '<div class="d-flex gap-2 justify-content-end">' +
        '<button type="button" class="btn btn-ghost" onclick="this.closest(\'div\').closest(\'div\').remove()">Batal</button>' +
        '<button type="submit" class="btn btn-primary">Simpan</button>' +
        '</div></form></div>';
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

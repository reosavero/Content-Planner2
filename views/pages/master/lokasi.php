<?php
$data = $data ?? [];
?>

<div class="modal" id="modalLokasi">
    <div class="modal-backdrop" onclick="closeModal('modalLokasi')"></div>
    <div class="modal-content" style="max-width:500px;">
        <div class="modal-header">
            <h6 class="mb-0">Tambah Lokasi</h6>
            <button type="button" class="modal-close" onclick="closeModal('modalLokasi')">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/master/lokasi/store">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Session::csrfToken() ?>">
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label class="form-label">Nama Lokasi</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Kota</label>
                    <input type="text" name="city" class="form-control">
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Latitude</label>
                            <input type="text" name="latitude" class="form-control" placeholder="-7.250445">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Longitude</label>
                            <input type="text" name="longitude" class="form-control" placeholder="112.768845">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modalLokasi')">Batal</button>
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
                        <th>Nama Lokasi</th>
                        <th>Alamat</th>
                        <th>Kota</th>
                        <th>Koordinat</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="bi bi-geo-alt-fill" style="font-size:2rem;color:var(--text-tertiary);"></i>
                                    <p style="color:var(--text-tertiary);margin-top:8px;">Belum ada lokasi</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($row['name']) ?></td>
                                <td><small class="text-tertiary"><?= htmlspecialchars($row['address'] ?? '-') ?></small></td>
                                <td><span class="badge badge-soft"><?= htmlspecialchars($row['city'] ?? '-') ?></span></td>
                                <td>
                                    <?php if (!empty($row['latitude']) && !empty($row['longitude'])): ?>
                                        <small class="text-tertiary"><?= $row['latitude'] ?>, <?= $row['longitude'] ?></small>
                                    <?php else: ?>
                                        <small class="text-tertiary">-</small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-ghost" onclick="editLokasi(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['address'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($row['city'] ?? '', ENT_QUOTES) ?>', '<?= $row['latitude'] ?? '' ?>', '<?= $row['longitude'] ?? '' ?>')" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-ghost text-danger" onclick="if(confirm('Hapus lokasi ini?')){deleteLokasi(<?= $row['id'] ?>)}" title="Hapus">
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
function editLokasi(id, name, address, city, lat, lng) {
    const div = document.createElement('div');
    div.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);';
    div.innerHTML = '<div style="background:var(--surface);border-radius:var(--radius-lg);padding:24px;max-width:500px;width:90%;">' +
        '<h6 style="margin-bottom:16px;">Edit Lokasi</h6>' +
        '<form method="POST" action="<?= BASE_URL ?>/master/lokasi/' + id + '/update">' +
        '<input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Session::csrfToken() ?>">' +
        '<div class="form-group mb-3"><label class="form-label">Nama Lokasi</label><input type="text" name="name" class="form-control" value="' + name + '" required></div>' +
        '<div class="form-group mb-3"><label class="form-label">Alamat</label><textarea name="address" class="form-control" rows="2">' + address + '</textarea></div>' +
        '<div class="form-group mb-3"><label class="form-label">Kota</label><input type="text" name="city" class="form-control" value="' + city + '"></div>' +
        '<div class="row g-3"><div class="col-6"><div class="form-group"><label class="form-label">Latitude</label><input type="text" name="latitude" class="form-control" value="' + lat + '"></div></div>' +
        '<div class="col-6"><div class="form-group"><label class="form-label">Longitude</label><input type="text" name="longitude" class="form-control" value="' + lng + '"></div></div></div>' +
        '<div class="d-flex gap-2 justify-content-end mt-3">' +
        '<button type="button" class="btn btn-ghost" onclick="this.closest(\'div\').closest(\'div\').remove()">Batal</button>' +
        '<button type="submit" class="btn btn-primary">Update</button>' +
        '</div></form></div>';
    document.body.appendChild(div);
}

function deleteLokasi(id) {
    var form = new FormData();
    form.append('<?= CSRF_TOKEN_NAME ?>', '<?= Session::csrfToken() ?>');
    fetch('<?= BASE_URL ?>/master/lokasi/' + id + '/delete', { method: 'POST', body: form })
        .then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); })
        .catch(() => location.reload());
}
</script>

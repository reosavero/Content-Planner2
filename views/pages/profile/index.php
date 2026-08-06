<?php
$user = $user ?? [];
$stats = $stats ?? [];
?>

<div class="row">
    
    <div class="col-12 col-lg-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <div style="position: relative; display: inline-block;">
                    <img src="<?= !empty($user['avatar']) ? BASE_URL . '/uploads/' . $user['avatar'] : 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect fill=%22%23e4e7ed%22 width=%22100%22 height=%22100%22/><text x=%2250%22 y=%2265%22 font-size=%2240%22 fill=%22%239ca0a8%22 text-anchor=%22middle%22 font-weight=%22bold%22>' . strtoupper(substr($user['name'] ?? 'U', 0, 1)) . '</text></svg>' ?>" 
                         alt="" class="avatar-xl mb-3" style="width: 100px; height: 100px; border: 4px solid var(--gray-200);">
                    <button class="btn btn-icon btn-sm" style="position: absolute; bottom: 8px; right: 0; background: var(--tvri-blue); color: #fff; border-radius: 50%; width: 32px; height: 32px;" onclick="document.getElementById('avatarInput').click()">
                        <i class="bi bi-camera"></i>
                    </button>
                    <input type="file" id="avatarInput" accept="image/*" style="display: none;" onchange="uploadAvatar(this)">
                </div>
                <h5 style="font-weight: 700;"><?= Security::escape($user['name'] ?? '') ?></h5>
                <p style="color: var(--gray-500); font-size: 0.8125rem;">
                    <span class="badge badge-primary"><?= Security::escape($user['role_name'] ?? '') ?></span>
                </p>
                <p style="font-size: 0.8125rem; color: var(--gray-400);">
                    <i class="bi bi-envelope"></i> <?= Security::escape($user['email'] ?? '') ?><br>
                    <i class="bi bi-telephone"></i> <?= Security::escape($user['phone'] ?? '-') ?>
                </p>
            </div>
        </div>

        
        <div class="card mt-3">
            <div class="card-body">
                <h6 style="font-weight: 600; margin-bottom: 16px;">Statistik Saya</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span style="font-size: 0.8125rem; color: var(--gray-500);">Total Planning</span>
                    <span style="font-weight: 700;"><?= number_format($stats['total_planning'] ?? 0) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span style="font-size: 0.8125rem; color: var(--gray-500);">Berhasil Posting</span>
                    <span style="font-weight: 700; color: var(--success);"><?= number_format($stats['total_success'] ?? 0) ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span style="font-size: 0.8125rem; color: var(--gray-500);">Draft Active</span>
                    <span style="font-weight: 700; color: var(--warning);"><?= number_format($stats['total_draft'] ?? 0) ?></span>
                </div>
            </div>
        </div>
    </div>

    
    <div class="col-12 col-lg-8 mb-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="bi bi-person text-primary"></i> Edit Profile</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/profile/update">
                    <?= Session::csrfField() ?>
                    
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control" value="<?= Security::escape($user['name'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= Security::escape($user['email'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?= Security::escape($user['username'] ?? '') ?>" disabled>
                                <div class="form-text">Username tidak dapat diubah</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="form-label">Telepon</label>
                                <input type="text" name="phone" class="form-control" value="<?= Security::escape($user['phone'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Bio</label>
                                <textarea name="bio" class="form-control" rows="3"><?= Security::escape($user['bio'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Simpan Profile
                    </button>
                </form>
            </div>
        </div>

        
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-lock text-primary"></i> Ubah Password</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/profile/change-password">
                    <?= Session::csrfField() ?>
                    
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="form-label">Password Lama</label>
                                <input type="password" name="old_password" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="new_password" class="form-control" required minlength="8">
                                <div class="form-text">Minimal 8 karakter</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" name="new_password_confirm" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-key"></i> Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function uploadAvatar(input) {
    if (!input.files || !input.files[0]) return;
    
    const formData = new FormData();
    formData.append('avatar', input.files[0]);
    formData.append('_csrf_token', CSRF_TOKEN);
    
    $.ajax({
        url: BASE_URL + '/profile/update-avatar',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.success) {
                showToast('success', 'Berhasil', 'Avatar berhasil diperbarui');
                location.reload();
            } else {
                showToast('error', 'Gagal', res.message);
            }
        },
        error: function() {
            showToast('error', 'Gagal', 'Terjadi kesalahan saat upload');
        }
    });
}
</script>

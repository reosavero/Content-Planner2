<?php
$user = $user ?? [];
$stats = $stats ?? [];
$roleLabel = $user['role_name'] ?? 'User';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/vendor/cropperjs/cropper.min.css?v=<?= APP_VERSION ?>">

<style>
.cropper-crop-box,
.cropper-view-box,
.cropper-face {
    border-radius: 50% !important;
}
.cropper-view-box {
    outline: 0 !important;
}
.cropper-dashed {
    display: none !important;
}
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
.badge-status.assigned { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.profile-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--border-light, #e2e8f0);
    background: var(--surface-secondary, #f1f5f9);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.profile-avatar.clickable-avatar:hover {
    transform: scale(1.06);
    box-shadow: 0 6px 16px rgba(0, 51, 153, 0.18);
}
.profile-camera-btn {
    transition: transform 0.2s ease, background 0.2s ease;
}
.profile-camera-btn:hover {
    transform: scale(1.12);
}
.modal-close-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: #f1f5f9;
    color: #475569;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 16px;
    flex-shrink: 0;
    transition: background 0.2s ease, color 0.2s ease;
}
.modal-close-btn:hover {
    background: #fee2e2;
    color: #dc2626;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(18px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes popIn {
    from { opacity: 0; transform: scale(0.92) translateY(12px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.anim-fade-up { animation: fadeInUp 0.55s ease both; }
.anim-fade-in { animation: fadeIn 0.45s ease both; }
.modal.show { animation: fadeIn 0.2s ease; }
.modal.show .modal-content { animation: popIn 0.28s cubic-bezier(0.22, 1, 0.36, 1); }
@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
}
@keyframes popOut {
    from { opacity: 1; transform: scale(1) translateY(0); }
    to { opacity: 0; transform: scale(0.94) translateY(8px); }
}
.modal.closing { animation: fadeOut 0.2s ease both; }
.modal.closing .modal-content { animation: popOut 0.22s ease both; }
@media (prefers-reduced-motion: reduce) {
    .anim-fade-up, .anim-fade-in, .modal.show, .modal.show .modal-content,
    .modal.closing, .modal.closing .modal-content,
    .profile-avatar, .profile-camera-btn {
        animation: none !important;
        transition: none !important;
    }
}
</style>

<div class="container-fluid">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 anim-fade-up">
        <div>
            <h4 class="fw-bold mb-1">Profile Saya</h4>
            <p class="text-muted small mb-0">Kelola informasi akun Anda di TVRI Jawa Timur.</p>
        </div>
    </div>

    <div class="row">
        
        <div class="col-12 col-lg-4 mb-4 anim-fade-up" style="animation-delay: 80ms;">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div style="position: relative; display: inline-block;">
                        <img id="profileAvatarImg" src="<?= !empty($user['avatar']) ? BASE_URL . '/uploads/' . $user['avatar'] : 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect fill=%22%23e4e7ed%22 width=%22100%22 height=%22100%22/><text x=%2250%22 y=%2265%22 font-size=%2240%22 fill=%22%239ca0a8%22 text-anchor=%22middle%22 font-weight=%22bold%22>' . strtoupper(substr($user['name'] ?? 'U', 0, 1)) . '</text></svg>' ?>" 
                             alt="Foto Profile" class="profile-avatar mb-3<?= !empty($user['avatar']) ? ' clickable-avatar' : '' ?>" <?= !empty($user['avatar']) ? 'onclick="previewAvatar()" style="cursor: pointer;" title="Klik untuk melihat foto profile"' : '' ?>>
                        <button type="button" class="btn btn-icon btn-sm profile-camera-btn" style="position: absolute; bottom: 8px; right: 0; background: var(--tvri-blue); color: #fff; border-radius: 50%; width: 32px; height: 32px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);" onclick="document.getElementById('avatarInput').click()" title="Ubah Foto Profile">
                            <i class="bi bi-camera"></i>
                        </button>
                        <input type="file" id="avatarInput" accept="image/*" style="display: none;" onchange="uploadAvatar(this)">
                    </div>
                    <h5 style="font-weight: 700;"><?= Security::escape($user['name'] ?? '') ?></h5>
                    <p style="margin-bottom: 8px;">
                        <span class="badge-status assigned"><i class="bi bi-person-badge"></i> <?= Security::escape($roleLabel) ?></span>
                    </p>
                </div>
            </div>
        </div>

        
        <div class="col-12 col-lg-8 mb-4 anim-fade-up" style="animation-delay: 160ms;">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-2 d-flex align-items-center gap-2">
                    <h6 class="fw-bold mb-0"><i class="bi bi-person text-primary me-1"></i> Info Profile</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/profile/update">
                        <?= Session::csrfField() ?>
                        
                        <div class="row">
                            <div class="col-12 col-md-6 anim-fade-up" style="animation-delay: 200ms;">
                                <div class="form-group">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" value="<?= Security::escape($user['name'] ?? '') ?>" disabled>
                                    <input type="hidden" name="name" value="<?= Security::escape($user['name'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 anim-fade-up" style="animation-delay: 240ms;">
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" value="<?= Security::escape($user['email'] ?? '') ?>" disabled>
                                    <input type="hidden" name="email" value="<?= Security::escape($user['email'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 anim-fade-up" style="animation-delay: 280ms;">
                                <div class="form-group">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" value="<?= Security::escape($user['username'] ?? '') ?>" disabled>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 anim-fade-up" style="animation-delay: 320ms;">
                                <div class="form-group">
                                    <label class="form-label">Telepon</label>
                                    <input type="text" class="form-control" value="<?= Security::escape($user['phone'] ?? '') ?>" disabled>
                                    <input type="hidden" name="phone" value="<?= Security::escape($user['phone'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Atur Foto Profile -->
<div class="modal fade" id="cropAvatarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px; width: 90%; margin: 1.75rem auto;">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark fs-6"><i class="bi bi-crop me-1"></i> Atur Foto Profile</h5>
                <button type="button" class="modal-close-btn" onclick="closeCropModal()" aria-label="Close" title="Tutup"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body">
                <p class="small text-secondary mb-3">Geser & atur ukuran lingkaran foto, lalu klik <strong>Simpan Foto</strong>.</p>
                <div style="max-height: 55vh; overflow: hidden; text-align: center;">
                    <img id="cropAvatarImage" src="" alt="Foto Profile" style="max-width: 100%; max-height: 55vh; display: block; margin: 0 auto;">
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn" onclick="closeCropModal()" style="height: 40px; font-weight: 600; font-size: 14px; background: #f1f5f9; color: #475569; border: none; border-radius: 10px; padding: 0 18px;">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSaveAvatar" onclick="saveCroppedAvatar()" style="height: 40px; font-weight: 600; font-size: 14px; border-radius: 10px; padding: 0 18px;">
                    <i class="bi bi-check-lg me-1"></i> Simpan Foto
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview Foto Profile -->
<div class="modal fade" id="previewAvatarModal" tabindex="-1" aria-hidden="true" onclick="if(event.target===this){closePreviewModal();}">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px; width: 90%; margin: 1.75rem auto;">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark fs-6"><i class="bi bi-person-circle me-1"></i> Foto Profile</h5>
                <button type="button" class="modal-close-btn" onclick="closePreviewModal()" aria-label="Close" title="Tutup"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body text-center">
                <img id="previewAvatarImage" src="" alt="Foto Profile" style="width: 360px; height: 360px; max-width: 100%; max-height: 65vh; object-fit: cover; border-radius: 50%; border: 4px solid var(--border-light, #e2e8f0); display: inline-block; margin: 0 auto;">
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/vendor/cropperjs/cropper.min.js?v=<?= APP_VERSION ?>"></script>
<script>
var cropperInstance = null;
var pendingAvatarFile = null;

function previewAvatar() {
    var img = document.getElementById('profileAvatarImg');
    var previewImg = document.getElementById('previewAvatarImage');
    if (!img || !previewImg || !img.src) return;
    previewImg.src = img.src;
    var modalEl = document.getElementById('previewAvatarModal');
    if (!modalEl) return;
    modalEl.classList.add('show');
    modalEl.style.display = 'flex';
    document.body.classList.add('modal-open');
}

function animateModalClose(modalEl, extraCleanup) {
    if (!modalEl || modalEl.classList.contains('closing')) return;

    var finish = function() {
        modalEl.classList.remove('closing');
        modalEl.classList.remove('show');
        modalEl.style.display = 'none';
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        var backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(function(b) { b.remove(); });
        if (typeof extraCleanup === 'function') extraCleanup();
    };

    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        finish();
        return;
    }

    modalEl.classList.add('closing');
    var done = false;
    var runFinish = function() {
        if (done) return;
        done = true;
        finish();
    };
    modalEl.addEventListener('animationend', runFinish, { once: true });
    setTimeout(runFinish, 300);
}

function closePreviewModal() {
    animateModalClose(document.getElementById('previewAvatarModal'));
}

function uploadAvatar(input) {
    if (!input.files || !input.files[0]) return;

    pendingAvatarFile = input.files[0];
    var file = input.files[0];
    var reader = new FileReader();
    reader.onload = function(e) {
        var img = document.getElementById('cropAvatarImage');
        img.onload = function() {
            openCropModal();
            try {
                if (typeof Cropper === 'undefined') {
                    throw new Error('library Cropper tidak dimuat');
                }
                if (cropperInstance) cropperInstance.destroy();
                cropperInstance = new Cropper(img, {
                    aspectRatio: 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.9,
                    minCropBoxWidth: 80,
                    minCropBoxHeight: 80,
                    background: false,
                    responsive: true,
                    guides: false,
                    center: true,
                    highlight: false,
                    movable: true,
                    rotatable: false,
                    scalable: false,
                    zoomable: true,
                    zoomOnTouch: true,
                    zoomOnWheel: true
                });
            } catch (err) {
                if (typeof showToast === 'function') {
                    showToast('error', 'Gagal', 'Tidak dapat membuat lingkaran crop: ' + err.message);
                }
            }
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
    input.value = '';
}

function openCropModal() {
    var modalEl = document.getElementById('cropAvatarModal');
    if (!modalEl) return;
    modalEl.classList.add('show');
    modalEl.style.display = 'flex';
    document.body.classList.add('modal-open');
}

function closeCropModal() {
    animateModalClose(document.getElementById('cropAvatarModal'), function() {
        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }
    });
}

function uploadAvatarDirect(file) {
    if (!file) return;
    var formData = new FormData();
    formData.append('avatar', file);
    formData.append('_csrf_token', CSRF_TOKEN);
    $.ajax({
        url: BASE_URL + '/profile/update-avatar',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            closeCropModal();
            if (res && res.success) {
                showToast('success', 'Berhasil', 'Foto profile berhasil diperbarui');
                setTimeout(function() { location.reload(); }, 800);
            } else {
                showToast('error', 'Gagal', (res && res.message) || 'Terjadi kesalahan saat upload');
            }
        },
        error: function() {
            closeCropModal();
            showToast('error', 'Gagal', 'Terjadi kesalahan saat upload');
        }
    });
}

function saveCroppedAvatar() {
    var btn = document.getElementById('btnSaveAvatar');
    if (btn) btn.disabled = true;

    if (!cropperInstance || typeof Cropper === 'undefined') {
        uploadAvatarDirect(pendingAvatarFile);
        return;
    }

    try {
        var canvas = cropperInstance.getCroppedCanvas({
            width: 400,
            height: 400,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });

        var outCanvas = document.createElement('canvas');
        outCanvas.width = 400;
        outCanvas.height = 400;
        var ctx = outCanvas.getContext('2d');
        ctx.beginPath();
        ctx.arc(200, 200, 200, 0, Math.PI * 2);
        ctx.closePath();
        ctx.clip();
        ctx.drawImage(canvas, 0, 0, 400, 400);

        outCanvas.toBlob(function(blob) {
            if (!blob) {
                if (btn) btn.disabled = false;
                uploadAvatarDirect(pendingAvatarFile);
                return;
            }
            var formData = new FormData();
            formData.append('avatar', blob, 'avatar.png');
            formData.append('_csrf_token', CSRF_TOKEN);

            $.ajax({
                url: BASE_URL + '/profile/update-avatar',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (btn) btn.disabled = false;
                    closeCropModal();
                    if (res && res.success) {
                        showToast('success', 'Berhasil', 'Foto profile berhasil diperbarui');
                        setTimeout(function() { location.reload(); }, 800);
                    } else {
                        showToast('error', 'Gagal', (res && res.message) || 'Terjadi kesalahan saat upload');
                    }
                },
                error: function() {
                    if (btn) btn.disabled = false;
                    closeCropModal();
                    showToast('error', 'Gagal', 'Terjadi kesalahan saat upload');
                }
            });
        }, 'image/png');
    } catch (e) {
        if (btn) btn.disabled = false;
        if (typeof showToast === 'function') {
            showToast('error', 'Gagal', 'Gagal memproses foto: ' + e.message);
        }
    }
}
</script>

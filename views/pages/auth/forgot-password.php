<div class="register-wizard-container">

    <div class="step-indicator" id="stepIndicator">
        <div class="step-item active" data-step="1">
            <div class="step-number">1</div>
            <span class="step-label">Email</span>
        </div>
        <div class="step-line"></div>
        <div class="step-item" data-step="2">
            <div class="step-number">2</div>
            <span class="step-label">Verifikasi OTP</span>
        </div>
        <div class="step-line"></div>
        <div class="step-item" data-step="3">
            <div class="step-number">3</div>
            <span class="step-label">Password Baru</span>
        </div>
    </div>




    <div class="wizard-step-content" id="step1Content">
        <form id="formStep1" autocomplete="off" novalidate>
            <?= Session::csrfField() ?>

            <div class="form-group">
                <label class="form-label" for="fpEmail">Email</label>
                <div class="input-wrapper">
                    <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                    <input type="email" id="fpEmail" name="email" class="form-control" placeholder="Masukkan email terdaftar" required>
                </div>
                <small class="text-muted" style="font-size: 12px; display: block; margin-top: 6px; color: #6b7280;">Kode verifikasi akan dikirimkan ke email Anda.</small>
            </div>

            <div class="d-flex flex-column gap-2" style="margin-top: 24px;">
                <a href="<?= BASE_URL ?>/login" class="btn btn-secondary w-100 text-center" style="text-decoration: none;">Kembali</a>
                <button type="button" class="btn btn-primary w-100" id="btnStep1Next" onclick="submitStep1()">
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




    <div class="wizard-step-content" id="step2Content" style="display: none;">
        <div class="text-center mb-4">
            <div class="otp-icon-wrapper mb-3" style="width: 56px; height: 56px; margin: 0 auto; background: #e8f0fe; color: #003399; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
            </div>
            <h5 style="margin: 0 0 6px 0; font-weight: 600;">Verifikasi Email</h5>
            <p style="font-size: 13px; color: #666; margin: 0;">Masukkan 6 digit kode verifikasi yang telah dikirim ke <br><strong id="otpTargetEmail" style="color: #003399;">-</strong></p>
        </div>

        <form id="formStep2" autocomplete="off">
            <div class="form-group mb-4">
                <div class="otp-input-container">
                    <input type="text" id="fpOtp" name="otp" class="form-control text-center text-tracking-widest"
                           placeholder="0 0 0 0 0 0" maxlength="6"
                           style="font-size: 24px; font-weight: bold; letter-spacing: 12px; height: 56px;" required>
                </div>
            </div>

            <div class="text-center mb-4">
                <span class="text-muted" style="font-size: 13px;">Tidak menerima kode? </span>
                <button type="button" class="btn-link-action" id="btnResendOtp" onclick="resendOtp()" style="background: none; border: none; color: #003399; font-weight: 600; font-size: 13px; cursor: pointer; text-decoration: underline;">Kirim Ulang</button>
            </div>

            <div class="d-flex justify-content-between align-items-center gap-3" style="margin-top: 24px;">
                <button type="button" class="btn btn-secondary" onclick="goToStep(1)">Kembali</button>
                <button type="button" class="btn btn-primary" id="btnStep2Next" onclick="submitStep2()">
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




    <div class="wizard-step-content" id="step3Content" style="display: none;">
        <form id="formStep3" autocomplete="off">
            <div class="form-group">
                <label class="form-label" for="fpPassword">Password Baru</label>
                <div class="input-wrapper">
                    <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <input type="password" id="fpPassword" name="password" class="form-control" placeholder="Minimal 8 karakter" required>
                    <button type="button" class="password-toggle" onclick="togglePassword(this)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="fpPasswordConfirm">Konfirmasi Password Baru</label>
                <div class="input-wrapper">
                    <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <input type="password" id="fpPasswordConfirm" name="password_confirm" class="form-control" placeholder="Ulangi Password Baru" required>
                </div>
            </div>

            <div class="d-flex flex-column gap-2" style="margin-top: 24px;">
                <button type="button" class="btn btn-secondary w-100" onclick="goToStep(2)">Kembali</button>
                <button type="button" class="btn btn-success w-100" id="btnStep3Finish" onclick="submitStep3()">
                    <span class="btn-text">Selesai</span>
                    <span class="btn-loader" style="display: none;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="spin">
                            <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
                        </svg> Memproses…
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>

.step-indicator {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    padding: 0 10px;
}

.step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    position: relative;
    z-index: 2;
}

.step-number {
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

.step-label {
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
    transition: all 0.3s ease;
}

.step-item.active .step-number {
    background: #003399;
    color: #ffffff;
    box-shadow: 0 0 0 4px rgba(0, 51, 153, 0.15);
}

.step-item.active .step-label {
    color: #003399;
}

.step-item.completed .step-number {
    background: #2e7d32;
    color: #ffffff;
}

.step-line {
    flex: 1;
    height: 2px;
    background: #e2e8f0;
    margin: 0 8px;
    transform: translateY(-10px);
}

.step-line.active {
    background: #003399;
}


.text-tracking-widest {
    letter-spacing: 8px;
}
.btn-block {
    width: 100%;
}
.btn-secondary {
    background-color: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
    padding: 8px 16px;
    border-radius: 6px;
    font-weight: 500;
}
.btn-secondary:hover {
    background-color: #e2e8f0;
}
.btn-success {
    background-color: #2e7d32;
    color: #ffffff;
    border: none;
    padding: 8px 20px;
    border-radius: 6px;
    font-weight: 600;
}
.btn-success:hover {
    background-color: #1b5e20;
}
</style>

<script>
var currentForgotEmail = '';

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function showWebDropdown(msg, type) {

    var container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        container.id = 'toastContainer';
        document.body.appendChild(container);
    }

    var normalizedType = type === 'danger' ? 'error' : (type || 'info');
    var icons = {
        success: 'check-circle-fill',
        error: 'x-circle-fill',
        warning: 'exclamation-triangle-fill',
        info: 'info-circle-fill'
    };
    var iconName = icons[normalizedType] || icons.info;

    var toast = document.createElement('div');
    toast.className = 'sonner-toast sonner-toast-' + normalizedType;
    toast.innerHTML =
        '<span class="sonner-icon">' +
            '<i class="bi bi-' + iconName + '"></i>' +
        '</span>' +
        '<div class="sonner-content">' +
            '<div class="sonner-message">' + escapeHtml(msg) + '</div>' +
        '</div>';

    container.appendChild(toast);

    setTimeout(function() {
        dismissSonnerToastEl(toast);
    }, 3500);
}

function showAlert(msg, type) {

    showWebDropdown(msg, type || 'danger');
}

function goToStep(step) {
    document.querySelectorAll('.wizard-step-content').forEach(function(el) {
        el.style.display = 'none';
    });

    document.getElementById('step' + step + 'Content').style.display = 'block';


    document.querySelectorAll('.step-item').forEach(function(item) {
        var s = parseInt(item.getAttribute('data-step'));
        item.classList.remove('active', 'completed');
        if (s === step) {
            item.classList.add('active');
        } else if (s < step) {
            item.classList.add('completed');
        }
    });

    var lines = document.querySelectorAll('.step-line');
    if (lines.length > 0) {
        lines[0].classList.toggle('active', step > 1);
        lines[1].classList.toggle('active', step > 2);
    }
}

function setBtnLoading(btnId, isLoading) {
    var btn = document.getElementById(btnId);
    if (!btn) return;
    var textEl = btn.querySelector('.btn-text');
    var loaderEl = btn.querySelector('.btn-loader');
    btn.disabled = isLoading;
    if (textEl) textEl.style.display = isLoading ? 'none' : 'inline';
    if (loaderEl) loaderEl.style.display = isLoading ? 'inline-flex' : 'none';
}


function togglePassword(btn) {
    var wrapper = btn.closest('.input-wrapper');
    var input = wrapper.querySelector('input');
    var iconSvg = btn.querySelector('svg');
    if (input.type === 'password') {
        input.type = 'text';
        iconSvg.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
    } else {
        input.type = 'password';
        iconSvg.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
    }
}


function submitStep1() {
    var email = document.getElementById('fpEmail').value.trim();

    if (!email) {
        showAlert('Silakan masukkan email Anda.');
        return;
    }

    setBtnLoading('btnStep1Next', true);

    var formData = new FormData(document.getElementById('formStep1'));

    fetch('<?= BASE_URL ?>/forgot-password', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(response) {
        return response.text().then(function(text) {
            try {
                return JSON.parse(text);
            } catch (e) {
                return { success: false, message: 'Respon server tidak valid.' };
            }
        });
    })
    .then(function(data) {
        setBtnLoading('btnStep1Next', false);
        if (data.success) {
            currentForgotEmail = email;
            document.getElementById('otpTargetEmail').textContent = email;
            goToStep(2);
        } else {
            showAlert(data.message || 'Terjadi kesalahan saat memproses email.');
        }
    })
    .catch(function(err) {
        setBtnLoading('btnStep1Next', false);
        showAlert('Terjadi kesalahan koneksi server.');
    });
}

function submitStep2() {
    var otp = document.getElementById('fpOtp').value.trim();
    if (!otp || otp.length < 4) {
        showAlert('Masukkan 6 digit kode OTP verifikasi.');
        return;
    }

    setBtnLoading('btnStep2Next', true);

    var formData = new FormData();
    formData.append('otp', otp);
    formData.append('email', currentForgotEmail);
    formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= Session::csrfToken() ?>');

    fetch('<?= BASE_URL ?>/forgot-password/verify-otp', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(response) {
        return response.text().then(function(text) {
            try {
                return JSON.parse(text);
            } catch (e) {
                return { success: false, message: 'Respon server tidak valid.' };
            }
        });
    })
    .then(function(data) {
        setBtnLoading('btnStep2Next', false);
        if (data.success) {
            goToStep(3);
        } else {
            showAlert(data.message || 'Kode OTP tidak sesuai.');
        }
    })
    .catch(function(err) {
        setBtnLoading('btnStep2Next', false);
        showAlert('Terjadi kesalahan koneksi server.');
    });
}

function resendOtp() {
    var email = currentForgotEmail || document.getElementById('fpEmail').value.trim();
    if (!email) {
        showAlert('Email tidak ditemukan. Silakan kembali ke Langkah 1.', 'danger');
        return;
    }

    var btnResend = document.getElementById('btnResendOtp');
    if (btnResend) btnResend.disabled = true;

    var formData = new FormData();
    formData.append('email', email);
    formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= Session::csrfToken() ?>');

    fetch('<?= BASE_URL ?>/forgot-password', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(response) {
        return response.text().then(function(text) {
            try {
                return JSON.parse(text);
            } catch (e) {
                return { success: false, message: 'Respon server tidak valid.' };
            }
        });
    })
    .then(function(data) {
        if (btnResend) btnResend.disabled = false;
        if (data.success) {
            showWebDropdown('Kode OTP baru telah berhasil dikirimkan ke email ' + email + '.', 'success');
        } else {
            showWebDropdown(data.message || 'Gagal mengirim ulang OTP.', 'danger');
        }
    })
    .catch(function(err) {
        if (btnResend) btnResend.disabled = false;
        showWebDropdown('Terjadi kesalahan koneksi server.', 'danger');
    });
}

function submitStep3() {
    var password = document.getElementById('fpPassword').value;
    var passwordConfirm = document.getElementById('fpPasswordConfirm').value;

    if (!password || !passwordConfirm) {
        showAlert('Silakan isi password baru dan konfirmasi password.');
        return;
    }

    if (password !== passwordConfirm) {
        showAlert('Konfirmasi password tidak cocok dengan password.');
        return;
    }

    setBtnLoading('btnStep3Finish', true);

    var formData = new FormData();
    formData.append('password', password);
    formData.append('password_confirm', passwordConfirm);
    formData.append('email', currentForgotEmail);
    formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= Session::csrfToken() ?>');

    fetch('<?= BASE_URL ?>/forgot-password/complete', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(response) {
        return response.text().then(function(text) {
            try {
                return JSON.parse(text);
            } catch (e) {
                return { success: false, message: 'Respon server tidak valid.' };
            }
        });
    })
    .then(function(data) {
        setBtnLoading('btnStep3Finish', false);
        if (data.success) {
            showWebDropdown(data.message || 'Password berhasil diubah. Silakan login dengan password baru.', 'success');
            setTimeout(function() {
                var target = data.redirect || '<?= BASE_URL ?>/login';
                if (target.indexOf('/') === 0 && target.indexOf('http') !== 0) {
                    target = '<?= BASE_URL ?>' + target;
                }
                window.location.href = target;
            }, 1200);
        } else {
            showAlert(data.message || 'Gagal menyelesaikan reset password.');
        }
    })
    .catch(function(err) {
        setBtnLoading('btnStep3Finish', false);
        showAlert('Terjadi kesalahan server: ' + (err.message || 'Gagal terhubung.'));
    });
}
</script>

<div class="register-wizard-container">

    <div class="step-indicator" id="stepIndicator">
        <div class="step-item active" data-step="1">
            <div class="step-number">1</div>
            <span class="step-label">Data Diri</span>
        </div>
        <div class="step-line"></div>
        <div class="step-item" data-step="2">
            <div class="step-number">2</div>
            <span class="step-label">Verifikasi OTP</span>
        </div>
        <div class="step-line"></div>
        <div class="step-item" data-step="3">
            <div class="step-number">3</div>
            <span class="step-label">Kredensial</span>
        </div>
    </div>


    <div id="registerAlert" class="alert alert-danger" style="display: none; margin-bottom: 20px;"></div>




    <div class="wizard-step-content" id="step1Content">
        <form id="formStep1" autocomplete="off" novalidate>
            <?= Session::csrfField() ?>

            <div class="form-group">
                <label class="form-label" for="regName">Nama Lengkap</label>
                <div class="input-wrapper">
                    <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <input type="text" id="regName" name="name" class="form-control" placeholder="Nama Lengkap" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="regJurusan">Jurusan / Program Studi</label>
                <div class="input-wrapper">
                    <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                    </svg>
                    <input type="text" id="regJurusan" name="jurusan" class="form-control" placeholder="Jurusan / Program Studi" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="regEmail">Email</label>
                <div class="input-wrapper">
                    <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                    <input type="email" id="regEmail" name="email" class="form-control" placeholder="email@gmail.com" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="regPhone">NO. HP</label>
                <div class="input-wrapper">
                    <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                    <input type="tel" id="regPhone" name="phone" class="form-control" placeholder="08xxxxxxxxxx" required>
                </div>
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
                    <input type="text" id="regOtp" name="otp" class="form-control text-center text-tracking-widest"
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
                <label class="form-label" for="regUsername">Username</label>
                <div class="input-wrapper">
                    <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <input type="text" id="regUsername" name="username" class="form-control" placeholder="Username" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="regPassword">Password</label>
                <div class="input-wrapper">
                    <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <input type="password" id="regPassword" name="password" class="form-control" placeholder="Minimal 8 karakter" required>
                    <button type="button" class="password-toggle" onclick="togglePassword(this)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="regPasswordConfirm">Konfirmasi Password</label>
                <div class="input-wrapper">
                    <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <input type="password" id="regPasswordConfirm" name="password_confirm" class="form-control" placeholder="Ulangi Password" required>
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




    <div class="wizard-step-content" id="step4Content" style="display: none; text-align: center; padding: 20px 10px;">
        <div class="success-status-icon mb-4" style="width: 72px; height: 72px; margin: 0 auto; background: #fff8e1; color: #f57c00; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>

        <h4 style="font-weight: 700; color: #1a202c; margin-bottom: 12px;">Pendaftaran Berhasil!</h4>

        <div class="status-box mb-4" style="background: #f8fafc; border: 1px dashed #cbd5e1; padding: 16px; border-radius: 8px;">
            <p style="font-size: 15px; color: #334155; margin: 0; line-height: 1.6;">
                Akun Anda telah terdaftar dan saat ini <strong>Menunggu Konfirmasi dari Admin</strong>.
            </p>
        </div>

        <p style="font-size: 13px; color: #64748b; margin-bottom: 28px; line-height: 1.5;">
            Setelah Admin atau Super Admin melakukan konfirmasi, Anda akan menerima pemberitahuan resmi melalui email yang telah terdaftar.
        </p>

        <a href="<?= BASE_URL ?>/login" class="btn btn-primary btn-block" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; height: 44px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7"></path>
            </svg>
            Kembali ke Halaman Login
        </a>
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

.wizard-step-content {
    animation: wizardStepEntrance 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes wizardStepEntrance {
    from {
        opacity: 0;
        transform: translateY(14px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
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


.web-toast-dropdown {
    position: fixed;
    top: 24px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 99999;
    min-width: 300px;
    max-width: 90%;
    animation: slideDownFade 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

.web-toast-content {
    padding: 12px 20px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 10px;
}

.web-toast-success {
    background-color: #10b981;
    color: #ffffff;
}

.web-toast-danger {
    background-color: #ef4444;
    color: #ffffff;
}

@keyframes slideDownFade {
    0% {
        transform: translate(-50%, -24px);
        opacity: 0;
    }
    100% {
        transform: translate(-50%, 0);
        opacity: 1;
    }
}
</style>

<script>
var currentRegisteredEmail = '';
var toastTimeout = null;

function showWebDropdown(msg, type) {
    type = type || 'success';
    var dropdown = document.getElementById('webNotificationDropdown');
    var content = document.getElementById('webNotificationContent');
    if (!dropdown || !content) return;

    var icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
    content.className = 'web-toast-content web-toast-' + (type === 'success' ? 'success' : 'danger');
    content.innerHTML = '<i class="bi ' + icon + ' fs-5"></i> <span>' + msg + '</span>';

    dropdown.style.display = 'block';

    if (toastTimeout) clearTimeout(toastTimeout);
    toastTimeout = setTimeout(function() {
        dropdown.style.display = 'none';
    }, 4500);
}

function showAlert(msg, type) {
    type = type || 'danger';
    var alertEl = document.getElementById('registerAlert');
    alertEl.className = 'alert alert-' + type;
    alertEl.style.borderRadius = '8px';
    alertEl.style.fontSize = '14px';
    alertEl.style.display = 'block';

    var icon = type === 'success' ? '<i class="bi bi-check-circle-fill me-2"></i>' : '<i class="bi bi-exclamation-triangle-fill me-2"></i>';
    alertEl.innerHTML = icon + msg;
}

function hideAlert() {
    var alertEl = document.getElementById('registerAlert');
    alertEl.style.display = 'none';
}

function goToStep(step) {
    hideAlert();
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


function showPageLoading(text) {
    var overlay = document.getElementById('pageLoadingOverlay');
    if (!overlay) return;
    var overlayText = overlay.querySelector('.page-loading-text');
    if (overlayText) overlayText.textContent = text || 'Memproses...';
    overlay.classList.add('show');
}

function hidePageLoading() {
    var overlay = document.getElementById('pageLoadingOverlay');
    if (overlay) overlay.classList.remove('show');
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
    hideAlert();
    var name = document.getElementById('regName').value.trim();
    var jurusan = document.getElementById('regJurusan').value.trim();
    var email = document.getElementById('regEmail').value.trim();
    var phone = document.getElementById('regPhone').value.trim();

    if (!name || !jurusan || !email || !phone) {
        showAlert('Silakan lengkapi semua kolom data diri.');
        return;
    }

    if (!email.toLowerCase().endsWith('@gmail.com')) {
        showAlert('Pendaftaran hanya dapat menggunakan alamat email @gmail.com.');
        return;
    }

    setBtnLoading('btnStep1Next', true);
    showPageLoading('Mengirim Kode OTP...');

    var formData = new FormData(document.getElementById('formStep1'));

    fetch('<?= BASE_URL ?>/register/send-otp', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        setBtnLoading('btnStep1Next', false);
        hidePageLoading();
        if (data.success) {
            currentRegisteredEmail = email;
            document.getElementById('otpTargetEmail').textContent = email;
            goToStep(2);
        } else {
            showAlert(data.message || 'Terjadi kesalahan saat mengikis data.');
        }
    })
    .catch(err => {
        setBtnLoading('btnStep1Next', false);
        hidePageLoading();
        showAlert('Terjadi kesalahan koneksi server.');
    });
}

function submitStep2() {
    hideAlert();
    var otp = document.getElementById('regOtp').value.trim();
    if (!otp || otp.length < 4) {
        showAlert('Masukkan 6 digit kode OTP verifikasi.');
        return;
    }

    setBtnLoading('btnStep2Next', true);
    showPageLoading('Memverifikasi Kode OTP...');

    var formData = new FormData();
    formData.append('otp', otp);
    formData.append('email', currentRegisteredEmail);
    formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= Session::csrfToken() ?>');

    fetch('<?= BASE_URL ?>/register/verify-otp', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        setBtnLoading('btnStep2Next', false);
        hidePageLoading();
        if (data.success) {
            goToStep(3);
        } else {
            showAlert(data.message || 'Kode OTP tidak sesuai.');
        }
    })
    .catch(err => {
        setBtnLoading('btnStep2Next', false);
        hidePageLoading();
        showAlert('Terjadi kesalahan koneksi server.');
    });
}

function resendOtp() {
    hideAlert();
    var email = currentRegisteredEmail || document.getElementById('regEmail').value.trim();
    if (!email) {
        showAlert('Email tidak ditemukan. Silakan kembali ke Langkah 1.', 'danger');
        return;
    }

    var btnResend = document.getElementById('btnResendOtp');
    if (btnResend) btnResend.disabled = true;

    var formData = new FormData(document.getElementById('formStep1'));

    fetch('<?= BASE_URL ?>/register/send-otp', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (btnResend) btnResend.disabled = false;
        if (data.success) {
            showWebDropdown('Kode OTP baru telah berhasil dikirimkan ke ' + email, 'success');
        } else {
            showWebDropdown(data.message || 'Gagal mengirim ulang OTP.', 'danger');
        }
    })
    .catch(err => {
        if (btnResend) btnResend.disabled = false;
        showWebDropdown('Terjadi kesalahan koneksi server.', 'danger');
    });
}

function submitStep3() {
    hideAlert();
    var username = document.getElementById('regUsername').value.trim();
    var password = document.getElementById('regPassword').value;
    var passwordConfirm = document.getElementById('regPasswordConfirm').value;

    if (!username || !password || !passwordConfirm) {
        showAlert('Silakan isi username, password, dan konfirmasi password.');
        return;
    }

    if (password !== passwordConfirm) {
        showAlert('Konfirmasi password tidak cocok dengan password.');
        return;
    }

    setBtnLoading('btnStep3Finish', true);

    var formData = new FormData();
    formData.append('username', username);
    formData.append('password', password);
    formData.append('password_confirm', passwordConfirm);
    formData.append('email', currentRegisteredEmail);
    formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= Session::csrfToken() ?>');

    fetch('<?= BASE_URL ?>/register/complete', {
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
            goToStep(4);
            var indicator = document.getElementById('stepIndicator');
            if (indicator) indicator.style.display = 'none';
        } else {
            showAlert(data.message || 'Gagal menyelesaikan pendaftaran.');
        }
    })
    .catch(function(err) {
        setBtnLoading('btnStep3Finish', false);
        showAlert('Terjadi kesalahan server: ' + (err.message || 'Gagal terhubung.'));
    });
}
</script>

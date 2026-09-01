<?php





class AuthController extends Controller
{
    


    public function showLoginForm(): void
    {
        if (Session::isLoggedIn()) {
            $userRole = Session::get('user_role_slug');
            $landing = in_array($userRole, ['superadmin', 'admin']) ? '/planning' : '/dashboard';
            $this->redirect($landing);
            return;
        }

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        $this->layout = 'layouts/auth';
        $this->view('auth/login', [
            'title' => 'Content Planner - Website Content Planner & Auto Post Social Media TVRI Jawa Timur'
        ]);
    }

    


    public function login(): void
    {
        if (Session::isLoggedIn()) {
            $userRole = Session::get('user_role_slug');
            $landing = in_array($userRole, ['superadmin', 'admin']) ? '/planning' : '/dashboard';
            $this->redirect($landing);
            return;
        }

        
        $ip = $_SERVER['REMOTE_ADDR'];
        $attempts = Database::fetchColumn(
            "SELECT COUNT(*) FROM login_attempts 
             WHERE ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE) AND success = 0",
            [$ip, LOGIN_LOCKOUT_TIME]
        );
        
        if ($attempts >= LOGIN_MAX_ATTEMPTS) {
            if ($this->isAjax()) {
                $this->error('Terlalu banyak percobaan login. Silakan coba lagi dalam ' . LOGIN_LOCKOUT_TIME . ' menit.', 429);
            }
            Session::setFlash('error', 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . LOGIN_LOCKOUT_TIME . ' menit.');
            $this->redirect('/login');
            return;
        }

        
        $username = Security::sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if (empty($username) || empty($password)) {
            Session::setFlash('error', 'Username dan Password wajib diisi');
            $this->redirect('/login');
            return;
        }

        
        $user = Database::fetch(
            "SELECT u.*, r.slug as role_slug, r.name as role_name 
             FROM users u 
             JOIN roles r ON r.id = u.role_id 
             WHERE u.username = ? AND u.deleted_at IS NULL",
            [$username]
        );

        
        if (!$user) {
            $user = Database::fetch(
                "SELECT u.*, r.slug as role_slug, r.name as role_name 
                 FROM users u 
                 JOIN roles r ON r.id = u.role_id 
                 WHERE u.email = ? AND u.deleted_at IS NULL",
                [$username]
            );
        }

        if (!$user || !Security::verifyPassword($password, $user['password'])) {
            Database::execute(
                "INSERT INTO login_attempts (email, ip_address, user_agent, success) VALUES (?, ?, ?, 0)",
                [$username, $ip, $_SERVER['HTTP_USER_AGENT'] ?? '']
            );
            Session::setFlash('error', 'Username atau Password salah');
            $this->redirect('/login');
            return;
        }

        
        if (isset($user['approval_status']) && $user['approval_status'] === 'pending') {
            Session::setFlash('warning', 'Akun Anda masih menunggu konfirmasi dari Admin. Silakan cek email Anda secara berkala.');
            $this->redirect('/login');
            return;
        }

        if (isset($user['approval_status']) && $user['approval_status'] === 'rejected') {
            $reasonInfo = !empty($user['rejection_reason']) ? ' Catatan Admin: ' . $user['rejection_reason'] : '';
            Session::setFlash('error', 'Pendaftaran akun Anda ditolak oleh Admin.' . $reasonInfo);
            $this->redirect('/login');
            return;
        }

        if (!$user['is_active']) {
            Session::setFlash('error', 'Akun Anda sedang dinonaktifkan.');
            $this->redirect('/login');
            return;
        }

        
        if (!empty($user['twofa_enabled']) && $user['twofa_enabled']) {
            Session::set('2fa_user_id', $user['id']);
            Session::set('2fa_required', true);
            $this->redirect('/login/2fa');
            return;
        }

        
        $this->authenticateUser($user, $remember);
    }

    


    public function verify2fa(): void
    {
        if (!Session::get('2fa_required')) {
            $this->redirect('/login');
            return;
        }

        $code = $_POST['code'] ?? '';
        $userId = Session::get('2fa_user_id');
        $user = Database::fetch(
            "SELECT u.*, r.slug as role_slug, r.name as role_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.id = ?",
            [$userId]
        );

        if (!$user) {
            Session::setFlash('error', 'Sesi tidak valid');
            $this->redirect('/login');
            return;
        }

        
        require_once HELPERS_PATH . 'TOTP.php';
        $totp = new TOTP($user['twofa_secret']);
        
        if (!$totp->verify($code)) {
            Session::setFlash('error', 'Kode 2FA salah');
            $this->redirect('/login/2fa');
            return;
        }

        Session::remove('2fa_required');
        Session::remove('2fa_user_id');
        $this->authenticateUser($user, false);
    }

    


    private function authenticateUser(array $user, bool $remember): void
    {
        $savedIntendedUrl = Session::get('intended_url');

        
        $_SESSION = [];
        session_regenerate_id(true);
        
        
        Session::set('user_id', $user['id']);
        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);
        Session::set('user_username', $user['username']);
        Session::set('user_role_id', $user['role_id']);
        Session::set('user_role_slug', $user['role_slug']);
        Session::set('user_avatar', $user['avatar']);
        Session::set('logged_in', true);
        Session::set('last_activity', time());
        Session::set('session_timeout_minutes', $user['session_timeout_minutes'] ?? 60);

        
        if ($remember) {
            $token = Security::generateToken(64);
            Database::execute(
                "UPDATE users SET remember_token = ? WHERE id = ?",
                [$token, $user['id']]
            );
            setcookie('remember_token', $token, time() + 86400 * 30, '/', '', isset($_SERVER['HTTPS']), true);
        }

        
        Database::execute(
            "UPDATE users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?",
            [$_SERVER['REMOTE_ADDR'], $user['id']]
        );

        
        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, description, ip_address, user_agent) 
             VALUES (?, ?, 'login', 'auth', 'User login', ?, ?)",
            [$user['id'], $user['role_id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '']
        );

        
        Session::generateCsrfToken();

        
        if (in_array($user['role_slug'], ['superadmin', 'admin'], true)) {
            $intendedUrl = (empty($savedIntendedUrl) || $savedIntendedUrl === '/dashboard' || $savedIntendedUrl === '/')
                ? '/planning'
                : $savedIntendedUrl;
        } else {
            $intendedUrl = ($user['role_slug'] === 'magang') ? '/dashboard' : ($savedIntendedUrl ?? '/dashboard');
        }

        Session::remove('intended_url');
        $this->redirect($intendedUrl);
    }

    


    public function logout(): void
    {
        if (Session::isLoggedIn()) {
            
            $userId = Session::get('user_id');
            $userExists = false;
            if ($userId) {
                $user = Database::fetch("SELECT id FROM users WHERE id = ?", [$userId]);
                $userExists = !empty($user);
            }

            if ($userExists) {
                Database::execute(
                    "INSERT INTO activity_logs (user_id, role_id, action, module, description, ip_address, user_agent) 
                     VALUES (?, ?, 'logout', 'auth', 'User logout', ?, ?)",
                    [$userId, Session::get('user_role_id'), $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '']
                );
            }
        }

        
        setcookie('remember_token', '', time() - 3600, '/');
        
        Session::destroy();
        $this->redirect('/login');
    }

    


    public function showForgotPassword(): void
    {
        $this->layout = 'layouts/auth';
        $this->view('auth/forgot-password', [
            'title' => 'Lupa Password - Content Planner TVRI Jawa Timur'
        ]);
    }

    


    public function sendResetLink(): void
    {
        $email = Security::sanitize($_POST['email'] ?? '');

        if (!Security::isValidEmail($email)) {
            $this->json(['success' => false, 'message' => 'Format email tidak valid.'], 422);
            return;
        }

        $user = Database::fetch(
            "SELECT * FROM users WHERE email = ? AND deleted_at IS NULL",
            [$email]
        );

        if (!$user) {
            $this->json(['success' => false, 'message' => 'Email tidak terdaftar. Silakan periksa kembali.'], 422);
            return;
        }

        if (!$user['is_active']) {
            $this->json(['success' => false, 'message' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Admin.'], 422);
            return;
        }

        
        $otpCode = sprintf('%06d', random_int(0, 999999));
        $expiresAt = date('Y-m-d H:i:s', time() + (15 * 60)); 

        $payload = json_encode(['user_id' => $user['id']], JSON_UNESCAPED_UNICODE);

        
        Database::execute(
            "INSERT INTO otp_verifications (email, otp_code, payload, expires_at, is_verified, created_at)
             VALUES (?, ?, ?, ?, 0, NOW())",
            [$email, $otpCode, $payload, $expiresAt]
        );

        Session::set('forgot_email', $email);

        
        Mail::sendPasswordResetOtp($email, $user['name'], $otpCode);

        $this->json([
            'success' => true,
            'message' => 'Kode verifikasi telah dikirimkan ke email ' . $email,
            'email' => $email
        ]);
    }

    


    public function verifyResetOtp(): void
    {
        $otp = trim($_POST['otp'] ?? '');
        $email = Session::get('forgot_email') ?: Security::sanitize($_POST['email'] ?? '');

        if (empty($otp) || strlen($otp) < 4) {
            $this->json(['success' => false, 'message' => 'Masukkan kode verifikasi (OTP) dengan benar.'], 422);
            return;
        }

        if (empty($email)) {
            $this->json(['success' => false, 'message' => 'Sesi lupa password tidak ditemukan. Silakan mulai ulang.'], 422);
            return;
        }

        
        $record = Database::fetch(
            "SELECT * FROM otp_verifications 
             WHERE email = ? AND otp_code = ? AND expires_at > NOW() 
             ORDER BY id DESC LIMIT 1",
            [$email, $otp]
        );

        if (!$record) {
            $this->json(['success' => false, 'message' => 'Kode OTP salah atau telah kadaluarsa.'], 422);
            return;
        }

        
        Database::execute("UPDATE otp_verifications SET is_verified = 1 WHERE id = ?", [$record['id']]);

        Session::set('forgot_verified_email', $email);

        $this->json([
            'success' => true,
            'message' => 'Kode verifikasi berhasil diverifikasi.'
        ]);
    }

    


    public function completeResetPassword(): void
    {
        try {
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';

            $email = Session::get('forgot_verified_email') ?: Security::sanitize($_POST['email'] ?? '');

            if (empty($email)) {
                $this->json(['success' => false, 'message' => 'Sesi verifikasi OTP tidak ditemukan. Silakan ulang dari awal.'], 422);
                return;
            }

            
            $passwordCheck = Security::isStrongPassword($password);
            if (!$passwordCheck['valid']) {
                $this->json(['success' => false, 'message' => implode('<br>', $passwordCheck['errors'])], 422);
                return;
            }

            if ($password !== $passwordConfirm) {
                $this->json(['success' => false, 'message' => 'Konfirmasi password tidak cocok.'], 422);
                return;
            }

            
            $otpRecord = Database::fetch(
                "SELECT * FROM otp_verifications WHERE email = ? AND is_verified = 1 ORDER BY id DESC LIMIT 1",
                [$email]
            );

            if (!$otpRecord) {
                $this->json(['success' => false, 'message' => 'Sesi verifikasi OTP telah kadaluarsa. Silakan ulangi proses.'], 422);
                return;
            }

            $user = Database::fetch(
                "SELECT u.*, r.slug as role_slug, r.name as role_name
                 FROM users u
                 JOIN roles r ON r.id = u.role_id
                 WHERE u.email = ? AND u.deleted_at IS NULL",
                [$email]
            );

            if (!$user) {
                $this->json(['success' => false, 'message' => 'Akun tidak ditemukan. Silakan mulai ulang.'], 422);
                return;
            }

            if (!$user['is_active']) {
                $this->json(['success' => false, 'message' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Admin.'], 422);
                return;
            }

            
            Database::execute(
                "UPDATE users SET password = ?, password_reset_token = NULL, password_reset_expires = NULL, updated_at = NOW() WHERE id = ?",
                [Security::hashPassword($password), $user['id']]
            );

            
            Database::execute(
                "UPDATE otp_verifications SET is_verified = 0 WHERE email = ? AND id <> ?",
                [$email, $otpRecord['id']]
            );

            
            Session::remove('forgot_email');
            Session::remove('forgot_verified_email');

            $this->json([
                'success' => true,
                'message' => 'Password berhasil diubah. Silakan login dengan password baru.',
                'redirect' => BASE_URL . '/login'
            ]);
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => 'Gagal memproses reset password: ' . $e->getMessage()], 500);
        }
    }

    


    public function showResetPassword(string $token): void
    {
        $user = Database::fetch(
            "SELECT * FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW() AND deleted_at IS NULL",
            [$token]
        );

        if (!$user) {
            Session::setFlash('error', 'Tautan reset password tidak valid atau sudah kadaluarsa');
            $this->redirect('/login');
            return;
        }

        $this->layout = 'layouts/auth';
        $this->view('auth/reset-password', [
            'title' => 'Reset Password - Content Planner TVRI Jawa Timur',
            'token' => $token
        ]);
    }

    


    public function resetPassword(): void
    {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['password_confirm'] ?? '';

        $user = Database::fetch(
            "SELECT * FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW() AND deleted_at IS NULL",
            [$token]
        );

        if (!$user) {
            Session::setFlash('error', 'Tautan reset password tidak valid');
            $this->redirect('/login');
            return;
        }

        
        $passwordCheck = Security::isStrongPassword($password);
        if (!$passwordCheck['valid']) {
            Session::setFlash('error', implode('<br>', $passwordCheck['errors']));
            $this->redirect('/reset-password/' . $token);
            return;
        }

        if ($password !== $confirmPassword) {
            Session::setFlash('error', 'Konfirmasi password tidak cocok');
            $this->redirect('/reset-password/' . $token);
            return;
        }

        
        Database::execute(
            "UPDATE users SET password = ?, password_reset_token = NULL, password_reset_expires = NULL WHERE id = ?",
            [Security::hashPassword($password), $user['id']]
        );

        Session::setFlash('success', 'Password berhasil direset. Silakan login dengan password baru.');
        $this->redirect('/login');
    }

    


    public function generateCaptcha(): void
    {
        $code = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 5);
        Session::set('captcha_code', $code);

        
        $width = 150;
        $height = 50;
        $image = imagecreatetruecolor($width, $height);
        
        
        $bg = imagecolorallocate($image, 245, 245, 255);
        $textColor = imagecolorallocate($image, 33, 37, 41);
        $lineColor = imagecolorallocate($image, 200, 200, 220);
        
        imagefill($image, 0, 0, $bg);
        
        
        for ($i = 0; $i < 5; $i++) {
            imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $lineColor);
        }
        
        
        $fontSize = 20;
        $x = 20;
        for ($i = 0; $i < strlen($code); $i++) {
            $angle = rand(-15, 15);
            $y = rand(35, 40);
            imagechar($image, $fontSize, $x, $y - 15, $code[$i], $textColor);
            $x += 25;
        }
        
        header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
        exit;
    }
}

<?php





class RegisterController extends Controller
{
    


    public function showRegisterForm(): void
    {
        if (Session::isLoggedIn()) {
            $userRole = Session::get('user_role_slug');
            $landing = in_array($userRole, ['superadmin', 'admin'], true) ? '/planning' : '/dashboard';
            $this->redirect($landing);
            return;
        }

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        $this->layout = 'layouts/auth';
        $this->view('auth/register', [
            'title' => 'Buat Akun Magang - Content Planner TVRI Jawa Timur'
        ]);
    }

    


    public function sendOtp(): void
    {
        $name = Security::sanitize($_POST['name'] ?? '');
        $jurusan = Security::sanitize($_POST['jurusan'] ?? '');
        $email = Security::sanitize($_POST['email'] ?? '');
        $phone = Security::sanitize($_POST['phone'] ?? '');

        if (empty($name) || strlen($name) < 3) {
            $this->json(['success' => false, 'message' => 'Nama lengkap wajib diisi (minimal 3 karakter).'], 422);
            return;
        }

        if (empty($jurusan)) {
            $this->json(['success' => false, 'message' => 'Jurusan wajib diisi.'], 422);
            return;
        }

        if (!Security::isValidEmail($email) || !str_ends_with(strtolower($email), '@gmail.com')) {
            $this->json(['success' => false, 'message' => 'Pendaftaran hanya dapat menggunakan alamat email @gmail.com.'], 422);
            return;
        }

        if (empty($phone)) {
            $this->json(['success' => false, 'message' => 'Nomor HP wajib diisi.'], 422);
            return;
        }

        
        $existingUser = Database::fetch(
            "SELECT id FROM users WHERE email = ? AND deleted_at IS NULL",
            [$email]
        );

        if ($existingUser) {
            $this->json(['success' => false, 'message' => 'Email sudah terdaftar. Silakan gunakan email lain atau login.'], 422);
            return;
        }

        
        $otpCode = sprintf('%06d', random_int(0, 999999));
        $expiresAt = date('Y-m-d H:i:s', time() + (15 * 60)); 

        $payload = json_encode([
            'name' => $name,
            'jurusan' => $jurusan,
            'phone' => $phone
        ], JSON_UNESCAPED_UNICODE);

        
        Database::execute(
            "INSERT INTO otp_verifications (email, otp_code, payload, expires_at, is_verified, created_at)
             VALUES (?, ?, ?, ?, 0, NOW())",
            [$email, $otpCode, $payload, $expiresAt]
        );

        Session::set('register_email', $email);

        
        Mail::sendOtp($email, $name, $otpCode);

        $this->json([
            'success' => true,
            'message' => 'Kode OTP verifikasi telah dikirimkan ke email ' . $email,
            'email' => $email
        ]);
    }

    


    public function verifyOtp(): void
    {
        $otp = trim($_POST['otp'] ?? '');
        $email = Session::get('register_email') ?: Security::sanitize($_POST['email'] ?? '');

        if (empty($otp) || strlen($otp) < 4) {
            $this->json(['success' => false, 'message' => 'Masukkan kode verifikasi (OTP) dengan benar.'], 422);
            return;
        }

        if (empty($email)) {
            $this->json(['success' => false, 'message' => 'Sesi pendaftaran tidak ditemukan. Silakan mulai ulang.'], 422);
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

        Session::set('otp_verified_email', $email);
        Session::set('otp_record_id', $record['id']);

        $this->json([
            'success' => true,
            'message' => 'Kode verifikasi berhasil diverifikasi.'
        ]);
    }

    


    public function complete(): void
    {
        try {
            $username = trim(Security::sanitize($_POST['username'] ?? ''));
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';

            $isSuperAdminCreating = (Session::isLoggedIn() && Session::get('user_role_slug') === 'superadmin');

            $email = Session::get('otp_verified_email') ?: Security::sanitize($_POST['email'] ?? '');

            if (empty($email)) {
                $this->json(['success' => false, 'message' => 'Sesi pendaftaran atau email tidak ditemukan.'], 422);
                return;
            }

            
            if (empty($username) || strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                $this->json(['success' => false, 'message' => 'Username wajib diisi (minimal 3 karakter, huruf/angka/underscore).'], 422);
                return;
            }

            
            $existingUsername = Database::fetch(
                "SELECT id FROM users WHERE username = ? AND deleted_at IS NULL",
                [$username]
            );

            if ($existingUsername) {
                $this->json(['success' => false, 'message' => 'Username sudah digunakan. Silakan gunakan username lain.'], 422);
                return;
            }

            
            if (empty($password) || strlen($password) < 8) {
                $this->json(['success' => false, 'message' => 'Password wajib diisi (minimal 8 karakter).'], 422);
                return;
            }

            if ($password !== $passwordConfirm) {
                $this->json(['success' => false, 'message' => 'Konfirmasi password tidak cocok.'], 422);
                return;
            }

            if ($isSuperAdminCreating) {
                $name = Security::sanitize($_POST['name'] ?? '');
                $jurusan = Security::sanitize($_POST['jurusan'] ?? '');
                $phone = Security::sanitize($_POST['phone'] ?? '');
            } else {
                $otpRecord = Database::fetch(
                    "SELECT * FROM otp_verifications WHERE email = ? AND is_verified = 1 ORDER BY id DESC LIMIT 1",
                    [$email]
                );

                if (!$otpRecord) {
                    $this->json(['success' => false, 'message' => 'Sesi verifikasi OTP telah kadaluarsa. Silakan ulangi pendaftaran.'], 422);
                    return;
                }

                $payload = json_decode($otpRecord['payload'] ?? '{}', true);
                $name = $payload['name'] ?? 'User Magang';
                $jurusan = $payload['jurusan'] ?? '';
                $phone = $payload['phone'] ?? '';
            }

            $targetRole = Security::sanitize($_POST['target_role'] ?? '');
            $userRoleSlug = Session::get('user_role_slug');

            if (in_array($targetRole, ['admin', 'superadmin'], true) && Session::isLoggedIn() && $userRoleSlug === 'superadmin') {
                $role = Database::fetch("SELECT id FROM roles WHERE slug IN ('{$targetRole}', 'super_admin') LIMIT 1");
                $roleId = $role['id'] ?? ($targetRole === 'superadmin' ? 1 : 2);
                $approvalStatus = 'approved';
                $isActive = 1;
            } else {
                $role = Database::fetch("SELECT id FROM roles WHERE slug = 'magang'");
                if (!$role) {
                    Database::execute("INSERT INTO roles (slug, name, description) VALUES ('magang', 'Magang', 'User magang dengan akses terbatas')");
                    $roleId = Database::lastInsertId();
                } else {
                    $roleId = $role['id'];
                }
                $approvalStatus = (Session::isLoggedIn() && $userRoleSlug === 'superadmin') ? 'approved' : 'pending';
                $isActive = (Session::isLoggedIn() && $userRoleSlug === 'superadmin') ? 1 : 0;
            }

            
            $existingEmail = Database::fetch("SELECT id FROM users WHERE email = ? AND deleted_at IS NULL", [$email]);
            if ($existingEmail) {
                $this->json(['success' => false, 'message' => 'Email ' . $email . ' sudah terdaftar.'], 422);
                return;
            }

            
            Database::execute(
                "UPDATE users 
                 SET email = CONCAT(email, '__deleted_', id, '_', UNIX_TIMESTAMP()),
                     username = CONCAT(username, '__deleted_', id, '_', UNIX_TIMESTAMP())
                 WHERE (email = ? OR username = ?) AND deleted_at IS NOT NULL",
                [$email, $username]
            );

            
            $passwordHash = Security::hashPassword($password);
            $userId = Database::insert(
                "INSERT INTO users (role_id, name, email, username, password, phone, jurusan, is_active, approval_status, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                [$roleId, $name, $email, $username, $passwordHash, $phone, $jurusan, $isActive, $approvalStatus]
            );

            if (!$userId) {
                $this->json(['success' => false, 'message' => 'Gagal membuat akun user. Silakan coba lagi.'], 500);
                return;
            }

            Database::execute(
                "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
                 VALUES (?, ?, 'register', 'auth', 'users', ?, ?, ?)",
                [$userId, $roleId, $userId, 'Registrasi akun baru: ' . $name . ' (' . $email . ')', $_SERVER['REMOTE_ADDR']]
            );

            
            if ($approvalStatus === 'pending') {
                try {
                    require_once HELPERS_PATH . 'Notification.php';
                    $admins = Database::fetchAll(
                        "SELECT u.id FROM users u JOIN roles r ON r.id = u.role_id 
                         WHERE r.slug IN ('admin', 'superadmin') AND u.is_active = 1 AND u.deleted_at IS NULL"
                    );
                    $adminIds = array_values(array_unique(array_map('intval', array_column($admins, 'id'))));
                    if (!empty($adminIds)) {
                        Notification::createBulk(
                            $adminIds,
                            'Pendaftaran magang baru',
                            "Magang baru \"{$name}\" ({$email}) telah mendaftar.",
                            'warning',
                            '/intern-users',
                            'bi-person-plus'
                        );
                    }
                } catch (\Throwable $e) {
                    error_log('Notifikasi pendaftaran magang gagal: ' . $e->getMessage());
                }
            }

            
            Session::remove('register_email');
            Session::remove('otp_verified_email');
            Session::remove('otp_record_id');

            $this->json([
                'success' => true,
                'message' => 'Pendaftaran akun berhasil diselesaikan!'
            ]);
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => 'Gagal memproses pendaftaran: ' . $e->getMessage()], 500);
        }
    }
}

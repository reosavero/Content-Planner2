<?php




class ProfileController extends Controller
{
    


    public function index(): void
    {
        $userId = Session::get('user_id');
        $user = Database::fetch(
            "SELECT u.*, r.name as role_name, r.slug as role_slug 
             FROM users u 
             JOIN roles r ON r.id = u.role_id 
             WHERE u.id = ? AND u.deleted_at IS NULL",
            [$userId]
        );

        if (!$user) {
            $this->redirectWith('/dashboard', 'error', 'User tidak ditemukan.');
        }

        
        $stats = [
            'total_planning' => Database::fetchColumn("SELECT COUNT(*) FROM planning_konten WHERE created_by = ? AND deleted_at IS NULL", [$userId]),
            'total_success' => Database::fetchColumn("SELECT COUNT(*) FROM planning_konten WHERE created_by = ? AND status = 'success' AND deleted_at IS NULL", [$userId]),
            'total_draft' => Database::fetchColumn("SELECT COUNT(*) FROM planning_konten WHERE created_by = ? AND status = 'draft' AND deleted_at IS NULL", [$userId]),
            'recent_activity' => Database::fetchAll(
                "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
                [$userId]
            ),
        ];

        $this->view('profile/index', [
            'title' => 'Profile Saya',
            'user' => $user,
            'stats' => $stats,
            'breadcrumbs' => [
                ['label' => 'Profile', 'url' => '#'],
            ],
        ]);
    }

    


    public function update(): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid.');
        }

        $userId = Session::get('user_id');
        $name = Security::sanitize($_POST['name'] ?? '');
        $email = Security::sanitize($_POST['email'] ?? '');
        $phone = Security::sanitize($_POST['phone'] ?? '');
        $bio = Security::sanitize($_POST['bio'] ?? '');

        if (empty($name)) {
            $this->redirectBackWith('error', 'Nama wajib diisi.');
        }

        if (!Security::isValidEmail($email)) {
            $this->redirectBackWith('error', 'Email tidak valid.');
        }

        
        $existing = Database::fetchColumn(
            "SELECT COUNT(*) FROM users WHERE email = ? AND id != ? AND deleted_at IS NULL",
            [$email, $userId]
        );
        if ($existing > 0) {
            $this->redirectBackWith('error', 'Email sudah digunakan oleh user lain.');
        }

        Database::execute(
            "UPDATE users SET name = ?, email = ?, phone = ?, bio = ?, updated_at = NOW() WHERE id = ?",
            [$name, $email, $phone, $bio, $userId]
        );

        
        Session::set('user_name', $name);
        Session::set('user_email', $email);

        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address) 
             VALUES (?, ?, 'update', 'profile', 'users', ?, 'Mengupdate profile', ?)",
            [$userId, Session::get('user_role_id'), $userId, $_SERVER['REMOTE_ADDR']]
        );

        $this->redirectBackWith('success', 'Profile berhasil diperbarui.');
    }

    


    public function changePassword(): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid.');
        }

        $userId = Session::get('user_id');
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['new_password_confirm'] ?? '';

        
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        if (!$user || !Security::verifyPassword($oldPassword, $user['password'])) {
            $this->redirectBackWith('error', 'Password lama salah.');
        }

        
        $check = Security::isStrongPassword($newPassword);
        if (!$check['valid']) {
            $this->redirectBackWith('error', implode('<br>', $check['errors']));
        }

        if ($newPassword !== $confirmPassword) {
            $this->redirectBackWith('error', 'Konfirmasi password baru tidak cocok.');
        }

        Database::execute(
            "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?",
            [Security::hashPassword($newPassword), $userId]
        );

        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address) 
             VALUES (?, ?, 'change_password', 'profile', 'users', ?, 'Mengubah password', ?)",
            [$userId, Session::get('user_role_id'), $userId, $_SERVER['REMOTE_ADDR']]
        );

        $this->redirectBackWith('success', 'Password berhasil diubah.');
    }

    


    public function updateAvatar(): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid'], 403);
        }

        if (empty($_FILES['avatar'])) {
            $this->json(['success' => false, 'message' => 'Tidak ada file yang diupload'], 400);
        }

        $file = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 2097152; 

        $validation = Security::validateFile($file, $allowedTypes, $maxSize);
        if (!$validation['valid']) {
            $this->error(implode(', ', $validation['errors']));
        }

        $userId = Session::get('user_id');
        $filename = Security::generateFilename($file['name']);
        $uploadPath = UPLOADS_PATH . 'avatars/';
        
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $uploadPath . $filename)) {
            
            $oldAvatar = Database::fetchColumn("SELECT avatar FROM users WHERE id = ?", [$userId]);
            if ($oldAvatar && file_exists(UPLOADS_PATH . $oldAvatar)) {
                unlink(UPLOADS_PATH . $oldAvatar);
            }

            $avatarPath = 'avatars/' . $filename;
            Database::execute(
                "UPDATE users SET avatar = ?, updated_at = NOW() WHERE id = ?",
                [$avatarPath, $userId]
            );

            Session::set('user_avatar', $avatarPath);

            $this->success([
                'url' => BASE_URL . '/uploads/' . $avatarPath,
            ], 'Avatar berhasil diperbarui.');
        } else {
            $this->error('Gagal mengupload avatar.');
        }
    }
}

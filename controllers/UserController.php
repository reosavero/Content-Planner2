<?php




class UserController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $search = Security::sanitize($_GET['search'] ?? '');
        $roleId = $_GET['role_id'] ?? '';

        $where = "u.deleted_at IS NULL";
        $params = [];

        if (!empty($search)) {
            $where .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)";
            $t = "%{$search}%";
            $params = [$t, $t, $t];
        }
        if (!empty($roleId)) {
            $where .= " AND u.role_id = ?";
            $params[] = $roleId;
        }

        $count = Database::fetchColumn(
            "SELECT COUNT(*) FROM users u WHERE {$where}", $params
        );

        $data = Database::fetchAll(
            "SELECT u.*, r.name as role_name, r.slug as role_slug,
                    (SELECT COUNT(*) FROM planning_konten WHERE created_by = u.id AND deleted_at IS NULL) as total_planning
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE {$where}
             ORDER BY u.created_at DESC
             LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage),
            $params
        );

        $roles = Database::fetchAll("SELECT * FROM roles ORDER BY id");

        $this->view('users/index', [
            'title' => 'Manajemen User',
            'data' => $data,
            'roles' => $roles,
            'pagination' => ['current_page' => $page, 'total_pages' => max(1, ceil($count / $perPage)), 'total' => $count],
            'filters' => ['search' => $search, 'role_id' => $roleId],
            'breadcrumbs' => [['label' => 'Pengaturan', 'url' => '#'], ['label' => 'Users', 'url' => '#']],
            'pageActions' => [
                ['label' => 'Tambah User', 'icon' => 'bi-plus', 'variant' => 'primary', 'onclick' => "window.location.href='" . BASE_URL . "/users/create'"],
            ],
        ]);
    }

    public function create(): void
    {
        $roles = Database::fetchAll("SELECT * FROM roles ORDER BY id");
        $this->view('users/create', [
            'title' => 'Tambah User Baru',
            'roles' => $roles,
            'breadcrumbs' => [['label' => 'Users', 'url' => '/users'], ['label' => 'Tambah', 'url' => '#']],
        ]);
    }

    public function store(): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid.');
        }

        $data = $this->validate($_POST, [
            'name' => 'required|min:3|max:150',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|min:3|max:100|unique:users,username',
            'password' => 'required|min:8',
            'role_id' => 'required|numeric',
            'phone' => 'max:30',
        ]);

        
        Database::execute(
            "UPDATE users 
             SET email = CONCAT(email, '__deleted_', id, '_', UNIX_TIMESTAMP()),
                 username = CONCAT(username, '__deleted_', id, '_', UNIX_TIMESTAMP())
             WHERE (email = ? OR username = ?) AND deleted_at IS NOT NULL",
            [$data['email'], $data['username']]
        );

        Database::execute(
            "INSERT INTO users (role_id, name, email, username, password, phone, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())",
            [
                $data['role_id'], $data['name'], $data['email'], $data['username'],
                Security::hashPassword($data['password']), $data['phone'] ?? ''
            ]
        );

        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
             VALUES (?, ?, 'create', 'users', 'users', ?, ?, ?)",
            [Session::get('user_id'), Session::get('user_role_id'), (int)Database::getInstance()->lastInsertId(), 'User baru ditambahkan: ' . $data['name'] . ' (' . $data['email'] . ')', $_SERVER['REMOTE_ADDR']]
        );

        $this->redirectWith('/users', 'success', 'User berhasil ditambahkan.');
    }

    public function edit(string $id): void
    {
        $user = Database::fetch("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$user) {
            $this->redirectWith('/users', 'error', 'User tidak ditemukan.');
        }
        $roles = Database::fetchAll("SELECT * FROM roles ORDER BY id");
        $this->view('users/edit', [
            'title' => 'Edit User: ' . $user['name'],
            'user' => $user,
            'roles' => $roles,
            'breadcrumbs' => [['label' => 'Users', 'url' => '/users'], ['label' => 'Edit', 'url' => '#']],
        ]);
    }

    public function update(string $id): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid.');
        }

        $user = Database::fetch("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$user) {
            $this->redirectWith('/users', 'error', 'User tidak ditemukan.');
        }

        $data = $this->validate($_POST, [
            'name' => 'required|min:3|max:150',
            'email' => 'required|email|unique:users,email,' . $id,
            'username' => 'required|min:3|max:100|unique:users,username,' . $id,
            'role_id' => 'required|numeric',
            'phone' => 'max:30',
        ]);

        $sql = "UPDATE users SET role_id = ?, name = ?, email = ?, username = ?, phone = ?, updated_at = NOW() WHERE id = ?";
        $params = [$data['role_id'], $data['name'], $data['email'], $data['username'], $data['phone'] ?? '', $id];

        
        if (!empty($_POST['password'])) {
            $sql = "UPDATE users SET role_id = ?, name = ?, email = ?, username = ?, phone = ?, password = ?, updated_at = NOW() WHERE id = ?";
            $params = [$data['role_id'], $data['name'], $data['email'], $data['username'], $data['phone'] ?? '', Security::hashPassword($_POST['password']), $id];
        }

        Database::execute($sql, $params);
        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
             VALUES (?, ?, 'update', 'users', 'users', ?, ?, ?)",
            [Session::get('user_id'), Session::get('user_role_id'), (int)$id, 'User "' . $user['name'] . '" diperbarui', $_SERVER['REMOTE_ADDR']]
        );
        $this->redirectWith('/users', 'success', 'User berhasil diupdate.');
    }

    public function delete(string $id): void
    {
        $csrfToken = $_POST[CSRF_TOKEN_NAME] ?? $_GET['csrf_token'] ?? $_POST['csrf_token'] ?? $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Session::validateCsrfToken($csrfToken)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
            }
            $this->redirectWith('/users', 'error', 'Token CSRF tidak valid.');
        }

        if ($id == Session::get('user_id')) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Tidak dapat menghapus akun sendiri.'], 400);
            }
            $this->redirectWith('/users', 'error', 'Tidak dapat menghapus akun sendiri.');
        }

        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'User tidak ditemukan.'], 404);
            }
            $this->redirectWith('/users', 'error', 'User tidak ditemukan.');
        }

        $currentUserId = Session::get('user_id');

        try {
            $safeExecute = function (string $sql, array $params = []): void {
                try {
                    Database::execute($sql, $params);
                } catch (\Throwable $e) {
                    // Ignore if table or column doesn't exist in database
                }
            };

            
            $safeExecute("UPDATE planning_konten SET created_by = ? WHERE created_by = ?", [$currentUserId, $id]);
            $safeExecute("UPDATE planning_konten SET pic_id = NULL WHERE pic_id = ?", [$id]);
            $safeExecute("UPDATE planning_konten SET editor_id = NULL WHERE editor_id = ?", [$id]);
            $safeExecute("UPDATE planning_konten SET approved_by = NULL WHERE approved_by = ?", [$id]);
            $safeExecute("UPDATE planning_konten SET rechecked_by = NULL WHERE rechecked_by = ?", [$id]);

            
            $safeExecute("UPDATE recheck_logs SET approved_by = NULL WHERE approved_by = ?", [$id]);
            $safeExecute("UPDATE backup_logs SET performed_by = NULL WHERE performed_by = ?", [$id]);
            $safeExecute("UPDATE posting_logs SET performed_by = NULL WHERE performed_by = ?", [$id]);
            $safeExecute("UPDATE lokasi_shooting SET created_by = NULL WHERE created_by = ?", [$id]);
            $safeExecute("UPDATE lokasi_shooting SET updated_by = NULL WHERE updated_by = ?", [$id]);
            $safeExecute("UPDATE calendar_notes SET created_by = NULL WHERE created_by = ?", [$id]);

            
            $safeExecute("DELETE FROM activity_logs WHERE user_id = ?", [$id]);
            $safeExecute("DELETE FROM notifications WHERE user_id = ?", [$id]);
            $safeExecute("DELETE FROM notifications WHERE pegawai_id = ?", [$id]);
            $safeExecute("DELETE FROM platform_akun WHERE user_id = ?", [$id]);
            $safeExecute("DELETE FROM platform_accounts WHERE user_id = ?", [$id]);
            $safeExecute("DELETE FROM user_sessions WHERE user_id = ?", [$id]);

            
            $safeExecute(
                "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
                 VALUES (?, ?, 'delete', 'users', 'users', ?, ?, ?)",
                [$currentUserId, Session::get('user_role_id'), (int)$id, 'User "' . $user['name'] . '" dihapus dari database', $_SERVER['REMOTE_ADDR']]
            );

            
            Database::execute("DELETE FROM users WHERE id = ?", [$id]);

            $msg = 'User "' . htmlspecialchars($user['name']) . '" berhasil dihapus dari database.';
            Session::setFlash('success', $msg);

            if ($this->isAjax()) {
                $this->json(['success' => true, 'message' => $msg]);
            } else {
                $this->redirectWith('/users', 'success', $msg);
            }
        } catch (\Throwable $e) {
            error_log('Gagal hapus user: ' . $e->getMessage());
            $errorMsg = 'Gagal menghapus user: ' . $e->getMessage();
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $errorMsg], 500);
            } else {
                $this->redirectWith('/users', 'error', $errorMsg);
            }
        }
    }

    public function toggleActive(string $id): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid'], 403);
        }

        $user = Database::fetch("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User tidak ditemukan.']);
        }

        $newStatus = $user['is_active'] ? 0 : 1;
        Database::execute("UPDATE users SET is_active = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $id]);
        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
             VALUES (?, ?, 'update', 'users', 'users', ?, ?, ?)",
            [Session::get('user_id'), Session::get('user_role_id'), (int)$id, 'User "' . $user['name'] . '" ' . ($newStatus ? 'diaktifkan' : 'dinonaktifkan'), $_SERVER['REMOTE_ADDR']]
        );

        $this->json([
            'success' => true,
            'message' => $newStatus ? 'User diaktifkan.' : 'User dinonaktifkan.',
            'is_active' => $newStatus
        ]);
    }
}

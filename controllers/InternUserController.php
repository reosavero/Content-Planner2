<?php




class InternUserController extends Controller
{
    


    private function checkAccess(): void
    {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $roleSlug = Session::get('user_role_slug');
        if (!in_array($roleSlug, ['superadmin', 'admin'])) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Akses ditolak. Hanya Admin dan Super Admin yang dapat mengakses.'], 403);
            }
            Session::setFlash('error', 'Akses ditolak. Hanya Admin dan Super Admin yang dapat mengelola user magang.');
            $this->redirect('/dashboard');
        }
    }

    


    public function index(): void
    {
        $this->checkAccess();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $search = Security::sanitize($_GET['search'] ?? '');
        $statusFilter = Security::sanitize($_GET['status'] ?? 'all');

        $userRoleSlug = Session::get('user_role_slug');
        $roleFilter = Security::sanitize($_GET['role'] ?? 'all');

        $where = "u.deleted_at IS NULL";
        $params = [];

        if ($userRoleSlug === 'superadmin') {
            if ($roleFilter === 'admin') {
                $where .= " AND r.slug = 'admin'";
            } elseif ($roleFilter === 'magang') {
                $where .= " AND r.slug = 'magang'";
            } else {
                $where .= " AND r.slug IN ('admin', 'magang')";
            }
        } else {
            $where .= " AND r.slug = 'magang'";
        }

        if (!empty($search)) {
            $where .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.username LIKE ? OR u.jurusan LIKE ?)";
            $t = "%{$search}%";
            $params[] = $t;
            $params[] = $t;
            $params[] = $t;
            $params[] = $t;
        }

        if (in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
            $where .= " AND u.approval_status = ?";
            $params[] = $statusFilter;
        } else {
            
            $where .= " AND u.approval_status != 'pending'";
        }

        $count = Database::fetchColumn(
            "SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE {$where}",
            $params
        );

        $interns = Database::fetchAll(
            "SELECT u.*, r.name as role_name, r.slug as role_slug
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE {$where}
             ORDER BY CASE WHEN u.approval_status = 'pending' THEN 1 WHEN u.approval_status = 'approved' THEN 2 ELSE 3 END ASC,
                      u.created_at DESC
             LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage),
            $params
        );

        
        $pendingCount = Database::fetchColumn("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE u.deleted_at IS NULL AND r.slug = 'magang' AND u.approval_status = 'pending'") ?: 0;
        $approvedCount = Database::fetchColumn("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE u.deleted_at IS NULL AND r.slug = 'magang' AND u.approval_status = 'approved'") ?: 0;
        $rejectedCount = Database::fetchColumn("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE u.deleted_at IS NULL AND r.slug = 'magang' AND u.approval_status = 'rejected'") ?: 0;
        $totalCount = Database::fetchColumn("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE u.deleted_at IS NULL AND r.slug = 'magang'") ?: 0;

        $pendingUsers = Database::fetchAll(
            "SELECT u.*, r.name as role_name, r.slug as role_slug
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.deleted_at IS NULL AND r.slug = 'magang' AND u.approval_status = 'pending'
             ORDER BY u.created_at DESC"
        );

        $this->view('interns/index', [
            'title' => ($userRoleSlug === 'superadmin' ? 'Kelola User' : 'Kelola User Magang') . ' - Content Planner TVRI',
            'interns' => $interns,
            'pendingUsers' => $pendingUsers,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => max(1, ceil($count / $perPage)),
                'total' => $count
            ],
            'filters' => [
                'search' => $search,
                'status' => $statusFilter,
                'role' => $roleFilter
            ],
            'stats' => [
                'total' => $totalCount,
                'pending' => $pendingCount,
                'approved' => $approvedCount,
                'rejected' => $rejectedCount
            ],
            'breadcrumbs' => [
                ['label' => 'Administrasi', 'url' => '#'],
                ['label' => 'User Magang', 'url' => '/intern-users']
            ]
        ]);
    }

    


    public function approve(string $id): void
    {
        $this->checkAccess();

        $token = $_POST[CSRF_TOKEN_NAME] ?? $_POST['csrf_token'] ?? $_POST['_csrf_token'] ?? $_GET['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Session::validateCsrfToken($token)) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
            return;
        }

        $user = Database::fetch("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User tidak ditemukan.'], 404);
            return;
        }

        
        Database::execute(
            "UPDATE users SET approval_status = 'approved', is_active = 1, updated_at = NOW() WHERE id = ?",
            [$id]
        );

        
        Mail::sendApprovalNotification($user['email'], $user['name'], $user['username']);

        
        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, description, ip_address, user_agent)
             VALUES (?, ?, 'approve_user', 'intern_user', ?, ?, ?)",
            [Session::get('user_id'), Session::get('user_role_id'), "Konfirmasi akun magang: {$user['name']} ({$user['username']})", $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '']
        );

        $this->json([
            'success' => true,
            'message' => 'Akun magang "' . htmlspecialchars($user['name']) . '" berhasil dikonfirmasi. Email pemberitahuan telah dikirimkan.'
        ]);
    }

    


    public function reject(string $id): void
    {
        $this->checkAccess();

        $token = $_POST[CSRF_TOKEN_NAME] ?? $_POST['csrf_token'] ?? $_POST['_csrf_token'] ?? $_GET['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Session::validateCsrfToken($token)) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
            return;
        }

        $user = Database::fetch("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User tidak ditemukan.'], 404);
            return;
        }

        $reason = Security::sanitize($_POST['reason'] ?? '');

        
        Mail::sendRejectionNotification($user['email'], $user['name'], $reason);

        
        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, description, ip_address, user_agent)
             VALUES (?, ?, 'reject_user', 'intern_user', ?, ?, ?)",
            [Session::get('user_id'), Session::get('user_role_id'), "Tolak akun magang: {$user['name']} ({$user['username']})", $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '']
        );

        
        Database::execute("DELETE FROM users WHERE id = ?", [$id]);

        $this->json([
            'success' => true,
            'message' => 'Pendaftaran akun magang "' . htmlspecialchars($user['name']) . '" telah ditolak dan data user dihapus dari sistem.'
        ]);
    }

    


    public function delete(string $id): void
    {
        $this->checkAccess();

        $token = $_POST[CSRF_TOKEN_NAME] ?? $_POST['csrf_token'] ?? $_POST['_csrf_token'] ?? $_GET['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Session::validateCsrfToken($token)) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
            return;
        }

        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User tidak ditemukan.'], 404);
            return;
        }

        if ($id == Session::get('user_id')) {
            $this->json(['success' => false, 'message' => 'Tidak dapat menghapus akun sendiri.'], 400);
            return;
        }

        $currentUserId = Session::get('user_id');

        
        Database::execute("UPDATE planning_konten SET created_by = ? WHERE created_by = ?", [$currentUserId, $id]);
        Database::execute("UPDATE planning_konten SET pic_id = NULL WHERE pic_id = ?", [$id]);
        Database::execute("UPDATE planning_konten SET editor_id = NULL WHERE editor_id = ?", [$id]);
        Database::execute("UPDATE planning_konten SET approved_by = NULL WHERE approved_by = ?", [$id]);

        
        Database::execute("DELETE FROM activity_logs WHERE user_id = ?", [$id]);
        Database::execute("DELETE FROM notifications WHERE user_id = ?", [$id]);
        Database::execute("DELETE FROM platform_accounts WHERE user_id = ?", [$id]);

        
        Database::execute("DELETE FROM users WHERE id = ?", [$id]);

        
        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, description, ip_address, user_agent)
             VALUES (?, ?, 'delete_user', 'intern_user', ?, ?, ?)",
            [Session::get('user_id'), Session::get('user_role_id'), "Hapus permanen user: {$user['name']} ({$user['username']})", $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '']
        );

        $msg = 'User "' . htmlspecialchars($user['name']) . '" berhasil dihapus dari database.';
        Session::setFlash('success', $msg);

        $this->json([
            'success' => true,
            'message' => $msg
        ]);
    }
}

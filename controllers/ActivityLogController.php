<?php





class ActivityLogController extends Controller
{
    


    public function index(): void
    {
        $action = $_GET['action'] ?? '';
        $module = $_GET['module'] ?? '';
        $userId = $_GET['user_id'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $q = trim(Security::sanitize($_GET['q'] ?? ''));

        $where = "1=1";
        $params = [];

        if (!empty($action)) {
            $where .= " AND al.action = ?";
            $params[] = $action;
        }
        if (!empty($module)) {
            $where .= " AND al.module = ?";
            $params[] = $module;
        }
        if (!empty($userId)) {
            $where .= " AND al.user_id = ?";
            $params[] = $userId;
        }
        if (!empty($dateFrom)) {
            $where .= " AND DATE(al.created_at) >= ?";
            $params[] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $where .= " AND DATE(al.created_at) <= ?";
            $params[] = $dateTo;
        }
        
        if ($q !== '') {
            foreach (preg_split('/\s+/', $q) as $word) {
                $like = "%{$word}%";
                $where .= " AND (al.description LIKE ? OR al.module LIKE ? OR al.action LIKE ? OR u.name LIKE ?)";
                array_push($params, $like, $like, $like, $like);
            }
        }

        // Polling AJAX: hanya ambil log baru setelah id terakhir yang sudah tampil
        $afterId = (int)($_GET['after_id'] ?? 0);
        if ($this->isAjax() && $afterId > 0) {
            $where .= " AND al.id > ?";
            $params[] = $afterId;

            $newLogs = Database::fetchAll(
                "SELECT al.*, u.name as user_name, u.avatar as user_avatar, r.name as role_name
                 FROM activity_logs al
                 LEFT JOIN users u ON u.id = al.user_id
                 LEFT JOIN roles r ON r.id = al.role_id
                 WHERE {$where}
                 ORDER BY al.created_at DESC, al.id DESC
                 LIMIT 100",
                $params
            );

            ob_start();
            $this->viewPartial('activity-logs/_rows', ['data' => $newLogs, 'search_q' => '', 'rows_only' => true]);
            $rowsHtml = ob_get_clean();

            $maxId = 0;
            foreach ($newLogs as $log) {
                if ((int)$log['id'] > $maxId) $maxId = (int)$log['id'];
            }

            $this->success([
                'rows_html' => $rowsHtml,
                'has_new' => !empty($newLogs),
                'count' => count($newLogs),
                'max_id' => $maxId,
            ]);
            return;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;

        $totalCount = (int)Database::fetchColumn(
            "SELECT COUNT(*)
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             LEFT JOIN roles r ON r.id = al.role_id
             WHERE {$where}",
            $params
        );

        $totalPages = max(1, (int)ceil($totalCount / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $perPage;

        $data = Database::fetchAll(
            "SELECT al.*, u.name as user_name, u.avatar as user_avatar, r.name as role_name
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             LEFT JOIN roles r ON r.id = al.role_id
             WHERE {$where}
             ORDER BY al.created_at DESC, al.id DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        
        $modules = Database::fetchAll("SELECT DISTINCT module FROM activity_logs ORDER BY module");
        $actions = Database::fetchAll("SELECT DISTINCT action FROM activity_logs ORDER BY action");
        $users = Database::fetchAll("SELECT id, name FROM users WHERE deleted_at IS NULL ORDER BY name");

        $latestLogId = (int)Database::fetchColumn("SELECT COALESCE(MAX(id), 0) FROM activity_logs");

        $this->view('activity-logs/index', [
            'title' => 'Log Aktivitas',
            'data' => $data,
            'latest_log_id' => $latestLogId,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_records' => $totalCount,
                'per_page' => $perPage,
            ],
            'filters' => [
                'action' => $action,
                'module' => $module,
                'user_id' => $userId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'search_q' => $q,
            'modules' => $modules,
            'actions' => $actions,
            'users' => $users,
            'breadcrumbs' => [
                ['label' => 'Pengaturan', 'url' => '#'],
                ['label' => 'Log Aktivitas', 'url' => '#'],
            ],
        ]);
    }
}

<?php





class ActivityLogController extends Controller
{
    


    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
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

        $count = Database::fetchColumn(
            "SELECT COUNT(*) FROM activity_logs al LEFT JOIN users u ON u.id = al.user_id WHERE {$where}", $params
        );

        $data = Database::fetchAll(
            "SELECT al.*, u.name as user_name, u.avatar as user_avatar, r.name as role_name
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             LEFT JOIN roles r ON r.id = al.role_id
             WHERE {$where}
             ORDER BY al.created_at DESC
             LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage),
            $params
        );

        
        $modules = Database::fetchAll("SELECT DISTINCT module FROM activity_logs ORDER BY module");
        $actions = Database::fetchAll("SELECT DISTINCT action FROM activity_logs ORDER BY action");
        $users = Database::fetchAll("SELECT id, name FROM users WHERE deleted_at IS NULL ORDER BY name");

        $this->view('activity-logs/index', [
            'title' => 'Log Aktivitas',
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => max(1, ceil($count / $perPage)),
                'total' => $count,
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
            'pageActions' => [
                ['label' => 'Export Log', 'icon' => 'bi-download', 'variant' => 'outline', 'onclick' => "alert('Export feature coming soon')"],
                ['label' => 'Refresh', 'icon' => 'bi-arrow-clockwise', 'variant' => 'outline', 'onclick' => "location.reload()"],
            ],
        ]);
    }
}

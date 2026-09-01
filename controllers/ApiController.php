<?php





class ApiController extends Controller
{
    


    public function dashboardStats(): void
    {
        $stats = (new DashboardController())->getDashboardStats(
            Session::get('user_id'),
            Session::get('user_role_slug')
        );
        $this->success($stats);
    }

    


    public function dashboardChart(): void
    {
        $period = $_GET['period'] ?? 'weekly';
        $chartData = (new DashboardController())->getChartData();
        
        if ($period === 'monthly') {
            $this->success([
                'labels' => $chartData['monthly']['labels'] ?? [],
                'success' => $chartData['monthly']['posting'] ?? [],
                'scheduled' => [],
                'failed' => [],
            ]);
        } else {
            $this->success([
                'labels' => $chartData['weekly']['labels'] ?? [],
                'success' => $chartData['weekly']['success'] ?? [],
                'scheduled' => $chartData['weekly']['scheduled'] ?? [],
                'failed' => $chartData['weekly']['failed'] ?? [],
            ]);
        }
    }

    


    public function searchPlanning(): void
    {
        $query = Security::sanitize($_GET['q'] ?? '');
        if (strlen($query) < 2) {
            $this->success([]);
        }

        $userId = Session::get('user_id');
        $roleSlug = Session::get('user_role_slug');

        $where = "(pk.judul LIKE ? OR pk.caption LIKE ?) AND pk.deleted_at IS NULL";
        $params = ["%{$query}%", "%{$query}%"];

        if ($roleSlug === 'magang') {
            $where .= " AND pk.created_by = ?";
            $params[] = $userId;
        }

        $results = Database::fetchAll(
            "SELECT pk.id, pk.judul, pk.status, pk.scheduled_at,
                    p.name as platform_name, p.icon as platform_icon
             FROM planning_konten pk
             LEFT JOIN platform_sosmed p ON p.id = pk.platform_id
             WHERE {$where}
             ORDER BY pk.updated_at DESC
             LIMIT 10",
            $params
        );

        $results = array_map(function($r) {
            $r['status_label'] = $r['status'];
            return $r;
        }, $results);

        $this->success($results);
    }

    



    public function notifications(): void
    {
        $userId = Session::get('user_id');
        $limit = min((int)($_GET['limit'] ?? 50), 100);
        $roleSlug = Session::get('user_role_slug');

        
        $notifs = Database::fetchAll(
            "SELECT n.*, 
                    COALESCE(gc.group_count, 0) as group_count,
                    COALESCE(gc.group_unread, 0) as group_unread
             FROM notifications n
             LEFT JOIN (
                 SELECT group_key, COUNT(*) as group_count, 
                        SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as group_unread
                 FROM notifications 
                 WHERE user_id = ? AND group_key IS NOT NULL
                 GROUP BY group_key
             ) gc ON gc.group_key = n.group_key
             WHERE n.user_id = ?
             ORDER BY n.created_at DESC
             LIMIT ?",
            [$userId, $userId, $limit]
        );

        $unreadCount = Database::fetch(
            "SELECT COUNT(*) as total FROM notifications WHERE user_id = ? AND is_read = 0",
            [$userId]
        );

        $this->success([
            'data' => $notifs,
            'unreadCount' => (int)($unreadCount['total'] ?? 0),
        ]);
    }

    


    public function notificationsUnreadCount(): void
    {
        $userId = Session::get('user_id');
        $count = Database::fetch(
            "SELECT COUNT(*) as total FROM notifications WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
        $this->success([
            'unreadCount' => (int)($count['total'] ?? 0),
        ]);
    }

    


    public function pegawaiNotifications(): void
    {
        $userId = Session::get('user_id');
        $limit = min((int)($_GET['limit'] ?? 30), 100);

        $notifs = Database::fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );

        $unreadCount = Database::fetch(
            "SELECT COUNT(*) as total FROM notifications WHERE user_id = ? AND is_read = 0",
            [$userId]
        );

        $this->success([
            'data' => $notifs,
            'unreadCount' => (int)($unreadCount['total'] ?? 0),
        ]);
    }

    



    public function markNotificationsRead(): void
    {
        $data = $this->getJsonBody();
        $id = $data['id'] ?? null;
        $userId = Session::get('user_id');

        if ($id) {
            Database::execute(
                "DELETE FROM notifications WHERE id = ? AND user_id = ?",
                [$id, $userId]
            );
        }

        $this->success(null, 'Notifikasi telah dihapus');
    }

    


    public function markAllNotificationsRead(): void
    {
        $userId = Session::get('user_id');
        Database::execute(
            "DELETE FROM notifications WHERE user_id = ?",
            [$userId]
        );
        $this->success(null, 'Semua notifikasi telah dihapus');
    }

    



    public function markMultipleNotificationsRead(): void
    {
        $data = $this->getJsonBody();
        $ids = $data['ids'] ?? [];
        $userId = Session::get('user_id');

        if (!is_array($ids) || empty($ids)) {
            $this->error('Tidak ada notifikasi yang dipilih');
            return;
        }

        
        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = $ids;
        $params[] = $userId;

        Database::execute(
            "DELETE FROM notifications WHERE id IN ({$placeholders}) AND user_id = ?",
            $params
        );

        $this->success(null, count($ids) . ' notifikasi telah dihapus');
    }

    


    public function analyticsData(): void
    {
        $period = $_GET['period'] ?? 'month';
        $platformId = $_GET['platform_id'] ?? null;

        
        $dateCondition = match($period) {
            'week' => "DATE(scheduled_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)",
            'month' => "DATE(scheduled_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)",
            'quarter' => "DATE(scheduled_at) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)",
            'year' => "YEAR(scheduled_at) = YEAR(CURDATE())",
            default => "1=1",
        };

        $platformCondition = $platformId ? "AND platform_id = " . (int)$platformId : "";

        
        $postingByDay = Database::fetchAll(
            "SELECT DATE(scheduled_at) as date, COUNT(*) as total,
                    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
             FROM planning_konten 
             WHERE {$dateCondition} {$platformCondition} AND deleted_at IS NULL
             GROUP BY DATE(scheduled_at)
             ORDER BY date ASC"
        );

        
        $platformPerformance = Database::fetchAll(
            "SELECT p.name, p.color, p.icon,
                    COUNT(pk.id) as total_posts,
                    SUM(CASE WHEN pk.status = 'success' THEN 1 ELSE 0 END) as success_posts,
                    COALESCE(SUM(pk.engagement_like), 0) as total_likes,
                    COALESCE(SUM(pk.engagement_comment), 0) as total_comments,
                    COALESCE(SUM(pk.engagement_share), 0) as total_shares,
                    COALESCE(SUM(pk.reach_count), 0) as total_reach,
                    COALESCE(SUM(pk.view_count), 0) as total_views
             FROM planning_konten pk
             JOIN platform_sosmed p ON p.id = pk.platform_id
             WHERE {$dateCondition} {$platformCondition} AND pk.deleted_at IS NULL
             GROUP BY p.id, p.name, p.color, p.icon
             ORDER BY total_posts DESC"
        );

        
        $topContent = Database::fetchAll(
            "SELECT pk.id, pk.judul, pk.engagement_like, pk.engagement_comment, pk.engagement_share,
                    p.name as platform_name, p.icon as platform_icon
             FROM planning_konten pk
             JOIN platform_sosmed p ON p.id = pk.platform_id
             WHERE pk.status = 'success' AND pk.deleted_at IS NULL {$platformCondition}
             ORDER BY (pk.engagement_like + pk.engagement_comment + pk.engagement_share) DESC
             LIMIT 10"
        );

        $this->success([
            'postingByDay' => $postingByDay,
            'platformPerformance' => $platformPerformance,
            'topContent' => $topContent,
        ]);
    }

    


    public function planningStats(): void
    {
        $userId = Session::get('user_id');
        $roleSlug = Session::get('user_role_slug');

        $condition = '';
        if ($roleSlug === 'magang') {
            $condition = "AND created_by = {$userId}";
        }

        $stats = Database::fetch(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
                SUM(CASE WHEN status = 'review' THEN 1 ELSE 0 END) as review,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'revision' THEN 1 ELSE 0 END) as revision,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
             FROM planning_konten 
             WHERE deleted_at IS NULL {$condition}"
        );

        $this->success($stats);
    }

    


    public function calendarData(): void
    {
        $start = $_GET['start'] ?? date('Y-m-01');
        $end = $_GET['end'] ?? date('Y-m-t');

        $data = Database::fetchAll(
            "SELECT pk.id, pk.judul as title, pk.scheduled_at as start, pk.status,
                    p.name as platform, p.color as platform_color
             FROM planning_konten pk
             LEFT JOIN platform_sosmed p ON p.id = pk.platform_id
             WHERE pk.scheduled_at BETWEEN ? AND ? AND pk.deleted_at IS NULL
             ORDER BY pk.scheduled_at",
            [$start . ' 00:00:00', $end . ' 23:59:59']
        );

        $this->success($data);
    }

    


    public function masterData(string $table): void
    {
        $allowedTables = ['program_tv', 'kategori_konten', 'platform_sosmed', 'tags', 'hashtag', 'jenis_konten'];
        
        if (!in_array($table, $allowedTables)) {
            $this->error('Tabel tidak ditemukan', 404);
        }

        $data = Database::fetchAll(
            "SELECT * FROM {$table} WHERE is_active = 1 ORDER BY name ASC"
        );

        $this->success($data);
    }
}

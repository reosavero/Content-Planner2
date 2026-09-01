<?php





class DashboardController extends Controller
{
    


    public function index(): void
    {
        $userId = Session::get('user_id');
        $roleSlug = Session::get('user_role_slug');

        if ($roleSlug === 'magang') {
            $userName = Session::get('user_name');
            [$groupedTasks, $taskStats] = $this->getMagangTaskData($userId, $userName);

            if ($this->isAjax()) {
                ob_start();
                $this->viewPartial('dashboard/_magang_tasks', ['groupedTasks' => $groupedTasks]);
                $html = ob_get_clean();
                $this->success(['html' => $html]);
            }

            $this->view('dashboard/index', [
                'title' => 'Dashboard Tugas Saya',
                'isMagangDashboard' => true,
                'assignedTasks' => array_merge(...array_column($groupedTasks, 'tasks')),
                'groupedTasks' => $groupedTasks,
                'taskStats' => $taskStats,
            ]);
            return;
        }

        
        $stats = $this->getDashboardStats($userId, $roleSlug);
        $chartData = $this->getChartData();
        $period = $_GET['period'] ?? 'month';
        $trend = $this->getTrendData($period);
        
        
        $recentActivities = Database::fetchAll(
            "SELECT al.*, u.name as user_name, u.avatar as user_avatar
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC
             LIMIT 10"
        );

        
        $upcomingSchedules = Database::fetchAll(
            "SELECT pk.*, p.name as platform_name, p.slug as platform_slug, p.icon as platform_icon, p.color as platform_color
             FROM planning_konten pk
             LEFT JOIN platform_sosmed p ON p.id = pk.platform_id
             WHERE pk.status = 'scheduled' 
             AND pk.scheduled_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
             AND pk.deleted_at IS NULL
             ORDER BY pk.scheduled_at ASC
             LIMIT 10"
        );

        
        $popularPosts = Database::fetchAll(
            "SELECT pk.*, p.name as platform_name, p.icon as platform_icon
             FROM planning_konten pk
             LEFT JOIN platform_sosmed p ON p.id = pk.platform_id
             WHERE pk.status = 'success'
             AND (pk.engagement_like > 0 OR pk.engagement_comment > 0)
             AND pk.deleted_at IS NULL
             ORDER BY (pk.engagement_like + pk.engagement_comment) DESC
             LIMIT 5"
        );

        $this->view('dashboard/index', [
            'title' => 'Dashboard - Content Planner',
            'stats' => $stats,
            'chartData' => $chartData,
            'trend' => $trend,
            'platformStats' => $chartData['platforms'] ?? [],
            'recentActivities' => $recentActivities,
            'upcomingSchedules' => $upcomingSchedules,
            'popularPosts' => $popularPosts,
        ]);
    }

    


    private function getMagangTaskData(int $userId, string $userName): array
    {
        $assignedTasks = Database::fetchAll(
            "SELECT tt.*, creator.name AS assigned_by_name,
                    p.name AS platform_name, p.icon AS platform_icon
             FROM timeline_tasks tt
             LEFT JOIN users creator ON creator.id = tt.creator_id
             LEFT JOIN platform_sosmed p ON p.id = tt.platform_id
             WHERE (tt.assignee_id = ? OR tt.assigned_to = ? OR LOWER(TRIM(tt.pic_name)) = LOWER(TRIM(?)))
               AND tt.status IN ('Assigned', 'In Progress', 'Belum', 'Proses', 'Need Revision')
             ORDER BY tt.task_date ASC, tt.sort_order ASC, tt.id ASC",
            [$userId, $userId, $userName]
        );

        
        $dayNames = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
        $groupedTasks = [];
        foreach ($assignedTasks as $task) {
            $date = $task['task_date'] ?? date('Y-m-d');
            if (!isset($groupedTasks[$date])) {
                $engDay = date('l', strtotime($date));
                $groupedTasks[$date] = [
                    'day_name' => $dayNames[$engDay] ?? $engDay,
                    'tasks' => [],
                ];
            }
            $groupedTasks[$date]['tasks'][] = $task;
        }
        ksort($groupedTasks);

        $taskStats = Database::fetch(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN status IN ('Assigned', 'Belum', 'Belum Selesai') THEN 1 ELSE 0 END) AS assigned,
                    SUM(CASE WHEN status IN ('In Progress', 'Proses') THEN 1 ELSE 0 END) AS progress,
                    SUM(CASE WHEN status IN ('Approved', 'Selesai', 'Publish') THEN 1 ELSE 0 END) AS selesai,
                    SUM(CASE WHEN status = 'Pending Approval' THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN status = 'Need Revision' THEN 1 ELSE 0 END) AS revision
             FROM timeline_tasks
             WHERE (assignee_id = ? OR assigned_to = ? OR LOWER(TRIM(pic_name)) = LOWER(TRIM(?)))",
            [$userId, $userId, $userName]
        );

        return [$groupedTasks, $taskStats];
    }

    public function getDashboardStats(int $userId, string $roleSlug): array
    {
        $dateToday = date('Y-m-d');
        $startOfWeek = date('Y-m-d', strtotime('monday this week'));
        $startOfMonth = date('Y-m-01');

        
        $myCondition = '';
        if ($roleSlug === 'magang') {
            $myCondition = "AND created_by = {$userId}";
        }

        return [
            'total_planning' => (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM planning_konten WHERE deleted_at IS NULL {$myCondition}"
            ),
            'posting_today' => (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM planning_konten WHERE DATE(scheduled_at) = ? AND status IN ('success','posting') AND deleted_at IS NULL {$myCondition}",
                [$dateToday]
            ),
            'posting_week' => (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM planning_konten WHERE DATE(scheduled_at) >= ? AND status IN ('success','posting') AND deleted_at IS NULL {$myCondition}",
                [$startOfWeek]
            ),
            'posting_month' => (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM planning_konten WHERE DATE(scheduled_at) >= ? AND status IN ('success','posting') AND deleted_at IS NULL {$myCondition}",
                [$startOfMonth]
            ),
            'draft' => (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM planning_konten WHERE status = 'draft' AND deleted_at IS NULL {$myCondition}"
            ),
            'approved' => (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM planning_konten WHERE status = 'approved' AND deleted_at IS NULL {$myCondition}"
            ),
            'rejected' => (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM planning_konten WHERE status IN ('revision','cancelled') AND deleted_at IS NULL {$myCondition}"
            ),
            'posting_success' => (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM planning_konten WHERE status = 'success' AND deleted_at IS NULL {$myCondition}"
            ),
            'posting_failed' => (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM planning_konten WHERE status = 'failed' AND deleted_at IS NULL {$myCondition}"
            ),
            'scheduled' => (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM planning_konten WHERE status = 'scheduled' AND scheduled_at > NOW() AND deleted_at IS NULL {$myCondition}"
            ),
            'total_success' => (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM planning_konten WHERE status = 'success' AND deleted_at IS NULL {$myCondition}"
            ),
            'total_scheduled' => (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM planning_konten WHERE status = 'scheduled' AND scheduled_at > NOW() AND deleted_at IS NULL {$myCondition}"
            ),
            'connected_accounts' => (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM platform_akun WHERE is_active = 1"
            ),
            'total_platforms' => (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM platform_sosmed WHERE is_active = 1"
            ),
            'review' => (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM planning_konten WHERE status = 'review' AND deleted_at IS NULL {$myCondition}"
            ),
            
            'total_followers' => (int) Database::fetchColumn(
                "SELECT COALESCE(SUM(followers_count), 0) FROM platform_akun WHERE is_active = 1"
            ),
            'total_reach' => (int) Database::fetchColumn(
                "SELECT COALESCE(SUM(reach_count), 0) FROM planning_konten WHERE status = 'success' AND deleted_at IS NULL"
            ),
            'total_engagement' => (int) Database::fetchColumn(
                "SELECT COALESCE(SUM(engagement_like + engagement_comment + engagement_share), 0) FROM planning_konten WHERE status = 'success' AND deleted_at IS NULL"
            ),
            'total_likes' => (int) Database::fetchColumn(
                "SELECT COALESCE(SUM(engagement_like), 0) FROM planning_konten WHERE status = 'success' AND deleted_at IS NULL"
            ),
            'total_comments' => (int) Database::fetchColumn(
                "SELECT COALESCE(SUM(engagement_comment), 0) FROM planning_konten WHERE status = 'success' AND deleted_at IS NULL"
            ),
            'total_shares' => (int) Database::fetchColumn(
                "SELECT COALESCE(SUM(engagement_share), 0) FROM planning_konten WHERE status = 'success' AND deleted_at IS NULL"
            ),
            'total_views' => (int) Database::fetchColumn(
                "SELECT COALESCE(SUM(view_count), 0) FROM planning_konten WHERE status = 'success' AND deleted_at IS NULL"
            ),
        ];
    }

    


    public function getChartData(): array
    {
        
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $weeklyData['labels'][] = date('D', strtotime($date));
            $weeklyData['success'][] = (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM planning_konten WHERE DATE(scheduled_at) = ? AND status = 'success' AND deleted_at IS NULL",
                [$date]
            );
            $weeklyData['failed'][] = (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM planning_konten WHERE DATE(scheduled_at) = ? AND status = 'failed' AND deleted_at IS NULL",
                [$date]
            );
            $weeklyData['scheduled'][] = (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM planning_konten WHERE DATE(scheduled_at) = ? AND status = 'scheduled' AND deleted_at IS NULL",
                [$date]
            );
        }

        
        $monthlyData = [];
        for ($i = 3; $i >= 0; $i--) {
            $start = date('Y-m-d', strtotime("monday -{$i} week"));
            $end = date('Y-m-d', strtotime("sunday -{$i} week"));
            $monthlyData['labels'][] = 'Minggu ' . (4 - $i);
            $monthlyData['posting'][] = (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM planning_konten WHERE DATE(scheduled_at) BETWEEN ? AND ? AND status IN ('success','failed') AND deleted_at IS NULL",
                [$start, $end]
            );
        }

        
        $platforms = Database::fetchAll(
            "SELECT p.name, p.slug, p.color, p.icon, COUNT(pk.id) as total
             FROM planning_konten pk
             JOIN platform_sosmed p ON p.id = pk.platform_id
             WHERE pk.deleted_at IS NULL
             GROUP BY p.id, p.name, p.slug, p.color, p.icon
             ORDER BY total DESC"
        );

        
        $statuses = Database::fetchAll(
            "SELECT 
                CASE 
                    WHEN status = 'draft' THEN 'Draft'
                    WHEN status = 'review' THEN 'Review'
                    WHEN status = 'approved' THEN 'Approved'
                    WHEN status = 'scheduled' THEN 'Terjadwal'
                    WHEN status = 'success' THEN 'Berhasil'
                    WHEN status = 'failed' THEN 'Gagal'
                    WHEN status = 'revision' THEN 'Revisi'
                    WHEN status = 'cancelled' THEN 'Dibatalkan'
                    ELSE status
                END as label,
                COUNT(*) as value,
                CASE 
                    WHEN status = 'draft' THEN '#6c757d'
                    WHEN status = 'review' THEN '#ffc107'
                    WHEN status = 'approved' THEN '#0d6efd'
                    WHEN status = 'scheduled' THEN '#0dcaf0'
                    WHEN status = 'success' THEN '#198754'
                    WHEN status = 'failed' THEN '#dc3545'
                    WHEN status = 'revision' THEN '#fd7e14'
                    WHEN status = 'cancelled' THEN '#6c757d'
                END as color
             FROM planning_konten 
             WHERE deleted_at IS NULL
             GROUP BY status
             ORDER BY COUNT(*) DESC"
        );

        return [
            'weekly' => $weeklyData,
            'monthly' => $monthlyData,
            'platforms' => $platforms,
            'statuses' => $statuses,
        ];
    }

    

    public function getTrendData(string $period): array
    {
        $startThisMonth = date('Y-m-01');
        $startLastMonth = date('Y-m-01', strtotime('first day of last month'));
        $endLastMonth = date('Y-m-t', strtotime('last day of last month'));

        $planningThis = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM planning_konten WHERE DATE(scheduled_at) >= ? AND deleted_at IS NULL",
            [$startThisMonth]
        );
        $planningLast = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM planning_konten WHERE DATE(scheduled_at) BETWEEN ? AND ? AND deleted_at IS NULL",
            [$startLastMonth, $endLastMonth]
        );
        $successThis = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM planning_konten WHERE status = 'success' AND DATE(scheduled_at) >= ? AND deleted_at IS NULL",
            [$startThisMonth]
        );
        $successLast = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM planning_konten WHERE status = 'success' AND DATE(scheduled_at) BETWEEN ? AND ? AND deleted_at IS NULL",
            [$startLastMonth, $endLastMonth]
        );

        $pct = function (int $current, int $previous): int {
            if ($previous > 0) {
                return (int) round((($current - $previous) / $previous) * 100);
            }
            return $current > 0 ? 100 : 0;
        };

        $labels = [];
        $data = [];

        if ($period === 'week') {
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i} days"));
                $labels[] = date('D', strtotime($date));
                $data[] = (int) Database::fetchColumn(
                    "SELECT COUNT(*) FROM planning_konten WHERE DATE(scheduled_at) = ? AND status = 'success' AND deleted_at IS NULL",
                    [$date]
                );
            }
        } elseif ($period === 'year') {
            for ($i = 11; $i >= 0; $i--) {
                $month = date('Y-m', strtotime("-{$i} months"));
                $labels[] = date('M', strtotime($month . '-01'));
                $data[] = (int) Database::fetchColumn(
                    "SELECT COUNT(*) FROM planning_konten WHERE DATE_FORMAT(scheduled_at, '%Y-%m') = ? AND status = 'success' AND deleted_at IS NULL",
                    [$month]
                );
            }
        } else {
            for ($i = 3; $i >= 0; $i--) {
                $start = date('Y-m-d', strtotime("monday -{$i} week"));
                $end = date('Y-m-d', strtotime("sunday -{$i} week"));
                $labels[] = 'Minggu ' . (4 - $i);
                $data[] = (int) Database::fetchColumn(
                    "SELECT COUNT(*) FROM planning_konten WHERE DATE(scheduled_at) BETWEEN ? AND ? AND status = 'success' AND deleted_at IS NULL",
                    [$start, $end]
                );
            }
        }

        return [
            'planning' => $pct($planningThis, $planningLast),
            'success' => $pct($successThis, $successLast),
            'chart_data' => [
                'labels' => $labels,
                'data' => $data,
            ],
        ];
    }
}

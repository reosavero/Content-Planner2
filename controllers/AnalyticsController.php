<?php





class AnalyticsController extends Controller
{
    


    public function index(): void
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

        $platformCondition = $platformId ? "AND pk.platform_id = " . (int)$platformId : "";

        
        $postingTrend = Database::fetchAll(
            "SELECT DATE(scheduled_at) as date, COUNT(*) as total,
                    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success,
                    SUM(CASE WHEN pk.status = 'failed' THEN 1 ELSE 0 END) as failed
             FROM planning_konten pk
             WHERE {$dateCondition} {$platformCondition} AND pk.deleted_at IS NULL
             GROUP BY DATE(scheduled_at)
             ORDER BY date ASC"
        );

        
        $platformComparison = Database::fetchAll(
            "SELECT p.name, p.color, p.icon,
                    COUNT(pk.id) as total_posts,
                    SUM(CASE WHEN pk.status = 'success' THEN 1 ELSE 0 END) as success,
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
                    pk.reach_count, pk.view_count, pk.scheduled_at,
                    p.name as platform_name, p.icon as platform_icon, p.color as platform_color
             FROM planning_konten pk
             JOIN platform_sosmed p ON p.id = pk.platform_id
             WHERE pk.status = 'success' AND pk.deleted_at IS NULL {$platformCondition}
             ORDER BY (pk.engagement_like + pk.engagement_comment + pk.engagement_share) DESC
             LIMIT 20"
        );

        
        $bestTime = Database::fetchAll(
            "SELECT HOUR(scheduled_at) as hour,
                    COUNT(*) as total_posts,
                    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success,
                    ROUND(AVG(CASE WHEN status = 'success' THEN 1 ELSE 0 END) * 100, 1) as success_rate
             FROM planning_konten pk
             WHERE {$dateCondition} {$platformCondition} AND pk.deleted_at IS NULL
             GROUP BY HOUR(scheduled_at)
             ORDER BY success_rate DESC"
        );

        
        $monthlyGrowth = Database::fetchAll(
            "SELECT DATE_FORMAT(scheduled_at, '%Y-%m') as month,
                    COUNT(*) as total_posts,
                    COUNT(DISTINCT pk.created_by) as active_users
             FROM planning_konten pk
             WHERE pk.deleted_at IS NULL {$platformCondition}
             GROUP BY DATE_FORMAT(scheduled_at, '%Y-%m')
             ORDER BY month ASC
             LIMIT 12"
        );

        $platforms = Database::fetchAll("SELECT id, name, icon, color FROM platform_sosmed WHERE is_active = 1");

        $this->view('analytics/index', [
            'title' => 'Analytics',
            'period' => $period,
            'platformId' => $platformId,
            'postingTrend' => $postingTrend,
            'platformComparison' => $platformComparison,
            'topContent' => $topContent,
            'bestTime' => $bestTime,
            'monthlyGrowth' => $monthlyGrowth,
            'platforms' => $platforms,
            'breadcrumbs' => [['label' => 'Analytics', 'url' => '#']],
            'pageActions' => [
                ['label' => 'Export PDF', 'icon' => 'bi-file-pdf', 'variant' => 'outline', 'onclick' => "window.location.href='" . BASE_URL . "/analytics/export/pdf'"],
                ['label' => 'Export Excel', 'icon' => 'bi-file-earmark-excel', 'variant' => 'outline', 'onclick' => "window.location.href='" . BASE_URL . "/analytics/export/excel'"],
            ],
        ]);
    }

    


    public function export(string $type): void
    {
        
        $this->redirectWith('/analytics', 'success', 'Fitur export akan segera tersedia.');
    }

    


    public function getData(): void
    {
        
        $this->json(['success' => true, 'message' => 'Use analyticsData endpoint in ApiController']);
    }
}

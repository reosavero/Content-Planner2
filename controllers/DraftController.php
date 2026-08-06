<?php





class DraftController extends Controller
{
    public function index(): void
    {
        $userId = Session::get('user_id');
        $data = Database::fetchAll(
            "SELECT pk.*, p.name as platform_name, p.icon as platform_icon
             FROM planning_konten pk
             LEFT JOIN platform_sosmed p ON p.id = pk.platform_id
             WHERE pk.created_by = ? AND pk.status = 'draft' AND pk.deleted_at IS NULL
             ORDER BY pk.updated_at DESC",
            [$userId]
        );

        $this->view('draft/index', [
            'title' => 'Draft Konten',
            'data' => $data,
            'breadcrumbs' => [['label' => 'Konten', 'url' => '#'], ['label' => 'Draft', 'url' => '#']],
            'pageActions' => [
                ['label' => 'Buat Draft', 'icon' => 'bi-plus', 'variant' => 'primary', 'onclick' => "window.location.href='" . BASE_URL . "/planning/create'"],
            ],
        ]);
    }

    public function submitReview(string $id): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid.');
        }

        $planning = Database::fetch(
            "SELECT * FROM planning_konten WHERE id = ? AND created_by = ? AND status = 'draft' AND deleted_at IS NULL",
            [$id, Session::get('user_id')]
        );

        if (!$planning) {
            $this->redirectWith('/draft', 'error', 'Draft tidak ditemukan.');
        }

        Database::execute(
            "UPDATE planning_konten SET status = 'review', updated_at = NOW() WHERE id = ?",
            [$id]
        );

        
        $editors = Database::fetchAll(
            "SELECT id FROM users WHERE role_id = (SELECT id FROM roles WHERE slug = 'admin') AND is_active = 1"
        );
        foreach ($editors as $editor) {
            Database::execute(
                "INSERT INTO notifications (user_id, type, title, message, link, created_at) 
                 VALUES (?, 'info', 'Draft Baru untuk Review', 'Konten \"{$planning['judul']}\" siap direview.', ?, NOW())",
                [$editor['id'], '/approval/' . $id]
            );
        }

        $this->redirectWith('/draft', 'success', 'Draft berhasil dikirim ke review.');
    }
}

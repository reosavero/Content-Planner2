<?php




class RoleController extends Controller
{
    public function index(): void
    {
        $roles = Database::fetchAll("SELECT * FROM roles ORDER BY id");
        
        
        foreach ($roles as &$role) {
            $role['user_count'] = Database::fetchColumn(
                "SELECT COUNT(*) FROM users WHERE role_id = ? AND deleted_at IS NULL",
                [$role['id']]
            );
        }

        $this->view('roles/index', [
            'title' => 'Manajemen Role',
            'roles' => $roles,
            'breadcrumbs' => [['label' => 'Pengaturan', 'url' => '#'], ['label' => 'Roles', 'url' => '#']],
        ]);
    }
}

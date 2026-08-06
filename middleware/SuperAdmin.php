<?php





class SuperAdmin
{
    


    public function handle(): void
    {
        if (!Session::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $roleSlug = Session::get('user_role_slug');
        
        if ($roleSlug !== 'superadmin') {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya Super Admin yang dapat mengakses fitur ini.'
                ]);
                exit;
            }
            
            Session::setFlash('error', 'Akses ditolak. Hanya Super Admin yang dapat mengakses halaman ini.');
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }
}

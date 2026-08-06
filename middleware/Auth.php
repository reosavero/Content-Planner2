<?php





class Auth
{
    


    public function handle(): void
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        if (!Session::isLoggedIn()) {
            
            $currentUrl = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
            $basePath = dirname($_SERVER['SCRIPT_NAME'] ?? '');
            if ($basePath !== '/' && $basePath !== '\\') {
                $normalizedBasePath = str_replace('\\', '/', $basePath);
                if (str_starts_with($currentUrl, $normalizedBasePath)) {
                    $currentUrl = substr($currentUrl, strlen($normalizedBasePath));
                }
            }
            $currentUrl = '/' . ltrim($currentUrl, '/');

            if ($currentUrl !== '/login' && !str_starts_with($currentUrl, '/login')) {
                Session::set('intended_url', $currentUrl);
            }
            
            
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        
        $lastActivity = Session::get('last_activity', time());
        $timeoutMinutes = Session::get('session_timeout_minutes', 60);
        
        if ((time() - $lastActivity) > ($timeoutMinutes * 60)) {
            Session::destroy();
            header('Location: ' . BASE_URL . '/login?timeout=1');
            exit;
        }
        
        
        $user = Database::fetch(
            "SELECT u.id, u.name, u.email, u.username, u.role_id, u.avatar, r.slug AS role_slug, r.name AS role_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.id = ? AND u.is_active = 1 AND u.deleted_at IS NULL",
            [Session::get('user_id')]
        );

        if (!$user || (int)$user['role_id'] !== (int)Session::get('user_role_id')) {
            Session::destroy();
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);
        Session::set('user_username', $user['username']);
        Session::set('user_role_slug', $user['role_slug']);
        Session::set('user_role_name', $user['role_name']);
        Session::set('user_avatar', $user['avatar']);

        
        Session::set('last_activity', time());
    }
}

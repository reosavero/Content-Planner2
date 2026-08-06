<?php






class Controller
{
    protected array $data = [];
    protected string $layout = 'layouts/main';
    protected array $jsFiles = [];
    protected array $cssFiles = [];

    


    protected function view(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        
        
        extract($this->data);
        
        
        ob_start();
        
        
        $viewPath = VIEWS_PATH . 'pages/' . $view . '.php';
        if (!file_exists($viewPath)) {
            $viewPath = VIEWS_PATH . $view . '.php';
        }
        
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            throw new Exception("View not found: {$view}");
        }
        
        $content = ob_get_clean();
        
        
        $layoutPath = VIEWS_PATH . $this->layout . '.php';
        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    


    protected function viewPartial(string $view, array $data = []): void
    {
        extract(array_merge($this->data, $data));
        $viewPath = VIEWS_PATH . 'pages/' . $view . '.php';
        if (!file_exists($viewPath)) {
            $viewPath = VIEWS_PATH . $view . '.php';
        }
        if (file_exists($viewPath)) {
            require $viewPath;
        }
    }

    


    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    


    protected function success(mixed $data = null, string $message = 'Berhasil', int $code = 200): void
    {
        $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    


    protected function error(string $message = 'Terjadi kesalahan', int $code = 400, mixed $errors = null): void
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        $this->json($response, $code);
    }

    


    protected function redirect(string $url): void
    {
        
        if (str_starts_with($url, '/') && !str_starts_with($url, 'http')) {
            $basePath = dirname($_SERVER['SCRIPT_NAME'] ?? '');
            if ($basePath !== '/' && $basePath !== '\\') {
                $normalizedBasePath = str_replace('\\', '/', $basePath);
                if (str_starts_with($url, $normalizedBasePath . '/') || $url === $normalizedBasePath) {
                    $url = substr($url, strlen($normalizedBasePath));
                }
            }
            $url = BASE_URL . '/' . ltrim($url, '/');
        }
        header('Location: ' . $url);
        exit;
    }

    


    protected function redirectBack(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/dashboard';
        $this->redirect($referer);
    }

    


    protected function redirectWith(string $url, string $type, string $message): void
    {
        Session::setFlash($type, $message);
        $this->redirect($url);
    }

    


    protected function redirectBackWith(string $type, string $message): void
    {
        Session::setFlash($type, $message);
        $this->redirectBack();
    }

    


    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    


    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST' || 
               (isset($_POST['_method']) && $_POST['_method'] === 'POST');
    }

    


    protected function getPost(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    


    protected function getQuery(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    


    protected function getJsonBody(): array
    {
        $body = file_get_contents('php://input');
        return json_decode($body, true) ?? [];
    }

    


    protected function addJs(string $file): void
    {
        $this->jsFiles[] = $file;
    }

    


    protected function addCss(string $file): void
    {
        $this->cssFiles[] = $file;
    }

    


    protected function paginate(string $table, string $where = '1=1', array $params = [], int $perPage = 20): array
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;
        
        $count = Database::fetchColumn(
            "SELECT COUNT(*) FROM {$table} WHERE {$where}",
            $params
        );
        
        $totalPages = max(1, ceil($count / $perPage));
        
        $data = Database::fetchAll(
            "SELECT * FROM {$table} WHERE {$where} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        
        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $count,
            'total_pages' => $totalPages,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1,
        ];
    }

    


    protected function validate(array $data, array $rules): array
    {
        $validator = new Validator();
        $validator->setData($data)->setRules($rules);
        
        if (!$validator->validate()) {
            if ($this->isAjax()) {
                $this->error('Validasi gagal', 422, $validator->getErrors());
            }
            Session::set('_old_input', $data);
            Session::set('_validation_errors', $validator->getErrors());
            $this->redirectBack();
        }
        
        return $validator->getValidatedData();
    }

    


    protected function old(string $key, mixed $default = ''): mixed
    {
        $old = Session::get('_old_input', []);
        return $old[$key] ?? $default;
    }

    


    protected function fieldError(string $field): string
    {
        $errors = Session::get('_validation_errors', []);
        return $errors[$field] ?? '';
    }
}

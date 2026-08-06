<?php





class MasterController extends Controller
{
    
    
    
    public function program(): void
    {
        $data = Database::fetchAll("SELECT * FROM program_tv ORDER BY sort_order, name");
        $this->view('master/program', [
            'title' => 'Program TV',
            'data' => $data,
            'breadcrumbs' => [['label' => 'Master Data', 'url' => '#'], ['label' => 'Program TV', 'url' => '#']],
            'pageActions' => [['label' => 'Tambah Program', 'icon' => 'bi-plus', 'variant' => 'primary', 'onclick' => "openModal('modalProgram')"]],
        ]);
    }

    public function programStore(): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid.');
        }
        $data = $this->validate($_POST, ['name' => 'required|max:200', 'category' => 'required']);
        $slug = generateSlug($data['name']);
        Database::execute(
            "INSERT INTO program_tv (name, slug, description, category, is_active, sort_order) VALUES (?, ?, ?, ?, 1, 0)",
            [$data['name'], $slug, $_POST['description'] ?? '', $data['category']]
        );
        $this->redirectWith('/master/program', 'success', 'Program TV berhasil ditambahkan.');
    }

    public function programUpdate(string $id): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid.');
        }
        Database::execute(
            "UPDATE program_tv SET name = ?, description = ?, category = ?, is_active = ?, sort_order = ? WHERE id = ?",
            [$_POST['name'], $_POST['description'] ?? '', $_POST['category'], $_POST['is_active'] ?? 0, $_POST['sort_order'] ?? 0, $id]
        );
        $this->redirectWith('/master/program', 'success', 'Program TV berhasil diupdate.');
    }

    public function programDelete(string $id): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid'], 403);
        }
        Database::execute("DELETE FROM program_tv WHERE id = ?", [$id]);
        $this->json(['success' => true, 'message' => 'Program TV berhasil dihapus.']);
    }

    
    
    
    public function kategori(): void
    {
        $data = Database::fetchAll("SELECT * FROM kategori_konten ORDER BY sort_order, name");
        $tags = Database::fetchAll("SELECT * FROM tags ORDER BY name");
        $hashtags = Database::fetchAll("SELECT * FROM hashtag ORDER BY name");
        $this->view('master/kategori', [
            'title' => 'Kategori',
            'data' => $data,
            'tags' => $tags,
            'hashtags' => $hashtags,
            'breadcrumbs' => [['label' => 'Master Data', 'url' => '#'], ['label' => 'Kategori', 'url' => '#']],
        ]);
    }

    public function kategoriStore(): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid.');
        }
        $slug = generateSlug($_POST['name']);
        Database::execute(
            "INSERT INTO kategori_konten (name, slug, description, color, icon, sort_order) VALUES (?, ?, ?, ?, ?, 0)",
            [$_POST['name'], $slug, $_POST['description'] ?? '', $_POST['color'] ?? '#3498db', $_POST['icon'] ?? 'bi-tag']
        );
        $this->redirectWith('/master/kategori', 'success', 'Kategori berhasil ditambahkan.');
    }

    public function kategoriUpdate(string $id): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid.');
        }
        Database::execute(
            "UPDATE kategori_konten SET name = ?, description = ?, color = ?, icon = ?, is_active = ? WHERE id = ?",
            [$_POST['name'], $_POST['description'] ?? '', $_POST['color'] ?? '#3498db', $_POST['icon'] ?? 'bi-tag', $_POST['is_active'] ?? 1, $id]
        );
        $this->redirectWith('/master/kategori', 'success', 'Kategori berhasil diupdate.');
    }

    public function kategoriDelete(string $id): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid'], 403);
        }
        Database::execute("DELETE FROM kategori_konten WHERE id = ?", [$id]);
        $this->json(['success' => true, 'message' => 'Kategori berhasil dihapus.']);
    }

    
    
    
    public function platform(): void
    {
        $data = Database::fetchAll("SELECT * FROM platform_sosmed ORDER BY sort_order");
        $this->view('master/platform', [
            'title' => 'Platform Sosial Media',
            'data' => $data,
            'breadcrumbs' => [['label' => 'Master Data', 'url' => '#'], ['label' => 'Platform', 'url' => '#']],
        ]);
    }

    public function platformToggle(string $id): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid'], 403);
        }
        $current = Database::fetchColumn("SELECT is_active FROM platform_sosmed WHERE id = ?", [$id]);
        $new = $current ? 0 : 1;
        Database::execute("UPDATE platform_sosmed SET is_active = ? WHERE id = ?", [$new, $id]);
        $this->json(['success' => true, 'is_active' => $new]);
    }

    
    
    
    public function tags(): void
    {
        $data = Database::fetchAll("SELECT * FROM tags ORDER BY name");
        $hashtags = Database::fetchAll("SELECT * FROM hashtag ORDER BY name");
        $this->view('master/tags', [
            'title' => 'Tags & Hashtag',
            'data' => $data,
            'hashtags' => $hashtags,
            'breadcrumbs' => [['label' => 'Master Data', 'url' => '#'], ['label' => 'Tags & Hashtag', 'url' => '#']],
        ]);
    }

    public function tagsStore(): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid.');
        }
        $slug = generateSlug($_POST['name']);
        Database::execute("INSERT INTO tags (name, slug, color) VALUES (?, ?, ?)",
            [$_POST['name'], $slug, $_POST['color'] ?? '#6c757d']);
        $this->redirectWith('/master/tags', 'success', 'Tag berhasil ditambahkan.');
    }

    public function tagsDelete(string $id): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid'], 403);
        }
        Database::execute("DELETE FROM tags WHERE id = ?", [$id]);
        $this->json(['success' => true, 'message' => 'Tag berhasil dihapus.']);
    }

    
    
    
    public function hashtagStore(): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid.');
        }
        $name = str_starts_with($_POST['name'], '#') ? $_POST['name'] : '#' . $_POST['name'];
        $slug = generateSlug($name);
        Database::execute("INSERT INTO hashtag (name, slug) VALUES (?, ?)", [$name, $slug]);
        $this->redirectWith('/master/tags', 'success', 'Hashtag berhasil ditambahkan.');
    }

    public function hashtagDelete(string $id): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid'], 403);
        }
        Database::execute("DELETE FROM hashtag WHERE id = ?", [$id]);
        $this->json(['success' => true, 'message' => 'Hashtag berhasil dihapus.']);
    }

    
    
    
    public function template(): void
    {
        $data = Database::fetchAll(
            "SELECT tc.*, p.name as platform_name, p.icon as platform_icon 
             FROM template_caption tc 
             LEFT JOIN platform_sosmed p ON p.id = tc.platform_id 
             ORDER BY tc.name"
        );
        $platforms = Database::fetchAll("SELECT id, name, icon FROM platform_sosmed WHERE is_active = 1 ORDER BY sort_order");
        $this->view('master/template', [
            'title' => 'Template Caption',
            'data' => $data,
            'platforms' => $platforms,
            'breadcrumbs' => [['label' => 'Master Data', 'url' => '#'], ['label' => 'Template', 'url' => '#']],
        ]);
    }

    public function templateStore(): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid.');
        }
        Database::execute(
            "INSERT INTO template_caption (name, content, platform_id, hashtag_placeholder) VALUES (?, ?, ?, ?)",
            [$_POST['name'], $_POST['content'], $_POST['platform_id'] ?: null, $_POST['hashtag_placeholder'] ?? '{hashtag}']
        );
        $this->redirectWith('/master/template', 'success', 'Template berhasil ditambahkan.');
    }

    public function templateUpdate(string $id): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid.');
        }
        Database::execute(
            "UPDATE template_caption SET name = ?, content = ?, platform_id = ?, hashtag_placeholder = ? WHERE id = ?",
            [$_POST['name'], $_POST['content'], $_POST['platform_id'] ?: null, $_POST['hashtag_placeholder'] ?? '{hashtag}', $id]
        );
        $this->redirectWith('/master/template', 'success', 'Template berhasil diupdate.');
    }

    public function templateDelete(string $id): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid'], 403);
        }
        Database::execute("DELETE FROM template_caption WHERE id = ?", [$id]);
        $this->json(['success' => true, 'message' => 'Template berhasil dihapus.']);
    }

    
    
    
    public function lokasi(): void
    {
        $data = Database::fetchAll("SELECT * FROM lokasi_shooting ORDER BY name");
        $this->view('master/lokasi', [
            'title' => 'Lokasi Shooting',
            'data' => $data,
            'breadcrumbs' => [['label' => 'Master Data', 'url' => '#'], ['label' => 'Lokasi', 'url' => '#']],
        ]);
    }

    public function lokasiStore(): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid.');
        }
        Database::execute(
            "INSERT INTO lokasi_shooting (name, address, city, latitude, longitude) VALUES (?, ?, ?, ?, ?)",
            [$_POST['name'], $_POST['address'] ?? '', $_POST['city'] ?? '', $_POST['latitude'] ?? null, $_POST['longitude'] ?? null]
        );
        $this->redirectWith('/master/lokasi', 'success', 'Lokasi berhasil ditambahkan.');
    }

    public function lokasiUpdate(string $id): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid.');
        }
        Database::execute(
            "UPDATE lokasi_shooting SET name = ?, address = ?, city = ?, latitude = ?, longitude = ? WHERE id = ?",
            [$_POST['name'], $_POST['address'] ?? '', $_POST['city'] ?? '', $_POST['latitude'] ?? null, $_POST['longitude'] ?? null, $id]
        );
        $this->redirectWith('/master/lokasi', 'success', 'Lokasi berhasil diupdate.');
    }

    public function lokasiDelete(string $id): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid'], 403);
        }
        Database::execute("DELETE FROM lokasi_shooting WHERE id = ?", [$id]);
        $this->json(['success' => true, 'message' => 'Lokasi berhasil dihapus.']);
    }

    
    
    
    public function talent(): void
    {
        $data = Database::fetchAll("SELECT * FROM talent ORDER BY name");
        $this->view('master/talent', [
            'title' => 'Talent',
            'data' => $data,
            'breadcrumbs' => [['label' => 'Master Data', 'url' => '#'], ['label' => 'Talent', 'url' => '#']],
        ]);
    }

    public function talentStore(): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid.');
        }
        $slug = generateSlug($_POST['name']);
        Database::execute(
            "INSERT INTO talent (name, slug, position, bio, phone, email, instagram) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$_POST['name'], $slug, $_POST['position'] ?? '', $_POST['bio'] ?? '', $_POST['phone'] ?? '', $_POST['email'] ?? '', $_POST['instagram'] ?? '']
        );
        $this->redirectWith('/master/talent', 'success', 'Talent berhasil ditambahkan.');
    }

    public function talentUpdate(string $id): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid.');
        }
        Database::execute(
            "UPDATE talent SET name = ?, position = ?, bio = ?, phone = ?, email = ?, instagram = ? WHERE id = ?",
            [$_POST['name'], $_POST['position'] ?? '', $_POST['bio'] ?? '', $_POST['phone'] ?? '', $_POST['email'] ?? '', $_POST['instagram'] ?? '', $id]
        );
        $this->redirectWith('/master/talent', 'success', 'Talent berhasil diupdate.');
    }

    public function talentDelete(string $id): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid'], 403);
        }
        Database::execute("DELETE FROM talent WHERE id = ?", [$id]);
        $this->json(['success' => true, 'message' => 'Talent berhasil dihapus.']);
    }
}

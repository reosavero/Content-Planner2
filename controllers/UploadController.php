<?php





class UploadController extends Controller
{
    private array $allowedImages = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private array $allowedVideos = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'];
    private array $allowedDocs = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    private int $maxImageSize = 10485760; 
    private int $maxVideoSize = 268435456; 

    


    public function uploadImage(): void
    {
        if (empty($_FILES['file'])) {
            $this->error('Tidak ada file yang diupload');
        }

        $file = $_FILES['file'];
        
        
        $validation = Security::validateFile($file, $this->allowedImages, $this->maxImageSize);
        if (!$validation['valid']) {
            $this->error(implode(', ', $validation['errors']));
        }

        $result = $this->processUpload($file, 'images');
        if ($result['success']) {
            
            $thumbnailPath = $this->generateThumbnail($result['path'], $result['filename']);
            
            $this->success([
                'url' => BASE_URL . '/uploads/' . $result['path'],
                'thumbnail' => $thumbnailPath ? BASE_URL . '/uploads/' . $thumbnailPath : null,
                'filename' => $result['filename'],
                'size' => $result['size'],
                'mime' => $validation['mime_type'],
            ]);
        } else {
            $this->error('Gagal mengupload file');
        }
    }

    


    public function uploadVideo(): void
    {
        if (empty($_FILES['file'])) {
            $this->error('Tidak ada file yang diupload');
        }

        $file = $_FILES['file'];
        
        $validation = Security::validateFile($file, $this->allowedVideos, $this->maxVideoSize);
        if (!$validation['valid']) {
            $this->error(implode(', ', $validation['errors']));
        }

        $result = $this->processUpload($file, 'videos');
        if ($result['success']) {
            $this->success([
                'url' => BASE_URL . '/uploads/' . $result['path'],
                'filename' => $result['filename'],
                'size' => $result['size'],
                'mime' => $validation['mime_type'],
            ]);
        } else {
            $this->error('Gagal mengupload video');
        }
    }

    


    public function uploadThumbnail(): void
    {
        if (empty($_FILES['file'])) {
            $this->error('Tidak ada file yang diupload');
        }

        $file = $_FILES['file'];
        $validation = Security::validateFile($file, $this->allowedImages, $this->maxImageSize);
        if (!$validation['valid']) {
            $this->error(implode(', ', $validation['errors']));
        }

        $result = $this->processUpload($file, 'thumbnails');
        if ($result['success']) {
            $this->success([
                'url' => BASE_URL . '/uploads/' . $result['path'],
                'filename' => $result['filename'],
            ]);
        } else {
            $this->error('Gagal mengupload thumbnail');
        }
    }

    


    public function uploadFile(): void
    {
        if (empty($_FILES['file'])) {
            $this->error('Tidak ada file yang diupload');
        }

        $file = $_FILES['file'];
        $allowed = array_merge($this->allowedImages, $this->allowedVideos, $this->allowedDocs);
        $maxSize = max($this->maxImageSize, $this->maxVideoSize);

        $validation = Security::validateFile($file, $allowed, $maxSize);
        if (!$validation['valid']) {
            $this->error(implode(', ', $validation['errors']));
        }

        $subdir = 'documents';
        if (in_array($validation['mime_type'], $this->allowedImages)) $subdir = 'images';
        if (in_array($validation['mime_type'], $this->allowedVideos)) $subdir = 'videos';

        $result = $this->processUpload($file, $subdir);
        if ($result['success']) {
            $this->success([
                'url' => BASE_URL . '/uploads/' . $result['path'],
                'filename' => $result['filename'],
                'size' => $result['size'],
                'mime' => $validation['mime_type'],
            ]);
        } else {
            $this->error('Gagal mengupload file');
        }
    }

    


    private function processUpload(array $file, string $subdir): array
    {
        $filename = Security::generateFilename($file['name']);
        $yearMonth = date('Y/m');
        $uploadPath = UPLOADS_PATH . $subdir . DIRECTORY_SEPARATOR . $yearMonth;
        
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $destination = $uploadPath . DIRECTORY_SEPARATOR . $filename;
        $relativePath = $subdir . '/' . $yearMonth . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            
            chmod($destination, 0644);
            
            return [
                'success' => true,
                'path' => $relativePath,
                'filename' => $filename,
                'size' => $file['size'],
            ];
        }

        return ['success' => false];
    }

    


    private function generateThumbnail(string $sourcePath, string $filename): ?string
    {
        $sourceFull = UPLOADS_PATH . $sourcePath;
        if (!file_exists($sourceFull)) return null;

        $thumbDir = UPLOADS_PATH . 'thumbnails' . DIRECTORY_SEPARATOR . date('Y/m');
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }

        $thumbFilename = 'thumb_' . $filename;
        $thumbPath = $thumbDir . DIRECTORY_SEPARATOR . $thumbFilename;
        $relativePath = 'thumbnails/' . date('Y/m') . '/' . $thumbFilename;

        
        $imageInfo = getimagesize($sourceFull);
        if (!$imageInfo) return null;

        [$width, $height] = $imageInfo;
        $maxSize = 400;
        
        if ($width <= $maxSize && $height <= $maxSize) return null;

        $ratio = min($maxSize / $width, $maxSize / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        $thumb = imagecreatetruecolor($newWidth, $newHeight);
        
        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($sourceFull);
                imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagejpeg($thumb, $thumbPath, 80);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($sourceFull);
                imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagepng($thumb, $thumbPath, 8);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($sourceFull);
                imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagewebp($thumb, $thumbPath, 80);
                break;
            default:
                imagedestroy($thumb);
                return null;
        }

        imagedestroy($thumb);
        imagedestroy($source);
        chmod($thumbPath, 0644);

        return $relativePath;
    }
}

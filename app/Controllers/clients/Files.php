<?php

namespace App\Controllers\clients;

use CodeIgniter\API\ResponseTrait;

class Files extends BaseClientController
{
    use ResponseTrait;

    /**
     * Display all files with pagination.
     *
     * @return string
     */
    public function index(): string
    {
        $type = 'all';
        $filesData = $this->getFiles($type);
        $commonData = $this->getFileCommonData($type);

        $data = array_merge($commonData, [
            'files_dump' => $filesData['data'] ?? [],
            'pager' => $filesData['pager'] ?? null,
            'totalFiles' => $filesData['total'] ?? 0,
            'current_type' => $type
        ]);

        return $this->renderAppView('users/files/files_all', $data);
    }

    /**
     * Display only images.
     *
     * @return string
     */
    public function images(): string
    {
        return $this->view('images');
    }

    /**
     * Display only videos.
     *
     * @return string
     */
    public function videos(): string
    {
        return $this->view('videos');
    }

    /**
     * Display media files (images + videos).
     *
     * @return string
     */
    public function media(): string
    {
        return $this->view('media');
    }

    /**
     * Display only documents.
     *
     * @return string
     */
    public function documents(): string
    {
        return $this->view('documents');
    }

    /**
     * Display audio files.
     *
     * @return string
     */
    public function audio(): string
    {
        return $this->view('audio');
    }

    /**
     * Display archive files.
     *
     * @return string
     */
    public function archives(): string
    {
        return $this->view('archives');
    }

    /**
     * Display other files.
     *
     * @return string
     */
    public function others(): string
    {
        return $this->view('others');
    }

    /**
     * Single method with parameter for all file types.
     *
     * @param string $type
     * @return string
     */
    public function view(string $type = 'all'): string
    {
        // Validate type parameter
        $validTypes = ['all', 'images', 'videos', 'media', 'documents', 'audio', 'archives', 'others'];
        if (!in_array($type, $validTypes)) {
            return redirect()->to('files');
        }

        // Get file data based on type
        $filesData = $this->getFiles($type);

        // Get common data for view
        $commonData = $this->getFileCommonData($type);

        // Prepare data for the view
        $data = array_merge($commonData, [
            'files_dump' => $filesData['data'] ?? [],
            'pager' => $filesData['pager'] ?? null,
            'totalFiles' => $filesData['total'] ?? 0,
            'current_type' => $type
        ]);

        return $this->renderAppView('users/files/files_with_type', $data);
    }

    /**
     * Get files with complete details.
     *
     * @param string $type
     * @return array
     */
    private function getFiles(string $type): array
    {
        try {
            $db = \Config\Database::connect();
            $query = $db->table('tbl_device_files');

            $query->select('
                id,
                name,
                path,
                is_directory,
                size_bytes,
                formatted_size,
                formatted_date,
                last_modified,
                extension,
                category,
                device_id,
                created_at
            ');
            
            $query->where('owner_id', $this->userId);

            // Filter by type
            switch ($type) {
                case 'images':
                    $query->where('category', 'Image');
                    break;
                case 'videos':
                    $query->where('category', 'Video');
                    break;
                case 'media':
                    $query->whereIn('category', ['Image', 'Video']);
                    break;
                case 'documents':
                    $query->whereIn('category', ['Document', 'Spreadsheet', 'Presentation', 'Ebook']);
                    break;
                case 'audio':
                    $query->where('category', 'Audio');
                    break;
                case 'archives':
                    $query->where('category', 'Archive');
                    break;
                case 'others':
                    $query->whereIn('category', ['Code', 'Font', 'Application', 'Other']);
                    break;
                case 'all':
                default:
                    // No filter
                    break;
            }

            $query->orderBy('last_modified', 'DESC');

            // Get total count for pagination
            $total = $query->countAllResults(false);

            // Apply pagination
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->perPage;
            $offset = ($page - 1) * $perPage;

            $results = $query->limit($perPage, $offset)->get()->getResultArray();

            log_message('debug', 'getFiles data check | Type: ' . $type . ' | UserID: ' . $this->userId . ' | Total: ' . $total . ' | Retrieved: ' . count($results));
            
            // Set up pagination
            $pager = \Config\Services::pager();
            $pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return [
                'data' => $results,
                'pager' => $pager,
                'total' => $total
            ];

        } catch (\Exception $e) {
            log_message('error', 'getFiles error: ' . $e->getMessage());
            return ['data' => [], 'pager' => null, 'total' => 0];
        }
    }

    public function delete($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_file((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'File deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete file.']);
    }
}

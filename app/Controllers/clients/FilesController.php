<?php

namespace App\Controllers\clients;

use CodeIgniter\API\ResponseTrait;

class FilesController extends BaseClientController
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
            'filesCounts' => $this->getFileCounts(),
            'files_desc' => $this->getFileDescription($type),
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
            'filesCounts' => $this->getFileCounts(),
            'files_desc' => $this->getFileDescription($type),
            'current_type' => $type
        ]);

        return $this->renderAppView('users/files/files_with_type', $data);
    }

    /**
     * Count files per category for the header counters.
     *
     * @return array
     */
    private function getFileCounts(): array
    {
        $empty = [
            'all' => 0,
            'media' => 0,
            'documents' => 0,
            'audio' => 0,
            'archives' => 0,
            'others' => 0,
        ];

        try {
            $db = \Config\Database::connect();
            $query = $db->table('tbl_extracted_device_files')
                ->select('category, COUNT(*) AS total')
                ->groupBy('category');
            $this->applyExclusionFilters($query);
            $rows = $query->get()->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'getFileCounts error: ' . $e->getMessage());
            return $empty;
        }

        $counts = $empty;
        foreach ($rows as $row) {
            $cat = strtolower((string) ($row['category'] ?? 'Other'));
            $total = (int) ($row['total'] ?? 0);
            $counts['all'] += $total;

            switch ($cat) {
                case 'image':
                case 'video':
                    $counts['media'] += $total;
                    break;
                case 'document':
                case 'spreadsheet':
                case 'presentation':
                case 'ebook':
                    $counts['documents'] += $total;
                    break;
                case 'audio':
                    $counts['audio'] += $total;
                    break;
                case 'archive':
                    $counts['archives'] += $total;
                    break;
                default: // code, font, application, other, unknown
                    $counts['others'] += $total;
                    break;
            }
        }

        return $counts;
    }

    /**
     * Page-specific description shown under the page heading.
     *
     * @param string $type
     * @return string
     */
    private function getFileDescription(string $type): string
    {
        $descriptions = [
            'all' => 'View and manage all files synced from your device',
            'images' => 'Images stored on your device',
            'videos' => 'Videos stored on your device',
            'media' => 'Images and videos stored on your device',
            'documents' => 'Documents and spreadsheets stored on your device',
            'audio' => 'Audio files stored on your device',
            'archives' => 'Compressed archives stored on your device',
            'others' => 'Other file types stored on your device',
        ];

        return $descriptions[$type] ?? 'FilesController stored on your device';
    }

    /**
     * Apply shared exclusion filters to a query on tbl_extracted_device_files:
     *  - Exclude directories / folders (is_directory = 0)
     *  - Exclude 0-byte files (size_bytes > 0)
     *  - Exclude hidden files/dirs starting with '.' (.nomedia, .thumbnails, .cache)
     *  - Exclude internal system/cache paths (/.thumbnails/, /.cache/, /Android/data/, /Android/obb/, /LOST.DIR/)
     *  - Exclude temporary / junk file extensions (.tmp, .log, .bak, .swp, .part, .crdownload, Thumbs.db)
     *
     * @param \CodeIgniter\Database\BaseBuilder $query
     * @return \CodeIgniter\Database\BaseBuilder
     */
    private function applyExclusionFilters($query)
    {
        $query->where('owner_id', $this->userId);
        
        // 1. Exclude directories / folders and 0-byte empty files
        $query->where('is_directory', 0);
        $query->where('size_bytes >', 0);

        // 2. Exclude hidden files starting with '.'
        $query->where('name NOT LIKE', '.%');

        // 3. Exclude hidden, cache, thumbnail, and system path patterns
        $excludedPaths = [
            '%/.thumbnails/%',
            '%/.cache/%',
            '%/.trashed-%',
            '%/.nomedia/%',
            '%/.telegram/%',
            '%/.whatsapp/%',
            '%/Android/data/%',
            '%/Android/obb/%',
            '%/LOST.DIR/%',
            '/data/user/0/%',
            '/sys/%',
            '/proc/%',
            '/dev/%'
        ];

        foreach ($excludedPaths as $p) {
            $query->where('path NOT LIKE', $p);
        }

        // 4. Exclude system temp/junk files and logs
        $junkExtensions = ['tmp', 'temp', 'bak', 'swp', 'log', 'chk', 'part', 'crdownload'];
        foreach ($junkExtensions as $ext) {
            $query->where('extension !=', $ext);
            $query->where('extension !=', strtoupper($ext));
        }

        $query->whereNotIn('name', ['Thumbs.db', '.DS_Store', 'desktop.ini']);

        return $query;
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
            $query = $db->table('tbl_extracted_device_files');

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
            
            $this->applyExclusionFilters($query);

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
        if (!$this->request->isAJAX() || $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_file((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'File deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete file.']);
    }
}

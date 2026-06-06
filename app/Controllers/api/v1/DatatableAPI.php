<?php

namespace App\Controllers\api\v1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * DatatableAPI Controller
 *
 * Handles server-side DataTables AJAX requests for the web dashboard.
 * Routes for this controller are inside the session filter group, so the
 * Shield session filter has already authenticated the user before any
 * method here runs.
 *
 * NOTE: Extends BaseController (not ResourceController) so that CI4's
 * initController() lifecycle runs correctly and Shield's session authenticator
 * is properly warmed up by the time getUserId() is called.
 */
class DatatableAPI extends BaseController
{
    use ResponseTrait;

    protected $db;

    /**
     * {@inheritdoc}
     */
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        log_message('info', '[DatatableAPI] initController called!');
        $this->db = \Config\Database::connect();
    }

    /**
     * Get the authenticated user ID.
     *
     * The session filter has already run and verified authentication.
     * We explicitly call auth('session')->loggedIn() to warm up Shield's
     * in-memory user state before reading the ID, which is required
     * because Shield is lazy — it only loads the user from the PHP session
     * when loggedIn() or check() is called.
     *
     * Fallback reads directly from the PHP session using Shield's configured
     * session field key ('user_v4' per Auth::$sessionConfig['field']).
     */
    private function getUserId(): int|null
    {
        log_message('info', '[DatatableAPI] getUserId started. Session ID: ' . session_id());

        // Step 1: Warm up Shield's session authenticator explicitly
        if (auth('session')->loggedIn()) {
            $id = auth('session')->id();
            if ($id) {
                log_message('info', '[DatatableAPI] getUserId via auth(session): ' . $id);
                return (int) $id;
            }
        }

        // Step 2: Read directly from Shield's session key as fallback
        $sessionUserId = session()->get('user_v4');
        if ($sessionUserId) {
            log_message('info', '[DatatableAPI] getUserId via session->get(user_v4): ' . $sessionUserId);
            return (int) $sessionUserId;
        }

        // Log failure details so we can diagnose
        log_message('info', '[DatatableAPI] getUserId FAILED — auth loggedIn: '
            . var_export(auth('session')->loggedIn(), true)
            . ' | session user_v4: ' . var_export(session()->get('user_v4'), true)
            . ' | session id: ' . session_id()
        );

        return null;
    }

    // =========================================================================
    // APP USAGE DATATABLE ENDPOINT
    // =========================================================================

    /**
     * POST /api/v1/DatatableAPI/getAppUsageDetails
     *
     * Returns paginated/searchable app usage records for a specific package,
     * formatted for DataTables server-side processing.
     */
    public function getAppUsageDetails(): ResponseInterface
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->failUnauthorized('Session expired. Please refresh the page.');
        }

        $packageName = $this->request->getPost('package_name');
        if (!$packageName) {
            return $this->failValidationErrors(['package_name' => 'package_name is required']);
        }

        $draw             = (int) ($this->request->getPost('draw') ?? 1);
        $start            = (int) ($this->request->getPost('start') ?? 0);
        $length           = (int) ($this->request->getPost('length') ?? 50);
        $search           = $this->request->getPost('search')['value'] ?? '';
        $orderColumnIndex = (int) ($this->request->getPost('order')[0]['column'] ?? 5);
        $orderDir         = strtolower($this->request->getPost('order')[0]['dir'] ?? 'desc');
        $orderDir         = in_array($orderDir, ['asc', 'desc']) ? $orderDir : 'desc';

        $columns = [
            0 => 'app_name',
            1 => 'package_name',
            2 => 'time_taken_formatted',
            3 => 'times_opened',
            4 => 'last_time_used',
            5 => 'extracted_at',
        ];
        $orderBy = $columns[$orderColumnIndex] ?? 'extracted_at';

        $builder = $this->db->table('tbl_app_usage')
            ->where('owner_id', $userId)
            ->where('package_name', $packageName);

        $totalRecords = $builder->countAllResults(false);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('app_name', $search)
                ->orLike('package_name', $search)
                ->groupEnd();
        }

        $filteredRecords = $builder->countAllResults(false);

        $rows = $builder->orderBy($orderBy, $orderDir)
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        $formattedData = [];
        foreach ($rows as $row) {
            $lastUsed  = !empty($row['last_time_used'])
                ? date('Y-m-d H:i:s', (int) ($row['last_time_used'] / 1000))
                : '—';
            $extracted = !empty($row['extracted_at'])
                ? date('Y-m-d H:i:s', (int) ($row['extracted_at'] / 1000))
                : '—';

            $formattedData[] = [
                htmlspecialchars($row['app_name'] ?? $row['package_name']),
                htmlspecialchars($row['package_name']),
                $row['time_taken_formatted'] ?? '0m',
                (int) ($row['times_opened'] ?? 0),
                $lastUsed,
                $extracted,
            ];
        }

        return $this->respond([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $formattedData,
        ]);
    }

    // =========================================================================
    // NOTIFICATIONS DATATABLE ENDPOINT
    // =========================================================================

    /**
     * POST /api/v1/DatatableAPI/getNotificationDetails
     *
     * Returns paginated/searchable notification records for a specific package,
     * formatted for DataTables server-side processing.
     */
    public function getNotificationDetails(): ResponseInterface
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->failUnauthorized('Session expired. Please refresh the page.');
        }

        $packageName = $this->request->getPost('package_name');
        if (!$packageName) {
            return $this->failValidationErrors(['package_name' => 'package_name is required']);
        }

        $draw             = (int) ($this->request->getPost('draw') ?? 1);
        $start            = (int) ($this->request->getPost('start') ?? 0);
        $length           = (int) ($this->request->getPost('length') ?? 50);
        $search           = $this->request->getPost('search')['value'] ?? '';
        $orderColumnIndex = (int) ($this->request->getPost('order')[0]['column'] ?? 3);
        $orderDir         = strtolower($this->request->getPost('order')[0]['dir'] ?? 'desc');
        $orderDir         = in_array($orderDir, ['asc', 'desc']) ? $orderDir : 'desc';

        $columns = [
            0 => 'title',
            1 => 'text',
            2 => 'action',
            3 => 'notification_timestamp',
            4 => 'extracted_at',
        ];
        $orderBy = $columns[$orderColumnIndex] ?? 'notification_timestamp';

        $builder = $this->db->table('tbl_notifications')
            ->where('owner_id', $userId)
            ->where('package_name', $packageName);

        $totalRecords = $builder->countAllResults(false);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('title', $search)
                ->orLike('text', $search)
                ->groupEnd();
        }

        $filteredRecords = $builder->countAllResults(false);

        $rows = $builder->orderBy($orderBy, $orderDir)
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        $formattedData = [];
        foreach ($rows as $row) {
            $notifTime = !empty($row['notification_timestamp'])
                ? date('Y-m-d H:i:s', (int) ($row['notification_timestamp'] / 1000))
                : '—';
            $extracted = !empty($row['extracted_at'])
                ? date('Y-m-d H:i:s', (int) ($row['extracted_at'] / 1000))
                : '—';

            $badge = $row['action'] === 'POSTED'
                ? '<span class="badge badge-success">POSTED</span>'
                : '<span class="badge badge-danger">REMOVED</span>';

            $screenBadge = !empty($row['is_screen_notification'])
                ? '<span class="badge badge-warning">Screen</span>'
                : '<span class="badge badge-light text-muted">—</span>';

            $formattedData[] = [
                htmlspecialchars($row['title'] ?? ''),
                htmlspecialchars($row['text'] ?? ''),
                htmlspecialchars($row['sender'] ?? '—'),
                $screenBadge,
                $badge,
                $notifTime,
                $extracted,
            ];
        }

        return $this->respond([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $formattedData,
        ]);
    }
}

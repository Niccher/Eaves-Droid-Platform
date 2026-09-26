<?php

namespace App\Controllers\superadmin;

class OmniSearchController extends BaseSuperadminController
{
    public function index()
    {
        $db = $this->getDb();
        $data = ['pag' => 'superadmin-omni-search'];

        $query = $this->request->getGet('q');

        $data['query'] = $query ?? '';
        $data['categoryResults'] = [];
        $data['total'] = 0;
        $data['categoryCounts'] = [];

        if ($query !== null && $query !== '') {
            $this->logAdminAction('omni_search', 'low', true, [
                'keyword' => $query,
            ]);
        }

        $catMeta = [
            'sms'      => ['label' => 'SMS',     'icon' => 'fa-sms',        'color' => 'info',    'table' => 'tbl_extracted_sms'],
            'calls'    => ['label' => 'CallsController',   'icon' => 'fa-phone',      'color' => 'success',  'table' => 'tbl_extracted_call_logs'],
            'contacts' => ['label' => 'Contacts', 'icon' => 'fa-address-book', 'color' => 'warning', 'table' => 'tbl_extracted_contacts'],
            'locations'=> ['label' => 'Locations', 'icon' => 'fa-map-marker-alt', 'color' => 'danger', 'table' => 'tbl_extracted_locations'],
            'accounts' => ['label' => 'Accounts', 'icon' => 'fa-user-circle', 'color' => 'primary', 'table' => 'tbl_accounts'],
            'files'    => ['label' => 'Files',   'icon' => 'fa-file',       'color' => 'secondary','table' => 'tbl_extracted_device_files'],
            'apps'     => ['label' => 'Apps',    'icon' => 'fa-th',         'color' => 'dark',     'table' => 'tbl_extracted_installed_apps'],
        ];

        $data['catMeta'] = $catMeta;

        if ($query !== null && $query !== '') {
            $search = trim($query);
            $allResults = [];

            foreach ($catMeta as $key => $meta) {
                $table = $meta['table'];
                $rows = $this->searchTable($db, $table, $search, $key);
                $data['categoryCounts'][$key] = count($rows);

                if ($rows !== []) {
                    $data['categoryResults'][$key] = $rows;
                    foreach ($rows as $r) {
                        $allResults[] = $r;
                    }
                }
            }

            usort($allResults, function ($a, $b) {
                return strcmp($b['timestamp'] ?? '', $a['timestamp'] ?? '');
            });

            $data['results'] = array_slice($allResults, 0, 200);
            $data['total'] = count($data['results']);
            $data['truncated'] = count($allResults) > 200;
        }

        return $this->renderView('superadmin/omni_search', $data);
    }

    private function searchTable($db, string $table, string $search, string $category): array
    {
        $searchFields = $this->getSearchFields($table, $category);

        $query = $db->table($table)
            ->select("{$table}.*, users.username, users.id as user_id")
            ->join('users', "users.id = {$table}.owner_id", 'left')
            ->groupStart();

        $first = true;
        foreach ($searchFields as $field) {
            if ($first) {
                $query->like($field, $search, 'both', true);
                $first = false;
            } else {
                $query->orLike($field, $search, 'both', true);
            }
        }

        $query = $query->groupEnd()
            ->orderBy('created_at', 'DESC')
            ->limit(200)
            ->get()
            ->getResultArray();

        $results = [];
        foreach ($query as $r) {
            $results[] = $this->formatResult($r, $category);
        }

        return $results;
    }

    private function getSearchFields(string $table, string $category): array
    {
        $fields = match ($category) {
            'sms'      => ['address', 'body', 'formatted_address', 'verified_sender', 'mms_subject'],
            'calls'    => ['phone_number', 'call_subject', 'call_notes', 'cnap_name', 'connected_number', 'dialing_number', 'geolocation'],
            'contacts' => ['display_name', 'phone_numbers', 'emails', 'nickname', 'phonetic_name', 'companies', 'sip_address'],
            'locations'=> ['place_name', 'place_address', 'latitude', 'longitude', 'geofence_transitions'],
            'accounts' => ['account_name', 'account_type', 'account_label'],
            'files'    => ['name', 'path', 'extension', 'mime_type', 'formatted_size', 'document_title', 'document_subject', 'document_keywords', 'category'],
            'apps'     => ['package_name', 'app_name', 'version_name', 'category', 'app_category'],
            default    => [],
        };

        return $fields;
    }

    private function formatResult(array $r, string $category): array
    {
        $common = [
            'user_id'   => (int) ($r['user_id'] ?? 0),
            'username'  => $r['username'] ?? 'Unknown',
            'timestamp' => $r['created_at'] ?? '',
        ];

        return match ($category) {
            'sms' => array_merge($common, [
                'type'    => 'SMS',
                'icon'    => 'fa-sms',
                'preview' => base64_decode($r['body'] ?? '', true) ?: ($r['body'] ?? ''),
                'detail'  => $r['address'] ?? '',
            ]),
            'calls' => array_merge($common, [
                'type'    => 'Call',
                'icon'    => 'fa-phone',
                'preview' => $r['call_subject'] ?? $r['phone_number'] ?? '',
                'detail'  => ($r['call_type'] ?? '') . ' · ' . ($r['phone_number'] ?? ''),
            ]),
            'contacts' => array_merge($common, [
                'type'    => 'Contact',
                'icon'    => 'fa-address-book',
                'preview' => $r['display_name'] ?? '',
                'detail'  => $r['phone_numbers'] ?? '',
            ]),
            'locations' => array_merge($common, [
                'type'    => 'LocationController',
                'icon'    => 'fa-map-marker-alt',
                'preview' => $r['place_name'] ?? $r['place_address'] ?? '',
                'detail'  => ($r['latitude'] !== null && $r['longitude'] !== null) ? $r['latitude'] . ', ' . $r['longitude'] : '',
            ]),
            'accounts' => array_merge($common, [
                'type'    => 'AccountController',
                'icon'    => 'fa-user-circle',
                'preview' => $r['account_name'] ?? '',
                'detail'  => $r['account_type'] ?? '',
            ]),
            'files' => array_merge($common, [
                'type'    => 'File',
                'icon'    => 'fa-file',
                'preview' => $r['name'] ?? '',
                'detail'  => strtoupper($r['extension'] ?? '') . ' · ' . ($r['formatted_size'] ?? '') . ' · ' . ($r['category'] ?? ''),
            ]),
            'apps' => array_merge($common, [
                'type'    => 'App',
                'icon'    => 'fa-th',
                'preview' => $r['app_name'] ?? $r['package_name'] ?? '',
                'detail'  => ($r['version_name'] ?? '') . ' · ' . ($r['app_category'] ?? $r['category'] ?? ''),
            ]),
            default => $common,
        };
    }

    /**
     * AJAX endpoint for global Omni Search modal (Ctrl+K).
     */
    public function ajaxSearch()
    {
        $db = $this->getDb();
        $query = trim((string) $this->request->getGet('q'));

        if ($query === '' || strlen($query) < 2) {
            return $this->response->setJSON([
                'success' => true,
                'query'   => $query,
                'results' => [],
                'total'   => 0,
            ]);
        }

        $catMeta = [
            'sms'       => ['label' => 'SMS',       'icon' => 'fa-sms',          'color' => 'info',    'table' => 'tbl_extracted_sms'],
            'calls'     => ['label' => 'Calls',     'icon' => 'fa-phone',        'color' => 'success', 'table' => 'tbl_extracted_call_logs'],
            'contacts'  => ['label' => 'Contacts',  'icon' => 'fa-address-book', 'color' => 'warning', 'table' => 'tbl_extracted_contacts'],
            'locations' => ['label' => 'Locations', 'icon' => 'fa-map-marker-alt','color' => 'danger',  'table' => 'tbl_extracted_locations'],
            'files'     => ['label' => 'Files',     'icon' => 'fa-file',         'color' => 'secondary','table' => 'tbl_extracted_device_files'],
            'apps'      => ['label' => 'Apps',      'icon' => 'fa-th',           'color' => 'dark',     'table' => 'tbl_extracted_installed_apps'],
        ];

        $results = [];
        $total = 0;

        foreach ($catMeta as $key => $meta) {
            $table = $meta['table'];
            $searchFields = $this->getSearchFields($table, $key);
            if (empty($searchFields)) continue;

            $builder = $db->table($table)
                ->select("{$table}.*, users.username, users.id as user_id")
                ->join('users', "users.id = {$table}.owner_id", 'left')
                ->groupStart();

            $first = true;
            foreach ($searchFields as $field) {
                if ($first) {
                    $builder->like($field, $query, 'both', true);
                    $first = false;
                } else {
                    $builder->orLike($field, $query, 'both', true);
                }
            }
            $rows = $builder->groupEnd()
                ->orderBy('created_at', 'DESC')
                ->limit(6)
                ->get()
                ->getResultArray();

            if (!empty($rows)) {
                $formatted = [];
                foreach ($rows as $r) {
                    $item = $this->formatResult($r, $key);
                    $item['category_label'] = $meta['label'];
                    $item['category_color'] = $meta['color'];
                    $formatted[] = $item;
                    $total++;
                }
                $results[$key] = [
                    'label' => $meta['label'],
                    'icon'  => $meta['icon'],
                    'color' => $meta['color'],
                    'items' => $formatted,
                ];
            }
        }

        // Also search Users table
        $userRows = $db->table('users')
            ->select('id as user_id, username, active, status, created_at')
            ->like('username', $query, 'both', true)
            ->limit(5)
            ->get()
            ->getResultArray();

        if (!empty($userRows)) {
            $userItems = [];
            foreach ($userRows as $u) {
                $userItems[] = [
                    'user_id'   => (int) $u['user_id'],
                    'username'  => $u['username'],
                    'type'      => 'User',
                    'icon'      => 'fa-user',
                    'preview'   => $u['username'],
                    'detail'    => 'Account #' . $u['user_id'] . ' · ' . ($u['active'] ? 'Active' : 'Inactive'),
                    'timestamp' => $u['created_at'] ?? '',
                    'category_label' => 'Users',
                    'category_color' => 'primary',
                ];
                $total++;
            }
            $results['users'] = [
                'label' => 'Users',
                'icon'  => 'fa-user',
                'color' => 'primary',
                'items' => $userItems,
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'query'   => $query,
            'results' => $results,
            'total'   => $total,
        ]);
    }
}
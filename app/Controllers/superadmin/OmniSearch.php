<?php

namespace App\Controllers\superadmin;

class OmniSearch extends BaseSuperadminController
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
            'calls'    => ['label' => 'Calls',   'icon' => 'fa-phone',      'color' => 'success',  'table' => 'tbl_extracted_call_logs'],
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
                'type'    => 'Location',
                'icon'    => 'fa-map-marker-alt',
                'preview' => $r['place_name'] ?? $r['place_address'] ?? '',
                'detail'  => ($r['latitude'] !== null && $r['longitude'] !== null) ? $r['latitude'] . ', ' . $r['longitude'] : '',
            ]),
            'accounts' => array_merge($common, [
                'type'    => 'Account',
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
}
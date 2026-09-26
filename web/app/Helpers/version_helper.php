<?php

if (!function_exists('get_system_version_data')) {
    function get_system_version_data(): array
    {
        static $cachedVersionData = null;
        if ($cachedVersionData !== null) {
            return $cachedVersionData;
        }

        $version = '2.7.0';
        $build = 20700;
        $releaseName = 'Security Hardening, Plans Definitions UI, Correlation Tier Gating & Label Cleanup';
        $changelogs = [];

        // 1. Try querying the database first (Runtime Source of Truth)
        try {
            $db = \Config\Database::connect();
            $v = $db->table('system_versions')
                ->where('is_current', 1)
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();
            if ($v) {
                $changelogs = $db->table('system_changelogs')
                    ->where('version_id', (int) $v['id'])
                    ->get()
                    ->getResultArray();
                $cachedVersionData = [
                    'platform_version'   => $v['version'],
                    'platform_build'     => $v['build_number'],
                    'platform_name'      => $v['release_name'],
                    'version_changelogs' => $changelogs,
                ];
                return $cachedVersionData;
            }
        } catch (\Throwable $e) {
            log_message('debug', 'Version DB query failed, falling back to files: ' . $e->getMessage());
        }

        // 2. Fallback: parse VERSION.json and CHANGELOG.md files dynamically
        $versionFile = ROOTPATH . 'VERSION.json';
        $changelogFile = ROOTPATH . 'CHANGELOG.md';

        if (file_exists($versionFile)) {
            $versionData = json_decode(file_get_contents($versionFile), true);
            if ($versionData) {
                $version = $versionData['version'] ?? $version;
                $build = $versionData['build'] ?? $build;
                $releaseName = $versionData['release_name'] ?? $releaseName;
            }
        }

        if (file_exists($changelogFile)) {
            $changelogContent = file_get_contents($changelogFile);
            $pattern = '/##\s*\[(' . preg_quote($version, '/') . ')\](.*?)(?=##\s*\[|$)/s';
            if (preg_match($pattern, $changelogContent, $matches)) {
                $versionBlock = $matches[2];
                $sectionPattern = '/###\s*(\w+)(.*?)(?=###\s*\w+|$)/s';
                if (preg_match_all($sectionPattern, $versionBlock, $sections, PREG_SET_ORDER)) {
                    foreach ($sections as $section) {
                        $categoryName = strtolower($section[1]);
                        $category = 'feature';
                        if ($categoryName === 'fixed' || $categoryName === 'fix') {
                            $category = 'fix';
                        } elseif ($categoryName === 'security') {
                            $category = 'security';
                        } elseif ($categoryName === 'changed') {
                            $category = 'capability';
                        } elseif ($categoryName === 'removed') {
                            $category = 'removed';
                        }

                        $bullets = explode("\n- ", "\n" . trim($section[2]));
                        foreach ($bullets as $bullet) {
                            $bullet = trim($bullet);
                            if (empty($bullet)) continue;

                            if (str_starts_with($bullet, '- ')) {
                                $bullet = substr($bullet, 2);
                            }

                            $title = 'Update';
                            $description = $bullet;
                            $component = 'webapp';

                            if (preg_match('/^\*\*(.*?)\*\*\s*(?:\((.*?)\))?:\s*(.*)$/s', $bullet, $bMatches)) {
                                $title = trim($bMatches[1]);
                                $compTag = trim($bMatches[2] ?? '');
                                if (!empty($compTag)) {
                                    $component = strtolower(str_replace('`', '', $compTag));
                                }
                                $description = trim($bMatches[3]);
                            }

                            $changelogs[] = [
                                'category'    => $category,
                                'title'       => $title,
                                'description' => $description,
                                'component'   => $component,
                            ];
                        }
                    }
                }
            }
        }

        return [
            'platform_version'   => $version,
            'platform_build'     => $build,
            'platform_name'      => $releaseName,
            'version_changelogs' => $changelogs,
        ];
    }
}

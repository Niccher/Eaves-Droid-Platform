<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SyncVersion extends BaseCommand
{
    protected $group       = 'System';
    protected $name        = 'system:sync-version';
    protected $description = 'Synchronize system version manifest and changelog between files and database.';
    protected $usage       = 'system:sync-version [options]';
    protected $options     = [
        '--import' => 'Read VERSION.json and CHANGELOG.md to update the database tables.',
        '--export' => 'Read database tables and regenerate/write VERSION.json and CHANGELOG.md.',
    ];

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $import = array_key_exists('import', $params) || CLI::getOption('import');
        $export = array_key_exists('export', $params) || CLI::getOption('export');

        if (!$import && !$export) {
            CLI::write('Please specify either --import or --export options.', 'red');
            return;
        }

        if ($import) {
            $this->importFromFile($db);
        } elseif ($export) {
            $this->exportToFiles($db);
        }
    }

    private function importFromFile($db)
    {
        $versionFile = ROOTPATH . 'VERSION.json';
        $changelogFile = ROOTPATH . 'CHANGELOG.md';

        if (!file_exists($versionFile)) {
            CLI::write("VERSION.json not found at: {$versionFile}", 'red');
            return;
        }

        CLI::write("Reading version manifest from: VERSION.json...", 'yellow');
        $versionData = json_decode(file_get_contents($versionFile), true);
        if (!$versionData) {
            CLI::write("Invalid or corrupt VERSION.json file.", 'red');
            return;
        }

        $version = $versionData['version'] ?? '2.7.0';
        $buildNumber = $versionData['build'] ?? 20700;
        $releaseName = $versionData['release_name'] ?? '';
        $releaseDate = $versionData['release_date'] ?? date('Y-m-d');

        // Check if table system_versions exists
        if (!$db->tableExists('system_versions')) {
            CLI::write("Table system_versions does not exist.", 'red');
            return;
        }

        // De-current existing versions
        $db->table('system_versions')->update(['is_current' => 0]);
        if ($db->tableExists('db_versions')) {
            $db->table('db_versions')->update(['is_current' => 0]);
        }

        // Insert or update version row
        $existing = $db->table('system_versions')->where('version', $version)->get()->getRowArray();
        if ($existing) {
            $db->table('system_versions')->where('id', $existing['id'])->update([
                'build_number' => $buildNumber,
                'release_name' => $releaseName,
                'is_current'   => 1,
                'released_at'  => $releaseDate . ' 00:00:00',
            ]);
            $versionId = $existing['id'];
            CLI::write("Updated existing version {$version} in system_versions.", 'green');
        } else {
            $db->table('system_versions')->insert([
                'version'      => $version,
                'build_number' => $buildNumber,
                'release_name' => $releaseName,
                'release_type' => 'minor',
                'is_current'   => 1,
                'released_at'  => $releaseDate . ' 00:00:00',
            ]);
            $versionId = $db->insertID();
            CLI::write("Inserted new version {$version} into system_versions.", 'green');
        }

        // Sync db_versions
        if ($db->tableExists('db_versions')) {
            $dbExisting = $db->table('db_versions')->where('version', $version)->get()->getRowArray();
            if ($dbExisting) {
                $db->table('db_versions')->where('id', $dbExisting['id'])->update([
                    'build_number' => $buildNumber,
                    'release_name' => $releaseName,
                    'is_current'   => 1,
                ]);
            } else {
                $db->table('db_versions')->insert([
                    'version'      => $version,
                    'build_number' => $buildNumber,
                    'release_name' => $releaseName,
                    'is_current'   => 1,
                    'applied_at'   => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // Parse and sync changelogs
        if (file_exists($changelogFile) && $db->tableExists('system_changelogs')) {
            // Delete old changelogs for this version
            $db->table('system_changelogs')->where('version_id', $versionId)->delete();

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

                            $db->table('system_changelogs')->insert([
                                'version_id'  => $versionId,
                                'category'    => $category,
                                'title'       => $title,
                                'description' => $description,
                                'component'   => $component,
                            ]);
                        }
                    }
                }
            }
            CLI::write("Imported changelog bullets from CHANGELOG.md for version {$version}.", 'green');
        }
    }

    private function exportToFiles($db)
    {
        $versionFile = ROOTPATH . 'VERSION.json';
        $changelogFile = ROOTPATH . 'CHANGELOG.md';

        if (!$db->tableExists('system_versions')) {
            CLI::write("Table system_versions does not exist.", 'red');
            return;
        }

        // Fetch current active version
        $current = $db->table('system_versions')->where('is_current', 1)->orderBy('id', 'DESC')->get()->getRowArray();
        if (!$current) {
            CLI::write("No active version marked in the database.", 'red');
            return;
        }

        $version = $current['version'];
        $buildNumber = (int)$current['build_number'];
        $releaseName = $current['release_name'];
        $releaseDate = date('Y-m-d', strtotime($current['released_at'] ?? 'now'));

        // Load all versions to rebuild CHANGELOG.md
        $versions = $db->table('system_versions')->orderBy('id', 'DESC')->get()->getResultArray();

        // Build VERSION.json
        $versionJson = [
            'version'      => $version,
            'build'        => $buildNumber,
            'release_name' => $releaseName,
            'release_date' => $releaseDate,
            'components'   => [
                'webapp'    => $version,
                'android'   => $version,
                'ml_engine' => '2.5.0'
            ]
        ];

        file_put_contents($versionFile, json_encode($versionJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
        CLI::write("Regenerated VERSION.json for version {$version}.", 'green');

        // Rebuild CHANGELOG.md
        $changelogMd = "# Platform Changelog — Eaves Droid Ecosystem" . PHP_EOL . PHP_EOL;
        $changelogMd .= "All notable changes across the Eaves Droid platform (`WebApp`, `Android Client`, and `ML Engine`) will be documented in this file." . PHP_EOL . PHP_EOL;
        $changelogMd .= "The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)," . PHP_EOL;
        $changelogMd .= "and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html)." . PHP_EOL . PHP_EOL;
        $changelogMd .= "---" . PHP_EOL . PHP_EOL;

        foreach ($versions as $v) {
            $vDate = date('Y-m-d', strtotime($v['released_at'] ?? 'now'));
            $changelogMd .= "## [{$v['version']}] - {$vDate} — {$v['release_name']}" . PHP_EOL . PHP_EOL;

            // Fetch changelogs for this version
            $logs = $db->table('system_changelogs')->where('version_id', $v['id'])->get()->getResultArray();
            
            $sections = [
                'feature'    => [],
                'capability' => [],
                'security'   => [],
                'fix'        => [],
                'removed'    => []
            ];

            foreach ($logs as $log) {
                $category = $log['category'];
                if ($category === 'feature') {
                    $sections['feature'][] = $log;
                } elseif ($category === 'capability') {
                    $sections['capability'][] = $log;
                } elseif ($category === 'security') {
                    $sections['security'][] = $log;
                } elseif ($category === 'fix') {
                    $sections['fix'][] = $log;
                } elseif ($category === 'removed') {
                    $sections['removed'][] = $log;
                }
            }

            $sectionHeaders = [
                'feature'    => '### Added',
                'capability' => '### Changed',
                'security'   => '### Security',
                'fix'        => '### Fixed',
                'removed'    => '### Removed'
            ];

            foreach ($sections as $cat => $items) {
                if (empty($items)) continue;
                $changelogMd .= $sectionHeaders[$cat] . PHP_EOL;
                foreach ($items as $item) {
                    $comp = !empty($item['component']) ? " (`" . esc($item['component']) . "`)" : "";
                    $changelogMd .= "- **" . $item['title'] . "**{$comp}: " . $item['description'] . PHP_EOL;
                }
                $changelogMd .= PHP_EOL;
            }

            $changelogMd .= "---" . PHP_EOL . PHP_EOL;
        }

        file_put_contents($changelogFile, rtrim($changelogMd) . PHP_EOL);
        CLI::write("Regenerated CHANGELOG.md from database.", 'green');
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateSystemVersionToV251 extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Mark all existing system_versions and db_versions as not current
        if ($db->tableExists('system_versions')) {
            $db->table('system_versions')->update(['is_current' => 0]);
        }
        if ($db->tableExists('db_versions')) {
            $db->table('db_versions')->update(['is_current' => 0]);
        }

        // 2. Insert new version into system_versions
        if ($db->tableExists('system_versions')) {
            $db->table('system_versions')->ignore(true)->insert([
                'version'      => '2.5.1',
                'build_number' => 20501,
                'release_name' => 'Private File Gateway, UUID Obfuscation & PDF Uploads',
                'release_type' => 'patch',
                'is_current'   => 1,
                'released_at'  => date('Y-m-d H:i:s'),
            ]);

            // Get the inserted version ID
            $versionId = $db->insertID();

            // Insert changelogs
            if ($db->tableExists('system_changelogs') && $versionId > 0) {
                $db->table('system_changelogs')->insertBatch([
                    [
                        'version_id'  => $versionId,
                        'category'    => 'feature',
                        'title'       => 'Secure Support Chat Gateway & Polling',
                        'description' => 'Migrated real-time communications to a highly efficient 5-second AJAX polling infrastructure, complete with a live navbar unread badge indicator.',
                        'component'   => 'webapp',
                    ],
                    [
                        'version_id'  => $versionId,
                        'category'    => 'security',
                        'title'       => 'Private Attachments & Access Control',
                        'description' => 'Stored user file uploads outside the public web root with credentials/ownership checks on access.',
                        'component'   => 'webapp',
                    ],
                    [
                        'version_id'  => $versionId,
                        'category'    => 'security',
                        'title'       => 'UUID Request Obfuscation',
                        'description' => 'Replaced sequential message IDs with unique UUID identifiers to eliminate data exposure risk.',
                        'component'   => 'webapp',
                    ],
                    [
                        'version_id'  => $versionId,
                        'category'    => 'feature',
                        'title'       => 'PDF Uploads & Sniffing Validation',
                        'description' => 'Allowed PDF document sharing with dynamic icon layout and binary MIME header validation to prevent shell executions.',
                        'component'   => 'webapp',
                    ],
                ]);
            }
        }

        // 3. Insert new version into db_versions
        if ($db->tableExists('db_versions')) {
            $db->table('db_versions')->ignore(true)->insert([
                'version'      => '2.5.1',
                'build_number' => 20501,
                'release_name' => 'Private File Gateway, UUID Obfuscation & PDF Uploads',
                'is_current'   => 1,
                'applied_at'   => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if ($db->tableExists('system_versions')) {
            // Find v2.5.1 row ID to delete changelogs first
            $row = $db->table('system_versions')->where('version', '2.5.1')->get()->getRowArray();
            if ($row) {
                if ($db->tableExists('system_changelogs')) {
                    $db->table('system_changelogs')->where('version_id', $row['id'])->delete();
                }
            }
            $db->table('system_versions')->where('version', '2.5.1')->delete();
        }

        if ($db->tableExists('db_versions')) {
            $db->table('db_versions')->where('version', '2.5.1')->delete();
        }

        // Restore latest remaining version as current
        if ($db->tableExists('system_versions')) {
            $latest = $db->table('system_versions')->orderBy('id', 'DESC')->limit(1)->get()->getRowArray();
            if ($latest) {
                $db->table('system_versions')->where('id', $latest['id'])->update(['is_current' => 1]);
            }
        }
        if ($db->tableExists('db_versions')) {
            $latest = $db->table('db_versions')->orderBy('id', 'DESC')->limit(1)->get()->getRowArray();
            if ($latest) {
                $db->table('db_versions')->where('id', $latest['id'])->update(['is_current' => 1]);
            }
        }
    }
}

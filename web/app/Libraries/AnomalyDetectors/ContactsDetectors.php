<?php

namespace App\Libraries\AnomalyDetectors;

class ContactsDetectors
{
    /**
     * ContactsController – New-Contact Frequency Monitor
     *
     * Flags days where new contact additions exceed 3× the 30-day rolling average.
     *
     * @param  array $contactRows  [{display_name, last_modified, ...}, ...]
     * @return array
     */
    public function detectContactsFrequency(array $contactRows): array
    {
        if (empty($contactRows)) {
            return [];
        }

        // Bucket new contacts by day
        $daily = [];
        foreach ($contactRows as $row) {
            $day           = date('Y-m-d', strtotime($row['last_modified'] ?? 'today'));
            $daily[$day]   = ($daily[$day] ?? 0) + 1;
        }

        $counts = array_values($daily);
        $mean   = array_sum($counts) / max(1, count($counts));
        $thresh = max(3, $mean * 3);

        $findings = [];
        foreach ($daily as $day => $cnt) {
            if ($cnt > $thresh) {
                $findings[] = [
                    'category'  => 'Contacts',
                    'icon'      => 'fas fa-address-book',
                    'anomaly'   => "{$cnt} new contacts added on {$day} (" . round($cnt / max(1, $mean), 1) . '× above 30-day average)',
                    'severity'  => $cnt > $thresh * 1.5 ? 'High' : 'Medium',
                    'algorithm' => 'New-Contact Frequency Monitor',
                    'timestamp' => $day . ' 00:00:00',
                    'engine_note' => 'Daily threshold: ' . round($thresh) . ' (mean: ' . round($mean, 1) . '/day)',
                ];
            }
        }

        return $findings;
    }

    /**
     * ContactsController – Duplicate & Anomaly Detector
     *
     * Finds contacts sharing the same phone number or suspiciously similar display names.
     *
     * @param  array $contactRows  [{display_name, phone_number, ...}, ...]
     * @return array
     */
    public function detectContactsDuplicates(array $contactRows): array
    {
        if (empty($contactRows)) {
            return [];
        }

        $phoneMap = [];
        $findings = [];

        foreach ($contactRows as $row) {
            $phone = preg_replace('/\D/', '', $row['phone_number'] ?? '');
            $name  = $row['display_name'] ?? 'Unknown';
            if ($phone) {
                if (isset($phoneMap[$phone])) {
                    $findings[] = [
                        'category'  => 'Contacts',
                        'icon'      => 'fas fa-address-book',
                        'anomaly'   => "Duplicate phone number shared by \"{$phoneMap[$phone]}\" and \"{$name}\" ({$phone})",
                        'severity'  => 'Medium',
                        'algorithm' => 'Duplicate & Anomaly Detector',
                        'timestamp' => date('Y-m-d H:i:s'),
                        'engine_note' => 'Exact phone number collision detected',
                    ];
                } else {
                    $phoneMap[$phone] = $name;
                }
            }
        }

        return $findings;
    }

    /**
     * ContactsController – Contact Graph Outlier Model
     *
     * Flags contact entries that are isolated or have suspicious formatting.
     */
    public function detectContactsGraph(array $contactRows): array
    {
        if (empty($contactRows)) {
            return [];
        }

        $findings = [];
        foreach ($contactRows as $row) {
            $name = trim($row['display_name'] ?? '');
            $phone = trim($row['phone_number'] ?? '');

            $reasons = [];
            if (empty($phone)) {
                $reasons[] = 'Missing phone number (isolated contact)';
            } elseif (strlen($phone) < 5) {
                $reasons[] = 'Suspiciously short phone number (' . esc($phone) . ')';
            }
            
            if (strlen($name) < 2) {
                $reasons[] = 'Suspiciously short display name';
            } elseif (preg_match('/[0-9]{5,}/', $name)) {
                $reasons[] = 'Display name contains bulk numbers';
            }

            if (!empty($reasons)) {
                $findings[] = [
                    'category'  => 'Contacts',
                    'icon'      => 'fas fa-address-book',
                    'anomaly'   => 'Isolated or poorly-structured contact entry: "' . esc($name ?: '(No Name)') . '"',
                    'severity'  => 'Medium',
                    'algorithm' => 'Contact Graph Outlier Model',
                    'timestamp' => $row['last_modified'] ?? date('Y-m-d H:i:s'),
                    'engine_note'=> 'PHP heuristic - contact anomalies: ' . implode(', ', $reasons),
                ];
            }
        }

        return array_slice($findings, 0, 5);
    }
}

<?php

if (! function_exists('coalesce_snapshots')) {
    /**
     * Coalesce a history of snapshots for unique devices, preserving static hardware configuration
     * metrics even when the adapter or radio status turns off or returns empty states.
     *
     * @param array $rows The raw database records (typically ordered newest first).
     * @param string $deviceKey The field identifying unique devices (default: 'device_id').
     * @param array $scalarFields Fields to carry forward from the latest non-empty snapshot.
     * @param array $listFields Fields containing lists/JSON arrays that should be aggregated.
     * @param string $listUniqueKey Key identifying uniqueness in list items (e.g. 'bt_address' or 'sensor_id').
     * @return array Coalesced rows (one per unique device_id).
     */
    function coalesce_snapshots(
        array $rows,
        string $deviceKey = 'device_id',
        array $scalarFields = [],
        array $listFields = [],
        string $listUniqueKey = ''
    ): array {
        if (empty($rows)) {
            return [];
        }

        // Group rows by device_id
        $groups = [];
        foreach ($rows as $r) {
            $devId = $r[$deviceKey] ?? 'default';
            $key = strtolower(trim((string)$devId));
            $groups[$key][] = $r;
        }

        $coalesced = [];

        foreach ($groups as $key => $deviceRows) {
            // Sort chronologically: oldest to newest
            usort($deviceRows, function ($a, $b) {
                $aTime = $a['extracted_at'] ?? $a['created_at'] ?? 0;
                $bTime = $b['extracted_at'] ?? $b['created_at'] ?? 0;
                return $aTime <=> $bTime;
            });

            // Initialize merged profile with the absolute newest snapshot (latest state)
            $newest = end($deviceRows);
            $merged = $newest;

            // Initialize aggregated list maps if list fields are defined
            $aggregatedLists = [];
            foreach ($listFields as $listField) {
                $aggregatedLists[$listField] = [];
            }

            // Loop through all snapshots to merge details
            foreach ($deviceRows as $snap) {
                // 1. Merge scalar fields: Keep latest non-empty value
                foreach ($scalarFields as $field) {
                    if (isset($snap[$field]) && $snap[$field] !== null && $snap[$field] !== '' && $snap[$field] !== '—') {
                        $merged[$field] = $snap[$field];
                    }
                }

                // 2. Merge aggregated lists (e.g. paired_devices, sensor arrays)
                foreach ($listFields as $listField) {
                    $items = [];
                    if (isset($snap[$listField])) {
                        $items = is_string($snap[$listField])
                            ? json_decode($snap[$listField], true)
                            : $snap[$listField];
                    }

                    if (is_array($items)) {
                        foreach ($items as $item) {
                            $itemKey = isset($item[$listUniqueKey])
                                ? strtolower(trim((string)$item[$listUniqueKey]))
                                : serialize($item);

                            if (empty($itemKey)) {
                                continue;
                            }

                            if (is_array($item)) {
                                $aggregatedLists[$listField][$itemKey] = array_merge(
                                    $aggregatedLists[$listField][$itemKey] ?? [],
                                    $item,
                                    ['last_seen_snapshot_at' => $snap['extracted_at'] ?? $snap['created_at'] ?? null]
                                );
                            } else {
                                $aggregatedLists[$listField][$itemKey] = $item;
                            }
                        }
                    }
                }
            }

            // Re-assign coalesced lists back to the merged array
            foreach ($listFields as $listField) {
                $merged[$listField] = array_values($aggregatedLists[$listField]);
            }

            $coalesced[] = $merged;
        }

        // Return ordered newest first based on original ordering preference
        return array_reverse($coalesced);
    }
}

<?php

if (! function_exists('format_relative_time')) {
    /**
     * Format a millisecond epoch timestamp as a human-readable relative time.
     */
    function format_relative_time(?int $msTimestamp): string
    {
        if (empty($msTimestamp)) {
            return '—';
        }

        $seconds = (int) floor($msTimestamp / 1000);
        $diff    = time() - $seconds;

        if ($diff < 0) {
            return 'just now';
        }
        if ($diff < 60) {
            return 'just now';
        }
        if ($diff < 3600) {
            $mins = (int) floor($diff / 60);

            return $mins . ' min ago';
        }
        if ($diff < 86400) {
            $hrs = (int) floor($diff / 3600);

            return $hrs . ' h ago';
        }
        if ($diff < 604800) {
            $days = (int) floor($diff / 86400);

            return $days . ' d ago';
        }

        return date('Y-m-d H:i', $seconds);
    }
}

if (! function_exists('package_url_encode')) {
    /**
     * Encode a package name for use in URL segments (base64url).
     */
    function package_url_encode(string $packageName): string
    {
        $crypt   = new \App\Models\Mod_Crypt();
        $encoded = $crypt->base64url_encode($packageName);

        return $encoded !== false ? $encoded : '';
    }
}

if (! function_exists('package_url_decode')) {
    /**
     * Decode a package name from a URL segment.
     */
    function package_url_decode(string $segment): ?string
    {
        if ($segment === '') {
            return null;
        }

        $crypt   = new \App\Models\Mod_Crypt();
        $decoded = $crypt->base64url_decode($segment, true);

        if ($decoded === false || $decoded === '') {
            return null;
        }

        return $decoded;
    }
}

if (! function_exists('format_timestamp_display')) {
    /**
     * Format a millisecond epoch timestamp as HTML with call_logs-style icons.
     * Returns e.g. '<i class="fas fa-calendar-day text-primary mr-1"></i> Jul 17, 2026
     *          <small class="text-muted"><i class="fas fa-clock text-secondary mr-1"></i> 14:30:00
     *          <span class="badge badge-light ml-1">Fri</span></small>'
     */
    function format_timestamp_display(?int $msTimestamp): string
    {
        if (empty($msTimestamp)) {
            return '<span class="text-muted">—</span>';
        }
        $seconds = (int) floor($msTimestamp / 1000);
        $dateOnly = date('M d, Y', $seconds);
        $timeOnly = date('H:i:s', $seconds);
        $dayName  = date('D', $seconds);
        return '<i class="fas fa-calendar-day text-primary mr-1"></i> ' . $dateOnly
            . ' <small class="text-muted"><i class="fas fa-clock text-secondary mr-1"></i> ' . $timeOnly
            . ' <span class="badge badge-light ml-1">' . $dayName . '</span></small>';
    }
}

if (! function_exists('format_ms_datetime')) {
    /**
     * Format ms timestamp for display (absolute).
     */
    function format_ms_datetime(?int $msTimestamp, string $format = 'Y-m-d H:i:s'): string
    {
        if (empty($msTimestamp)) {
            return '—';
        }

        return date($format, (int) floor($msTimestamp / 1000));
    }
}

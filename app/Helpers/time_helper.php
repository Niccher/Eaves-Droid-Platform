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

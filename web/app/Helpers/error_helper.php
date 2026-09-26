<?php

if (!function_exists('error_page')) {
    /**
     * Loads an error page with proper layout.
     *
     * @param string $errorCode Error code (403, 404, 500, 503)
     * @param string $message Custom error message
     * @param array $data Additional data to pass to view
     * @return string
     */
    function error_page(string $errorCode = '500', string $message = '', array $data = []): string
    {
        $errorPages = [
            '403' => 'errors/custom_errors/error_403',
            '404' => 'errors/custom_errors/error_404',
            '500' => 'errors/custom_errors/error_500',
            '503' => 'errors/custom_errors/error_503',
        ];

        $view = $errorPages[$errorCode] ?? 'errors/custom_errors/error_general';

        $defaultData = [
            'error_code' => $errorCode,
            'error_message' => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'current_url' => current_url(),
        ];

        return view($view, array_merge($defaultData, $data));
    }
}

if (!function_exists('get_notification_sender')) {
    /**
     * Returns the notification sender email and name from admin settings.
     *
     * @return array{email: string, name: string}
     */
    function get_notification_sender(): array
    {
        $db = \Config\Database::connect();
        $settings = [];
        $rows = $db->table('settings')->where('class', 'notification')->get()->getResultArray();
        foreach ($rows as $r) {
            $settings[$r['key']] = $r['value'];
        }
        return [
            'email' => $settings['smtp_from_email'] ?? config('Email')->fromEmail ?? 'prjs4@chegecache.co.ke',
            'name'  => $settings['smtp_from_name'] ?? config('Email')->fromName ?? 'Eaves Droid',
        ];
    }
}

if (!function_exists('throw_custom_error')) {
    /**
     * Throws a custom HTTP exception with error page.
     *
     * @param int $code HTTP status code
     * @param string $message Error message
     * @throws \CodeIgniter\HTTP\Exceptions\HTTPException
     */
    function throw_custom_error(int $code = 500, string $message = '')
    {
        throw \CodeIgniter\HTTP\Exceptions\HTTPException::forError($code, $message);
    }
}
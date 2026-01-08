<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =================================================================
// 1. SITE WIDE ROUTES & CONFIGURATION
// =================================================================

// Set default namespace for all routes
$routes->setDefaultNamespace('App\Controllers');

// Set 404 Override
$routes->set404Override(function() {
    return redirect()->to('/error_404');
});

// Set Translate URI Dashes
$routes->setTranslateURIDashes(false);

// Set Auto Route (disabled for security)
$routes->setAutoRoute(false);

// =================================================================
// 2. PUBLIC ROUTES (Landing Pages)
//    Unauthenticated access only
// =================================================================

$routes->group('', ['namespace' => 'App\Controllers\Home'], static function ($routes) {
    // Base URL & Landing
    $routes->get('/', 'Home::index', ['as' => 'home']);
    $routes->get('landing', 'Home::index', ['as' => 'landing']);

    // Information Pages
    $routes->get('download', 'Home::landing_download', ['as' => 'download']);
    $routes->get('aboutus', 'Home::landing_aboutus', ['as' => 'about']);
    $routes->get('faqs_terms', 'Home::landing_faqs', ['as' => 'faqs']);
    $routes->get('how_to', 'Home::landing_how_to', ['as' => 'how-to']);
    $routes->get('prices', 'Home::landing_prices', ['as' => 'pricing']);
    $routes->get('error_404', 'Home::landing_error_404', ['as' => 'error-404']);

    // Contact Form (GET for view, POST for submission)
    $routes->match(['get', 'post'], 'contactus', 'Home::landing_contactus', ['as' => 'contact']);
});

// =================================================================
// 3. AUTHENTICATION ROUTES (CodeIgniter Shield)
//    Custom authentication controllers override Shield defaults
// =================================================================

// Load Shield routes first (for password reset, email verification, etc.)
service('auth')->routes($routes);

// Custom Authentication Routes (override Shield defaults)
$routes->group('', ['namespace' => 'App\Controllers\Auth'], static function ($routes) {
    // Login
    $routes->get('login', 'LoginController::loginView', ['as' => 'login']);
    $routes->post('login', 'LoginController::loginAction');
    $routes->get('logout', 'LoginController::logoutAction', ['as' => 'logout']);

    // Registration
    $routes->get('register', 'RegisterController::registerView', ['as' => 'register']);
    $routes->post('register', 'RegisterController::registerAction');

    // Forgot Password
    $routes->get('forgot', 'ForgotPasswordController::forgotView', ['as' => 'forgot-password']);
    $routes->post('forgot', 'ForgotPasswordController::forgotAction');

    // Reset Password
    $routes->get('reset-password', 'ForgotPasswordController::resetView', ['as' => 'reset-password']);
    $routes->post('reset-password', 'ForgotPasswordController::resetAction');
});

// =================================================================
// 4. PROTECTED CLIENT/DASHBOARD ROUTES
//    All routes require authentication (session filter)
// =================================================================

$routes->group('', [
    'namespace' => 'App\Controllers\clients',
    'filter'    => 'session'  // Requires authentication
], static function ($routes) {

    // =============================================================
    // 4.1 DASHBOARD & GENERAL PAGES
    // =============================================================

    // Main Dashboard
    $routes->get('home', 'Client::home', ['as' => 'client-dashboard']);

    // FAQ Page (Client Version)
    $routes->get('faqs', 'Client::faqs', ['as' => 'client-faqs']);

    // =============================================================
    // 4.2 DATA VIEWS - APPS
    // =============================================================

    $routes->group('apps', static function ($routes) {
        // Main apps listing with pagination
        $routes->get('/', 'Apps::apps', ['as' => 'apps-all']);
        $routes->get('(:num)', 'Apps::apps/$1');

        // Filtered views with pagination
        $routes->get('unique', 'Apps::apps_unique', ['as' => 'apps-unique']);
        $routes->get('unique/(:num)', 'Apps::apps_unique/$1');

        $routes->get('last_time', 'Apps::apps_last_time', ['as' => 'apps-recent']);
        $routes->get('last_time/(:num)', 'Apps::apps_last_time/$1');

        $routes->get('all_apps', 'Apps::apps_all', ['as' => 'apps-complete']);
        $routes->get('all_apps/(:num)', 'Apps::apps_all/$1');

        // Categorized views (using BaseClientController navigation)
        $routes->get('system', 'Apps::apps_system', ['as' => 'apps-system']);
        $routes->get('system/(:num)', 'Apps::apps_system/$1');

        $routes->get('user', 'Apps::apps_user', ['as' => 'apps-user']);
        $routes->get('user/(:num)', 'Apps::apps_user/$1');

        $routes->get('disabled', 'Apps::apps_disabled', ['as' => 'apps-disabled']);
        $routes->get('disabled/(:num)', 'Apps::apps_disabled/$1');
    });

    // =============================================================
    // 4.3 DATA VIEWS - CALL LOGS
    // =============================================================

    $routes->group('call_logs', static function ($routes) {
        // Main call logs listing with pagination
        $routes->get('/', 'Calls::call_logs', ['as' => 'call-logs-all']);
        $routes->get('(:num)', 'Calls::call_logs/$1');

        // Filtered views with pagination
        $routes->get('incoming', 'Calls::call_incoming', ['as' => 'call-logs-incoming']);
        $routes->get('incoming/(:num)', 'Calls::call_incoming/$1');

        $routes->get('outgoing', 'Calls::call_outgoing', ['as' => 'call-logs-outgoing']);
        $routes->get('outgoing/(:num)', 'Calls::call_outgoing/$1');

        $routes->get('rejected', 'Calls::call_rejected', ['as' => 'call-logs-rejected']);
        $routes->get('rejected/(:num)', 'Calls::call_rejected/$1');

        $routes->get('blocked', 'Calls::call_blocked', ['as' => 'call-logs-blocked']);
        $routes->get('blocked/(:num)', 'Calls::call_blocked/$1');
    });

    // =============================================================
    // 4.4 DATA VIEWS - SMS
    // =============================================================

    $routes->group('sms', static function ($routes) {
        // Main SMS listing with pagination
        $routes->get('/', 'Sms::sms', ['as' => 'sms-all']);
        $routes->get('(:num)', 'Sms::sms/$1');

        // Filtered views with pagination
        $routes->get('inbox', 'Sms::sms_inbox', ['as' => 'sms-inbox']);
        $routes->get('inbox/(:num)', 'Sms::sms_inbox/$1');

        $routes->get('sent', 'Sms::sms_sent', ['as' => 'sms-sent']);
        $routes->get('sent/(:num)', 'Sms::sms_sent/$1');
    });

    // =============================================================
    // 4.5 DATA VIEWS - CONTACTS
    // =============================================================

    $routes->group('contacts', static function ($routes) {
        // Main contacts listing with pagination
        $routes->get('/', 'Contacts::index', ['as' => 'contacts-all']);
        $routes->get('(:num)', 'Contacts::index/$1');

        // Contact analysis (dynamic routes)
        $routes->get('analyze/sms/(:any)', 'Analyze::sms/$1', ['as' => 'contact-analyze-sms']);
        $routes->get('analyze/sms/(:any)/(:num)', 'Analyze::sms/$1/$2');

        $routes->get('analyze/calls/(:any)', 'Analyze::calls/$1', ['as' => 'contact-analyze-calls']);
        $routes->get('analyze/calls/(:any)/(:num)', 'Analyze::calls/$1/$2');
    });

    // =============================================================
    // 4.6 ANALYSIS & CORRELATION ROUTES
    // =============================================================

    $routes->group('analysis', static function ($routes) {
        // Main analysis dashboard
        $routes->get('/', 'Correlation::index', ['as' => 'analysis-dashboard']);
        $routes->get('(:num)', 'Correlation::index/$1');

        // Financial SMS analysis
        $routes->get('sms/finance', 'Correlation::sms_finance', ['as' => 'analysis-sms-finance']);
        $routes->get('sms/finance/(:num)', 'Correlation::sms_finance/$1');

        $routes->get('sms/finance/(:any)', 'Correlation::sms_analyze_finance_from/$1');
        $routes->get('sms/finance/(:any)/(:num)', 'Correlation::sms_analyze_finance_from/$1/$2');

        // SMS rules configuration
        $routes->get('set_rules', 'Correlation::set_sms_rules', ['as' => 'analysis-set-rules']);
        $routes->get('set_rules/(:num)', 'Correlation::set_sms_rules/$1');

        // SMS datapoints configuration (POST only)
        $routes->post('set/set_sms_datapoints/(:any)', 'Correlation::set_sms_datapoints/$1');
    });

    // =============================================================
    // 4.7 ACCOUNT MANAGEMENT ROUTES (UPDATED)
    // =============================================================

    $routes->group('account', static function ($routes) {
        // ---------------------------------------------------------
        // PROFILE MANAGEMENT
        // ---------------------------------------------------------

        // Profile Home (Main Profile Page)
        $routes->get('home', 'Account::home', ['as' => 'account-profile']);
        $routes->get('profile', 'Account::home'); // Legacy alias

        // Profile Update Actions (AJAX/POST)
        $routes->post('updateProfile', 'Account::updateProfile', ['as' => 'account-update-profile']);
        $routes->post('uploadImage', 'Account::uploadImage', ['as' => 'account-upload-image']);

        // ---------------------------------------------------------
        // SETTINGS & TOKEN MANAGEMENT
        // ---------------------------------------------------------

        // Settings Page
        $routes->get('setting', 'Account::setting', ['as' => 'account-settings']);

        // Token Management
        $routes->post('regenerateToken', 'Account::regenerateToken', ['as' => 'account-regenerate-token']);
        $routes->post('revokeToken', 'Account::revokeToken', ['as' => 'account-revoke-token']);
        $routes->get('tokens', 'Account::tokens', ['as' => 'account-tokens']);

        // ---------------------------------------------------------
        // ACCESS LOGS & SECURITY
        // ---------------------------------------------------------

        // Access Logs with Tabbed Navigation
        $routes->get('access_logs', 'Account::access_logs', ['as' => 'account-access-logs']);
        $routes->get('access_logs/(:any)', 'Account::access_logs/$1'); // web, android, all

        // Security Settings
        $routes->get('security', 'Account::security', ['as' => 'account-security']);
        $routes->post('updateSecurity', 'Account::updateSecurity', ['as' => 'account-update-security']);

        // Devices & Sessions
        $routes->get('devices', 'Account::devices', ['as' => 'account-devices']);
        $routes->get('sessions', 'Account::sessions', ['as' => 'account-sessions']);
        $routes->post('terminateSession/(:any)', 'Account::terminateSession/$1', ['as' => 'account-terminate-session']);

        // ---------------------------------------------------------
        // DATA EXPORT & MANAGEMENT
        // ---------------------------------------------------------

        // Data Export (GET downloads)
        $routes->get('exportData/(:any)', 'Account::exportData/$1', ['as' => 'account-export-data']);

        // Data Deletion (GET for confirmation, POST for action)
        $routes->get('deleteData/(:any)', 'Account::deleteData/$1', ['as' => 'account-delete-data-confirm']);
        $routes->post('deleteData/(:any)', 'Account::deleteData/$1', ['as' => 'account-delete-data']);

        // Data Statistics
        $routes->get('stats', 'Account::stats', ['as' => 'account-stats']);

        // ---------------------------------------------------------
        // LEGACY ROUTES (For backward compatibility)
        // ---------------------------------------------------------

        // Legacy Profile Routes
        $routes->match(['get', 'post'], 'profile/update', 'Profile::profile_update');
        $routes->match(['get', 'post'], 'profile/image_upload', 'Profile::profile_upload');
        $routes->post('setting/token_generate', 'Profile::token_generate');

        // Legacy Data Deletion Routes (POST only)
        $routes->post('data/del/apps', 'Profile::profile_del_apps');
        $routes->post('data/del/call_logs', 'Profile::profile_del_call_logs');
        $routes->post('data/del/contacts', 'Profile::profile_del_contacts');
        $routes->post('data/del/sms', 'Profile::profile_del_sms');

        // Legacy Access Logs Alias
        $routes->get('logs', 'Account::access_logs');
    });

    // =============================================================
    // 4.8 OTHER CLIENT ROUTES
    // =============================================================

    // Support Requests
    $routes->match(['get', 'post'], 'account/requests', 'Requests::send_request', ['as' => 'client-requests']);
    $routes->match(['get', 'post'], 'account/requests/sleep', 'Requests::send_sleep', ['as' => 'client-sleep-request']);
});

// =================================================================
// 5. API ROUTES (Mobile & External Integration)
//    Requires API authentication (tokens filter)
// =================================================================

$routes->group('api/v1', [
    'namespace' => 'App\Controllers\api\v1',
    'filter'    => 'throttle:api'  // Rate limiting
], static function ($routes) {

    // -------------------------------------------------------------
    // 5.1 AUTHENTICATION & TOKEN VERIFICATION
    // -------------------------------------------------------------

    // Token verification for mobile devices
    $routes->post('token/verify', 'Receive::token_verify', ['as' => 'api-token-verify']);

    // -------------------------------------------------------------
    // 5.2 DEVICE REGISTRATION & MANAGEMENT
    // -------------------------------------------------------------

    // Device fingerprint registration
    $routes->post('device/print', 'Receive::device_print', ['as' => 'api-device-print']);

    // Device status check
    $routes->get('device/status', 'Receive::device_status', ['as' => 'api-device-status']);

    // -------------------------------------------------------------
    // 5.3 DATA UPLOAD ENDPOINTS
    // -------------------------------------------------------------

    // Bulk file upload (encrypted/text files)
    $routes->post('files/upload', 'Receive::upload', ['as' => 'api-files-upload']);

    // Individual data type uploads
    $routes->post('data/sms', 'Receive::upload_sms', ['as' => 'api-data-sms']);
    $routes->post('data/calls', 'Receive::upload_calls', ['as' => 'api-data-calls']);
    $routes->post('data/contacts', 'Receive::upload_contacts', ['as' => 'api-data-contacts']);
    $routes->post('data/apps', 'Receive::upload_apps', ['as' => 'api-data-apps']);

    // -------------------------------------------------------------
    // 5.4 DATA RETRIEVAL ENDPOINTS (Read-only)
    // -------------------------------------------------------------

    // Account information
    $routes->get('account/info', 'Receive::account_info', ['as' => 'api-account-info']);

    // Configuration data
    $routes->get('config', 'Receive::config', ['as' => 'api-config']);

    // -------------------------------------------------------------
    // 5.5 UTILITY & HEALTH CHECK ENDPOINTS
    // -------------------------------------------------------------

    // API health check
    $routes->get('health', 'Receive::health', ['as' => 'api-health']);

    // Server time synchronization
    $routes->get('time', 'Receive::server_time', ['as' => 'api-server-time']);

    // App version check
    $routes->get('version', 'Receive::version_check', ['as' => 'api-version-check']);
});

// =================================================================
// 6. ADMIN ROUTES (Administrative Interface)
//    Requires admin role and authentication
// =================================================================

$routes->group('admin', [
    'namespace' => 'App\Controllers\admin',
    'filter'    => ['session', 'role:admin']  // Auth + Admin role
], static function ($routes) {

    // -------------------------------------------------------------
    // 6.1 ADMIN DASHBOARD
    // -------------------------------------------------------------

    $routes->get('dashboard', 'Dashboard::index', ['as' => 'admin-dashboard']);
    $routes->get('overview', 'Dashboard::overview', ['as' => 'admin-overview']);

    // -------------------------------------------------------------
    // 6.2 USER MANAGEMENT
    // -------------------------------------------------------------

    $routes->group('users', static function ($routes) {
        $routes->get('/', 'Users::index', ['as' => 'admin-users']);
        $routes->get('(:num)', 'Users::index/$1');

        // User CRUD operations
        $routes->get('create', 'Users::create', ['as' => 'admin-user-create']);
        $routes->post('store', 'Users::store', ['as' => 'admin-user-store']);
        $routes->get('edit/(:num)', 'Users::edit/$1', ['as' => 'admin-user-edit']);
        $routes->post('update/(:num)', 'Users::update/$1', ['as' => 'admin-user-update']);
        $routes->post('delete/(:num)', 'Users::delete/$1', ['as' => 'admin-user-delete']);

        // User status management
        $routes->post('suspend/(:num)', 'Users::suspend/$1', ['as' => 'admin-user-suspend']);
        $routes->post('activate/(:num)', 'Users::activate/$1', ['as' => 'admin-user-activate']);

        // User data management
        $routes->get('data/(:num)', 'Users::user_data/$1', ['as' => 'admin-user-data']);
        $routes->post('clearData/(:num)', 'Users::clear_user_data/$1', ['as' => 'admin-user-clear-data']);
    });

    // -------------------------------------------------------------
    // 6.3 SYSTEM LOGS & MONITORING
    // -------------------------------------------------------------

    $routes->group('logs', static function ($routes) {
        $routes->get('/', 'Logs::index', ['as' => 'admin-logs']);
        $routes->get('(:num)', 'Logs::index/$1');

        // Filtered logs
        $routes->get('access', 'Logs::access_logs', ['as' => 'admin-access-logs']);
        $routes->get('access/(:num)', 'Logs::access_logs/$1');

        $routes->get('errors', 'Logs::error_logs', ['as' => 'admin-error-logs']);
        $routes->get('errors/(:num)', 'Logs::error_logs/$1');

        $routes->get('api', 'Logs::api_logs', ['as' => 'admin-api-logs']);
        $routes->get('api/(:num)', 'Logs::api_logs/$1');

        // Log management
        $routes->post('clear', 'Logs::clear_logs', ['as' => 'admin-logs-clear']);
        $routes->post('export', 'Logs::export_logs', ['as' => 'admin-logs-export']);
    });

    // -------------------------------------------------------------
    // 6.4 SYSTEM SETTINGS & CONFIGURATION
    // -------------------------------------------------------------

    $routes->group('settings', static function ($routes) {
        $routes->get('/', 'Settings::index', ['as' => 'admin-settings']);
        $routes->post('update', 'Settings::update', ['as' => 'admin-settings-update']);

        // Module-specific settings
        $routes->get('api', 'Settings::api_settings', ['as' => 'admin-settings-api']);
        $routes->get('security', 'Settings::security_settings', ['as' => 'admin-settings-security']);
        $routes->get('notifications', 'Settings::notification_settings', ['as' => 'admin-settings-notifications']);

        // System maintenance
        $routes->get('maintenance', 'Settings::maintenance', ['as' => 'admin-maintenance']);
        $routes->post('maintenance/run', 'Settings::run_maintenance', ['as' => 'admin-run-maintenance']);

        // Backup & restore
        $routes->get('backup', 'Settings::backup', ['as' => 'admin-backup']);
        $routes->post('backup/create', 'Settings::create_backup', ['as' => 'admin-create-backup']);
        $routes->post('backup/restore', 'Settings::restore_backup', ['as' => 'admin-restore-backup']);
    });

    // -------------------------------------------------------------
    // 6.5 REPORTS & ANALYTICS
    // -------------------------------------------------------------

    $routes->group('reports', static function ($routes) {
        $routes->get('/', 'Reports::index', ['as' => 'admin-reports']);

        // User activity reports
        $routes->get('user-activity', 'Reports::user_activity', ['as' => 'admin-reports-user-activity']);
        $routes->get('user-activity/(:any)', 'Reports::user_activity_report/$1');

        // Data usage reports
        $routes->get('data-usage', 'Reports::data_usage', ['as' => 'admin-reports-data-usage']);
        $routes->get('data-usage/(:any)', 'Reports::data_usage_report/$1');

        // System performance reports
        $routes->get('performance', 'Reports::performance', ['as' => 'admin-reports-performance']);

        // Generate custom reports
        $routes->match(['get', 'post'], 'generate', 'Reports::generate', ['as' => 'admin-reports-generate']);

        // Export reports
        $routes->post('export', 'Reports::export', ['as' => 'admin-reports-export']);
    });

    // -------------------------------------------------------------
    // 6.6 TOKEN MANAGEMENT (Admin View)
    // -------------------------------------------------------------

    $routes->group('tokens', static function ($routes) {
        $routes->get('/', 'Tokens::index', ['as' => 'admin-tokens']);
        $routes->get('(:num)', 'Tokens::index/$1');

        // Token management
        $routes->post('revoke/(:num)', 'Tokens::revoke/$1', ['as' => 'admin-token-revoke']);
        $routes->post('regenerate/(:num)', 'Tokens::regenerate/$1', ['as' => 'admin-token-regenerate']);
        $routes->post('delete/(:num)', 'Tokens::delete/$1', ['as' => 'admin-token-delete']);

        // Token analytics
        $routes->get('analytics', 'Tokens::analytics', ['as' => 'admin-token-analytics']);
        $routes->get('expired', 'Tokens::expired', ['as' => 'admin-tokens-expired']);
    });
});

// =================================================================
// 7. UTILITY & SYSTEM ROUTES
// =================================================================

// Health check endpoint (no authentication required)
$routes->get('health-check', function() {
    return service('response')->setJSON([
        'status' => 'online',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '1.0.0',
        'environment' => ENVIRONMENT
    ]);
});

// Server status (minimal information)
$routes->get('server-status', function() {
    $data = [
        'server_time' => date('Y-m-d H:i:s'),
        'timezone' => date_default_timezone_get(),
        'php_version' => PHP_VERSION,
        'memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB',
        'memory_limit' => ini_get('memory_limit')
    ];

    return service('response')->setJSON($data);
});

// =================================================================
// 8. DEVELOPMENT ROUTES (Environment specific)
// =================================================================

if (ENVIRONMENT === 'development') {
    $routes->get('dev/debug', function() {
        if (!function_exists('auth')) {
            echo "Auth helper not loaded";
            return;
        }

        $data = [
            'logged_in' => auth()->loggedIn(),
            'user_id' => auth()->id(),
            'user_email' => auth()->loggedIn() ? auth()->user()->getEmail() : null,
            'session_data' => session()->get()
        ];

        return service('response')->setJSON($data);
    });

    $routes->get('dev/routes', function() {
        $router = service('router');
        $routes = service('routes');

        $data = [];
        foreach ($routes->getRoutes() as $method => $routeCollection) {
            foreach ($routeCollection as $route => $handler) {
                $data[] = [
                    'method' => $method,
                    'route' => $route,
                    'handler' => $handler
                ];
            }
        }

        return service('response')->setJSON($data);
    });
}

// =================================================================
// 9. CATCH-ALL ROUTE (Must be last)
// =================================================================

// Any other route not matched above goes to 404
$routes->get('(:any)', function() {
    return redirect()->to('/error_404');
});

// =================================================================
// END OF ROUTES
// =================================================================
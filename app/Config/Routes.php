<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

service('auth')->routes($routes);

$routes->group('', ['namespace' => 'App\Controllers'], static function ($routes) {
    $routes->get('login', 'auth\LoginController::loginView');
    $routes->post('login', 'auth\LoginController::loginAction');
    $routes->get('logout', 'auth\LoginController::logoutAction');

    $routes->get('register', 'auth\RegisterController::registerView');
    $routes->post('register', 'Aauth\RegisterController::registerAction');

    $routes->get('forgot', 'auth\ForgotPasswordController::forgotView');
    $routes->post('forgot', 'auth\ForgotPasswordController::forgotAction');

    $routes->get('reset-password', 'auth\ForgotPasswordController::resetView');
    $routes->post('reset-password', 'auth\ForgotPasswordController::resetAction');

    // Dashboard route (protected)
//    $routes->get('dashboard', 'DashboardController::index', ['filter' => 'session']);
});

//// If you want to keep the Shield controllers for other actions
//$routes->group('', ['namespace' => 'CodeIgniter\Shield\Controllers'], static function ($routes) {
//    $routes->get('auth/a/show', 'ActionController::show');
//    $routes->post('auth/a/handle', 'ActionController::handle');
//    $routes->get('auth/a/verify', 'ActionController::verify');
//});

$routes->get('landing','Home::index');
$routes->get('download', 'Home::landing_download');
$routes->get('aboutus','Home::landing_aboutus');
$routes->get('contactus', 'Home::landing_contactus');
$routes->get('prices', 'Home::landing_prices');
$routes->get('faqs_terms', 'Home::landing_faqs');
$routes->get('how_to', 'Home::landing_how_to');
$routes->get('error_404', 'Home::landing_error_404');

$routes->get('home', 'clients\Client::home');
$routes->get('apps', 'clients\Apps::apps');
$routes->get('apps/unique', 'clients\Apps::apps');
$routes->get('apps/last_time', 'clients\Apps::apps_last_time');
$routes->get('apps/all_apps', 'clients\Apps::apps');
$routes->get('faqs', 'clients\Client::faqs');

$routes->get('analysis', 'clients\Correlation::index');
$routes->get('analysis/sms/finance', 'clients\Correlation::sms_finance');
$routes->get('analysis/sms/finance/(:any)', 'clients\Correlation::sms_analyze_finance_from/$1');


$routes->get('analysis/set_rules', 'clients\Correlation::set_sms_rules');
$routes->post('analysis/set/set_sms_datapoints/(:any)', 'clients\Correlation::set_sms_datapoints/$1');

$routes->get('call_logs', 'clients\Calls::call_logs');
$routes->get('call_logs/incoming', 'clients\Calls::call_incoming');
$routes->get('call_logs/outgoing', 'clients\Calls::call_outgoing');
$routes->get('call_logs/rejected', 'clients\Calls::call_rejected');
$routes->get('call_logs/blocked', 'clients\Calls::call_blocked');

$routes->get('contacts', 'clients\Contacts');

$routes->get('contacts/analyze/sms/(:any)', 'clients\Analyze::sms/$1');
$routes->get('contacts/analyze/calls/(:any)', 'clients\Analyze::calls/$1');

$routes->get('sms', 'clients\Sms::sms');
$routes->get('sms/inbox', 'clients\Sms::sms_inbox');
$routes->get('sms/sent', 'clients\Sms::sms_sent');

$routes->get('account/profile', 'clients\Account');
$routes->get('account/setting', 'clients\Account::setting');
$routes->get('account/logs', 'clients\Account::access_logs');

$routes->get('account/profile/update', 'clients\Profile::profile_update');
$routes->get('account/profile/image_upload', 'clients\Profile::profile_upload');
$routes->get('account/setting/token_generate', 'clients\Profile::token_generate');

$routes->get('account/requests', 'clients\Requests::send_request');
$routes->get('account/requests/sleep', 'clients\Requests::send_sleep');

$routes->get('account/data/del/apps', 'clients\Profile::profile_del_apps');
$routes->get('account/data/del/call_logs', 'clients\Profile::profile_del_call_logs');
$routes->get('account/data/del/contacts', 'clients\Profile::profile_del_contacts');
$routes->get('account/data/del/sms', 'clients\Profile::profile_del_sms');

$routes->post('files/upload', 'api\Receive::upload');
$routes->post('token/verify', 'api\Receive::token_verify');
$routes->post('device/print', 'api\Receive::device_print');
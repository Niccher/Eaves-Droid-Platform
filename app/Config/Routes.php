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

// Set 404 Override - Point to custom error controller
$routes->set404Override('App\Controllers\Errors::show404');

// Set Translate URI Dashes
$routes->setTranslateURIDashes(false);

// Set Auto Route (disabled for security)
$routes->setAutoRoute(false);

// =================================================================
// 2. PUBLIC ROUTES (Landing Pages)
//    Unauthenticated access only
// =================================================================

// Base URL & Landing
$routes->get('/', 'Home::index', ['as' => 'home']);
$routes->get('landing', 'Home::index', ['as' => 'landing']);

// Information Pages
$routes->get('download', 'Home::landing_download', ['as' => 'download']);
$routes->get('aboutus', 'Home::landing_aboutus', ['as' => 'about']);
$routes->get('faqs_terms', 'Home::landing_faqs', ['as' => 'faqs']);
$routes->get('how_to', 'Home::landing_how_to', ['as' => 'how-to']);
$routes->get('privacy-policy', 'Home::landing_privacy', ['as' => 'privacy-policy']);
$routes->get('pricing', 'Home::landing_prices', ['as' => 'pricing']);

// Contact Form (GET for view, POST for submission)
$routes->get('contactus', 'ContactController::index', ['as' => 'contact']);
$routes->post('contactus', 'ContactController::send');

// Temporary backfill route (remove after use)
$routes->get('admin/backfill-created-at', function() {
    $db = \Config\Database::connect();
    $db->query("UPDATE tbl_device_profile SET created_at = extraction_timestamp WHERE created_at IS NULL;");
    return "Updated " . $db->affectedRows() . " rows";
});

// =================================================================
// 3. ERROR PAGES ROUTES
//    Custom error pages accessible to all users
// =================================================================

$routes->group('', ['namespace' => 'App\Controllers'], static function ($routes) {
    /**
     * Displays a 403 Forbidden error page.
     *
     * @return string
     */
    $routes->get('error/403', 'Errors::show403', ['as' => 'error-403']);

    /**
     * Displays a 404 Not Found error page.
     *
     * @return string
     */
    $routes->get('error/404', 'Errors::show404', ['as' => 'error-404']);

    /**
     * Displays a 500 Internal Server Error page.
     *
     * @return string
     */
    $routes->get('error/500', 'Errors::show500', ['as' => 'error-500']);

    /**
     * Displays a 503 Service Unavailable error page.
     *
     * @return string
     */
    $routes->get('error/503', 'Errors::show503', ['as' => 'error-503']);

    /**
     * Displays a general error page.
     *
     * @return string
     */
    $routes->get('error/general', 'Errors::showGeneral', ['as' => 'error-general']);

    // Test routes for error pages (development only)
    if (ENVIRONMENT === 'development') {
        /**
         * Triggers a 403 error for testing.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->get('error/test/403', 'Errors::trigger403', ['as' => 'error-test-403']);

        /**
         * Triggers a 404 error for testing.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->get('error/test/404', 'Errors::trigger404', ['as' => 'error-test-404']);

        /**
         * Triggers a 500 error for testing.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->get('error/test/500', 'Errors::trigger500', ['as' => 'error-test-500']);

        /**
         * Triggers a 503 error for testing.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->get('error/test/503', 'Errors::trigger503', ['as' => 'error-test-503']);
    }
});

// =================================================================
// 4. AUTHENTICATION ROUTES (CodeIgniter Shield)
//    Custom authentication controllers override Shield defaults
// =================================================================

// Custom Authentication Routes (override Shield defaults)
// Defining these before service('auth')->routes() ensures they take precedence
$routes->group('', ['namespace' => 'App\Controllers\auth'], static function ($routes) {
    /**
     * Displays login view.
     *
     * @return string
     */
    $routes->get('login', 'LoginController::loginView', ['as' => 'login']);

    /**
     * Handles login action.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    $routes->post('login', 'LoginController::loginAction');

    /**
     * Handles logout action.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    $routes->get('logout', 'LoginController::logoutAction', ['as' => 'logout']);

    /**
     * Displays registration view.
     *
     * @return string
     */
    $routes->get('register', 'RegisterController::registerView', ['as' => 'register']);

    /**
     * Handles registration action.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    $routes->post('register', 'RegisterController::registerAction');

    /**
     * Displays forgot password view.
     *
     * @return string
     */
    $routes->get('forgot', 'ForgotPasswordController::forgotView', ['as' => 'forgot-password']);

    /**
     * Handles forgot password action.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    $routes->post('forgot', 'ForgotPasswordController::forgotAction');

    /**
     * Displays reset password view.
     *
     * @return string
     */
    $routes->get('reset-password', 'ForgotPasswordController::resetView', ['as' => 'reset-password']);

    /**
     * Handles reset password action.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    $routes->post('reset-password', 'ForgotPasswordController::resetAction');

    /**
     * Offline password reset (no email required).
     */
    $routes->get('forgot/offline', 'ForgotPasswordController::offlineResetView', ['as' => 'forgot-offline']);
    $routes->post('forgot/offline', 'ForgotPasswordController::offlineResetAction');
});

// Load Shield routes after custom routes so custom routes take precedence (for password reset, email verification, etc.)
service('auth')->routes($routes);

// =================================================================
// 5. PROTECTED CLIENT/DASHBOARD ROUTES
//    All routes require authentication (session filter) + role filter
// =================================================================

$routes->group('', [
    'namespace' => 'App\Controllers\clients',
    'filter' => ['maintenance', 'session']  // Maintenance first, then authentication; role check handled globally
], static function ($routes) {

    // =============================================================
    // 5.1 DASHBOARD & GENERAL PAGES
    // =============================================================

    /**
     * Displays main client dashboard.
     *
     * @return string
     */
    $routes->get('home', 'Client::home', ['as' => 'client-dashboard']);

    /**
     * Handles universal search across SMS, Calls, Contacts, Files, and Apps.
     *
     * @return string
     */
    $routes->get('globalsearch', 'GlobalSearch::index', ['as' => 'global-search']);
    $routes->get('globalsearch/(:any)', 'GlobalSearch::search/$1');


    /**
     * Displays client FAQ page.
     *
     * @return string
     */
    $routes->get('faqs', 'Client::faqs', ['as' => 'client-faqs']);

    // =============================================================
    // 5.2 DATA VIEWS - APPS
    // =============================================================

    $routes->group('apps', static function ($routes) {
        /**
         * Displays all apps with pagination.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('/', 'Apps::apps', ['as' => 'apps-all']);
        $routes->get('(:num)', 'Apps::apps/$1');

        /**
         * Displays unique apps.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('unique', 'Apps::apps_unique', ['as' => 'apps-unique']);
        $routes->get('unique/(:num)', 'Apps::apps_unique/$1');

        /**
         * Displays recently used apps.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('last_time', 'Apps::apps_last_time', ['as' => 'apps-recent']);
        $routes->get('last_time/(:num)', 'Apps::apps_last_time/$1');

        /**
         * Displays complete apps list.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('all_apps', 'Apps::apps_all', ['as' => 'apps-complete']);
        $routes->get('all_apps/(:num)', 'Apps::apps_all/$1');

        /**
         * Displays system apps.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('system', 'Apps::apps_system', ['as' => 'apps-system']);
        $routes->get('system/(:num)', 'Apps::apps_system/$1');

        /**
         * Displays user-installed apps.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('user', 'Apps::apps_user', ['as' => 'apps-user']);
        $routes->get('user/(:num)', 'Apps::apps_user/$1');

        /**
         * Deletes an app entry.
         *
         * @param mixed $id App counter
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('delete/(:num)', 'Apps::delete/$1');

    });

    // =============================================================
    // 5.2.1 DATA VIEWS - FILES
    // =============================================================

    $routes->group('files', static function ($routes) {
        /**
         * Displays all files.
         *
         * @return string
         */
        $routes->post('delete/(:num)', 'Files::delete/$1');
        $routes->get('/', 'Files::index', ['as' => 'files-all']);

        /**
         * Displays images.
         *
         * @return string
         */
        $routes->get('images', 'Files::images', ['as' => 'files-images']);

        /**
         * Displays videos.
         *
         * @return string
         */
        $routes->get('videos', 'Files::videos', ['as' => 'files-videos']);

        /**
         * Displays media files (images + videos).
         *
         * @return string
         */
        $routes->get('media', 'Files::media', ['as' => 'files-media']);

        /**
         * Displays documents.
         *
         * @return string
         */
        $routes->get('documents', 'Files::documents', ['as' => 'files-documents']);

        /**
         * Displays audio files.
         *
         * @return string
         */
        $routes->get('audio', 'Files::audio', ['as' => 'files-audio']);

        /**
         * Displays archive files.
         *
         * @return string
         */
        $routes->get('archives', 'Files::archives', ['as' => 'files-archives']);

        /**
         * Displays other files.
         *
         * @return string
         */
        $routes->get('others', 'Files::others', ['as' => 'files-others']);
    });

    // =============================================================
    // 5.3 DATA VIEWS - CALL LOGS
    // =============================================================

    $routes->group('call_logs', static function ($routes) {
        /**
         * Displays all call logs.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('/', 'Calls::call_logs', ['as' => 'call-logs-all']);
        $routes->get('(:num)', 'Calls::call_logs/$1');

        /**
         * Displays incoming calls.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('incoming', 'Calls::call_incoming', ['as' => 'call-logs-incoming']);
        $routes->get('incoming/(:num)', 'Calls::call_incoming/$1');

        /**
         * Displays outgoing calls.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('outgoing', 'Calls::call_outgoing', ['as' => 'call-logs-outgoing']);
        $routes->get('outgoing/(:num)', 'Calls::call_outgoing/$1');

        /**
         * Displays rejected calls.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('rejected', 'Calls::call_rejected', ['as' => 'call-logs-rejected']);
        $routes->get('rejected/(:num)', 'Calls::call_rejected/$1');

        /**
         * Displays blocked calls.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('blocked', 'Calls::call_blocked', ['as' => 'call-logs-blocked']);
        $routes->get('blocked/(:num)', 'Calls::call_blocked/$1');
        $routes->post('delete/(:num)', 'Calls::delete/$1');
    });

    // =============================================================
    // 5.4 DATA VIEWS - SMS
    // =============================================================
    // Location Routes
    $routes->group('location', static function ($routes) {
        $routes->get('/', 'Location::simplified', ['as' => 'location-all']);
        $routes->get('map', 'Location::map', ['as' => 'location-map']);
        $routes->get('map/(:num)', 'Location::map/$1');
        $routes->get('(:num)', 'Location::simplified/$1');
        $routes->post('delete/(:num)', 'Location::delete/$1');
    });

    // Activity Routes
    $routes->group('activities', static function ($routes) {
        $routes->get('/', 'Location::activities', ['as' => 'activity-all']);
        $routes->get('(:num)', 'Location::activities/$1');
        $routes->post('delete/(:num)', 'Location::deleteActivity/$1');
    });

    // Advanced Data Extractions
    $routes->group('advanced', static function ($routes) {
        $routes->group('hardware', static function ($routes) {
            // Landing page
            $routes->get('/', 'Advanced::hardware', ['as' => 'adv-hardware']);

            $routes->get('device', 'Advanced::device_context', ['as' => 'adv-device']);
            $routes->get('network', 'Advanced::network_info', ['as' => 'adv-network']);
            $routes->get('bluetooth', 'Advanced::bluetooth', ['as' => 'adv-bluetooth']);
            $routes->get('sensors', 'Advanced::sensors', ['as' => 'adv-sensors']);
            $routes->get('camera_info', 'Advanced::camera_info', ['as' => 'adv-camera-info']);
            $routes->get('battery_stats', 'Advanced::battery_stats', ['as' => 'adv-battery-stats']);
            $routes->get('processes', 'Advanced::processes', ['as' => 'adv-processes']);
            $routes->get('proc_info', 'Advanced::proc_info', ['as' => 'adv-proc-info']);

            $routes->get('cell_towers', 'Advanced::cell_towers', ['as' => 'adv-cell-towers']);
            $routes->get('display_info', 'Advanced::display_info', ['as' => 'adv-display-info']);
            $routes->get('storage', 'Advanced::storage', ['as' => 'adv-storage']);
            $routes->get('thermal', 'Advanced::thermal', ['as' => 'adv-thermal']);
            $routes->get('nfc', 'Advanced::nfc', ['as' => 'adv-nfc']);
            $routes->get('hardware_graphics', 'Advanced::hardware_graphics', ['as' => 'adv-hardware-graphics']);
            $routes->get('hardware_network', 'Advanced::hardware_network', ['as' => 'adv-hardware-network']);
            $routes->get('audio_devices', 'Advanced::audio_devices', ['as' => 'adv-audio-devices']);
            $routes->get('biometric', 'Advanced::biometric', ['as' => 'adv-biometric']);
            $routes->get('gnss_hardware', 'Advanced::gnss_hardware', ['as' => 'adv-gnss-hardware']);
            $routes->get('power_rails', 'Advanced::power_rails', ['as' => 'adv-power-rails']);
            $routes->get('usb_devices', 'Advanced::usb_devices', ['as' => 'adv-usb-devices']);
            $routes->get('vibration', 'Advanced::vibration', ['as' => 'adv-vibration']);

            // Additional hardware pages
            $routes->get('hardware_dashboard', 'Advanced::hardware_dashboard', ['as' => 'adv-hardware-dashboard']);
            $routes->get('battery_power', 'Advanced::battery_power', ['as' => 'adv-battery-power']);
            $routes->get('system_performance', 'Advanced::system_performance', ['as' => 'adv-system-performance']);
            $routes->get('network_connectivity', 'Advanced::network_connectivity', ['as' => 'adv-network-connectivity']);
            $routes->get('display_graphics', 'Advanced::display_graphics', ['as' => 'adv-display-graphics']);
            $routes->get('sensors_location', 'Advanced::sensors_location', ['as' => 'adv-sensors-location']);
            $routes->get('media_hardware', 'Advanced::media_hardware', ['as' => 'adv-media-hardware']);
            $routes->get('storage_peripherals', 'Advanced::storage_peripherals', ['as' => 'adv-storage-peripherals']);
            $routes->get('shortrange_auth', 'Advanced::shortrange_auth', ['as' => 'adv-shortrange-auth']);
            $routes->get('device_fingerprint', 'Advanced::device_fingerprint', ['as' => 'adv-device-fingerprint']);

            // Delete routes - hardware pages
            $routes->post('device/delete/(:num)', 'Advanced::delete_device_context/$1');
            $routes->post('network/delete/(:num)', 'Advanced::delete_network_info/$1');
            $routes->post('bluetooth/delete/(:num)', 'Advanced::delete_bluetooth_row/$1');
            $routes->post('sensors/delete/(:num)', 'Advanced::delete_sensor_profile/$1');
            $routes->post('camera_info/delete/(:num)', 'Advanced::delete_camera_info/$1');
            $routes->post('battery_stats/delete/(:num)', 'Advanced::delete_battery_stats/$1');
            $routes->post('processes/delete/(:num)', 'Advanced::delete_processes/$1');
            $routes->post('proc_info/delete/(:num)', 'Advanced::delete_proc_info/$1');
            $routes->post('cell_towers/delete/(:num)', 'Advanced::delete_cell_towers/$1');
            $routes->post('display_info/delete/(:num)', 'Advanced::delete_display_info/$1');
            $routes->post('storage/delete/(:num)', 'Advanced::delete_storage/$1');
            $routes->post('thermal/delete/(:num)', 'Advanced::delete_thermal/$1');
            $routes->post('nfc/delete/(:num)', 'Advanced::delete_nfc/$1');
            $routes->post('hardware_graphics/delete/(:num)', 'Advanced::delete_hardware_graphics/$1');
            $routes->post('hardware_network/delete/(:num)', 'Advanced::delete_hardware_network/$1');
            $routes->post('audio_devices/delete/(:num)', 'Advanced::delete_audio_devices/$1');
            $routes->post('biometric/delete/(:num)', 'Advanced::delete_biometric/$1');
            $routes->post('gnss_hardware/delete/(:num)', 'Advanced::delete_gnss_hardware/$1');
            $routes->post('power_rails/delete/(:num)', 'Advanced::delete_power_rails/$1');
            $routes->post('usb_devices/delete/(:num)', 'Advanced::delete_usb_devices/$1');
            $routes->post('vibration/delete/(:num)', 'Advanced::delete_vibration/$1');
            $routes->post('hardware_dashboard/delete/(:num)', 'Advanced::delete_hardware_dashboard/$1');
            $routes->post('battery_power/delete/(:num)', 'Advanced::delete_battery_power/$1');
            $routes->post('system_performance/delete/(:num)', 'Advanced::delete_system_performance/$1');
            $routes->post('network_connectivity/delete/(:num)', 'Advanced::delete_network_connectivity/$1');
            $routes->post('display_graphics/delete/(:num)', 'Advanced::delete_display_graphics/$1');
            $routes->post('sensors_location/delete/(:num)', 'Advanced::delete_sensors_location/$1');
            $routes->post('media_hardware/delete/(:num)', 'Advanced::delete_media_hardware/$1');
            $routes->post('storage_peripherals/delete/(:num)', 'Advanced::delete_storage_peripherals/$1');
            $routes->post('shortrange_auth/delete/(:num)', 'Advanced::delete_shortrange_auth/$1');
            $routes->post('device_fingerprint/delete/(:num)', 'Advanced::delete_device_fingerprint/$1');

            // SIM Configs (kept in hardware group)
            $routes->get('sim-configs', 'SimConfig::index', ['as' => 'sim-configs']);
            $routes->post('sim-configs/delete/(:num)', 'SimConfig::delete/$1');
        });

        $routes->group('software', static function ($routes) {
            // Landing page
            $routes->get('/', 'Advanced::software', ['as' => 'adv-software']);

            $routes->get('accounts', 'Advanced::accounts', ['as' => 'adv-accounts']);
            $routes->get('calendar', 'Advanced::calendar', ['as' => 'adv-calendar']);
            $routes->get('app-usage', 'Advanced::app_usage', ['as' => 'adv-app-usage']);
            $routes->get('app-usage/(:any)', 'Advanced::app_usage_detail/$1', ['as' => 'adv-app-usage-detail']);
            $routes->get('notifications', 'Advanced::notifications', ['as' => 'adv-notifications']);
            $routes->get('notifications/(:any)', 'Advanced::notification_detail/$1', ['as' => 'adv-notification-detail']);
            $routes->get('accessibility', 'Advanced::accessibility', ['as' => 'adv-accessibility']);
            $routes->get('input_methods', 'Advanced::input_methods', ['as' => 'adv-input-methods']);
            $routes->get('security_audit', 'Advanced::security_audit', ['as' => 'adv-security-audit']);

            $routes->get('data_usage', 'Advanced::data_usage', ['as' => 'adv-data-usage']);
            $routes->get('saved_wifi', 'Advanced::saved_wifi', ['as' => 'adv-saved-wifi']);
            $routes->get('default_apps', 'Advanced::default_apps', ['as' => 'adv-default-apps']);
            $routes->get('alarms', 'Advanced::alarms', ['as' => 'adv-alarms']);
            $routes->get('app_security', 'Advanced::app_security', ['as' => 'adv-app-security']);
            $routes->get('network_security', 'Advanced::network_security', ['as' => 'adv-network-security']);
            $routes->get('telephony_network', 'Advanced::telephony_network', ['as' => 'adv-telephony-network']);
            $routes->get('system_locale', 'Advanced::system_locale', ['as' => 'adv-system-locale']);
            $routes->get('app_permissions', 'Advanced::app_permissions', ['as' => 'adv-app-permissions']);
            $routes->get('browser_history', 'Advanced::browser_history', ['as' => 'adv-browser-history']);
            $routes->get('clipboard', 'Advanced::clipboard', ['as' => 'adv-clipboard']);
            $routes->get('content_providers', 'Advanced::content_providers', ['as' => 'adv-content-providers']);
            $routes->get('crash_logs', 'Advanced::crash_logs', ['as' => 'adv-crash-logs']);
            $routes->get('digital_wellbeing', 'Advanced::digital_wellbeing', ['as' => 'adv-digital-wellbeing']);
            $routes->get('doze_standby', 'Advanced::doze_standby', ['as' => 'adv-doze-standby']);
            $routes->get('email', 'Advanced::email', ['as' => 'adv-email']);
            $routes->get('health_data', 'Advanced::health_data', ['as' => 'adv-health-data']);
            $routes->get('keyboard_input', 'Advanced::keyboard_input', ['as' => 'adv-keyboard-input']);
            $routes->get('keyguard', 'Advanced::keyguard', ['as' => 'adv-keyguard']);
            $routes->get('screenshots', 'Advanced::screenshots', ['as' => 'adv-screenshots']);
            $routes->get('screen_state', 'Advanced::screen_state', ['as' => 'adv-screen-state']);
            $routes->get('vpn_config', 'Advanced::vpn_config', ['as' => 'adv-vpn-config']);
            $routes->get('running_processes', 'Advanced::running_processes', ['as' => 'adv-running-processes']);

            $routes->post('datatable/app-usage', '\App\Controllers\api\v1\DatatableAPI::getAppUsageDetails', ['as' => 'adv-datatable-app-usage']);
            $routes->post('datatable/notifications', '\App\Controllers\api\v1\DatatableAPI::getNotificationDetails', ['as' => 'adv-datatable-notifications']);

            // Delete routes - software pages
            $routes->post('app-usage/delete/(:num)', 'Advanced::delete_app_usage/$1');
            $routes->post('notifications/delete/(:any)', 'Advanced::delete_notifications_by_app/$1');
            $routes->post('notifications/delete-row/(:num)', 'Advanced::delete_notification_row/$1');
            $routes->post('accounts/delete/(:num)', 'Advanced::delete_accounts_row/$1');
            $routes->post('calendar/delete/(:num)', 'Advanced::delete_calendar_event/$1');
            $routes->post('app-usage/delete-package/(:any)', 'Advanced::delete_app_usage_by_package/$1');
            $routes->post('security_audit/delete/(:num)', 'Advanced::delete_security_audit_row/$1');
            $routes->post('data_usage/delete/(:num)', 'Advanced::delete_data_usage/$1');
            $routes->post('saved_wifi/delete/(:num)', 'Advanced::delete_saved_wifi/$1');
            $routes->post('default_apps/delete/(:num)', 'Advanced::delete_default_apps/$1');
            $routes->post('alarms/delete/(:num)', 'Advanced::delete_alarms/$1');
            $routes->post('accessibility/delete/(:num)', 'Advanced::delete_accessibility/$1');
            $routes->post('input_methods/delete/(:num)', 'Advanced::delete_input_methods/$1');
            $routes->post('app_security/delete/(:num)', 'Advanced::delete_app_security/$1');
            $routes->post('network_security/delete/(:num)', 'Advanced::delete_network_security/$1');
            $routes->post('telephony_network/delete/(:num)', 'Advanced::delete_telephony_network/$1');
            $routes->post('system_locale/delete/(:num)', 'Advanced::delete_system_locale/$1');
            $routes->post('app_permissions/delete/(:num)', 'Advanced::delete_app_permissions/$1');
            $routes->post('browser_history/delete/(:num)', 'Advanced::delete_browser_history/$1');
            $routes->post('clipboard/delete/(:num)', 'Advanced::delete_clipboard/$1');
            $routes->post('content_providers/delete/(:num)', 'Advanced::delete_content_providers/$1');
            $routes->post('crash_logs/delete/(:num)', 'Advanced::delete_crash_logs/$1');
            $routes->post('digital_wellbeing/delete/(:num)', 'Advanced::delete_digital_wellbeing/$1');
            $routes->post('doze_standby/delete/(:num)', 'Advanced::delete_doze_standby/$1');
            $routes->post('email/delete/(:num)', 'Advanced::delete_email/$1');
            $routes->post('health_data/delete/(:num)', 'Advanced::delete_health_data/$1');
            $routes->post('keyboard_input/delete/(:num)', 'Advanced::delete_keyboard_input/$1');
            $routes->post('keyguard/delete/(:num)', 'Advanced::delete_keyguard/$1');
            $routes->post('screenshots/delete/(:num)', 'Advanced::delete_screenshots/$1');
            $routes->post('screen_state/delete/(:num)', 'Advanced::delete_screen_state/$1');
            $routes->post('vpn_config/delete/(:num)', 'Advanced::delete_vpn_config/$1');
            $routes->post('running_processes/delete/(:num)', 'Advanced::delete_running_processes/$1');
        });
    });

    $routes->get('advanced/media', 'Advanced::remote_media');
    $routes->get('advanced/media/serve/(:any)', 'Advanced::serve_media/$1');
    $routes->post('advanced/media/delete/(:num)', 'Advanced::delete_media/$1');
    $routes->get('remote-device', 'Advanced::remote_device', ['as' => 'adv-remote-device']);

    // =============================================================
    // Unified Data Deletion & Export
    // =============================================================
    $routes->group('admin/data', static function ($routes) {
        $routes->post('delete-all', 'Advanced::delete_all_user_data', ['as' => 'admin-delete-all-data']);
        $routes->post('export-all', 'Advanced::export_all_user_data', ['as' => 'admin-export-all-data']);
    });


    /**
     * Group for SMS related actions.
     */
    $routes->group('sms', static function ($routes) {
        /**
         * Displays all SMS messages.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('/', 'Sms::sms', ['as' => 'sms-all']);
        $routes->get('(:num)', 'Sms::sms/$1');

        /**
         * Displays inbox SMS messages.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('inbox', 'Sms::sms_inbox', ['as' => 'sms-inbox']);
        $routes->get('inbox/(:num)', 'Sms::sms_inbox/$1');

        /**
         * Displays sent SMS messages.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('sent', 'Sms::sms_sent', ['as' => 'sms-sent']);
        $routes->get('sent/(:num)', 'Sms::sms_sent/$1');
        $routes->post('delete/(:num)', 'Sms::delete/$1');
    });

    // =============================================================
    // 5.5 DATA VIEWS - CONTACTS
    // =============================================================

    $routes->group('contacts', static function ($routes) {
        /**
         * Displays all contacts.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('/', 'Contacts::index', ['as' => 'contacts-all']);
        $routes->get('(:num)', 'Contacts::index/$1');

        /**
         * Displays favorite contacts.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('favorites', 'Contacts::view/favorites', ['as' => 'contacts-favorites']);
        $routes->get('favorites/(:num)', 'Contacts::view/favorites/$1');

        /**
         * Displays recent contacts.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('recent', 'Contacts::view/recent', ['as' => 'contacts-recent']);
        $routes->get('recent/(:num)', 'Contacts::view/recent/$1');

        /**
         * Displays individual contact view.
         *
         * @param string $contactId Contact identifier
         * @return string
         */
        $routes->post('delete/(:num)', 'Contacts::delete/$1');
        $routes->get('view/(:any)', 'Contacts::viewContact/$1', ['as' => 'contact-view']);

        /**
         * Analyzes SMS with specific contact.
         *
         * @param string $contactId Contact identifier
         * @return string
         */
        $routes->get('analyze/sms/(:any)', 'Analyze::sms/$1', ['as' => 'contact-analyze-sms']);

        /**
         * Analyzes SMS with pagination.
         *
         * @param string $contactId Contact identifier
         * @param int $page Page number
         * @return string
         */
        $routes->get('analyze/sms/(:any)/(:num)', 'Analyze::sms/$1/$2');

        /**
         * Analyzes calls with specific contact.
         *
         * @param string $contactId Contact identifier
         * @return string
         */
        $routes->get('analyze/calls/(:any)', 'Analyze::calls/$1', ['as' => 'contact-analyze-calls']);

        /**
         * Analyzes calls with pagination.
         *
         * @param string $contactId Contact identifier
         * @param int $page Page number
         * @return string
         */
        $routes->get('analyze/calls/(:any)/(:num)', 'Analyze::calls/$1/$2');


    });

    // =============================================================
    // 5.6 ANALYSIS & CORRELATION ROUTES
    // =============================================================

    $routes->group('analysis', ['filter' => 'planGate', 'namespace' => 'App\Controllers\clients'], static function ($routes) {
        /**
         * Displays analysis dashboard.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('/', 'Correlation::advanced', ['as' => 'analysis-dashboard']);
        $routes->get('(:num)', 'Correlation::index/$1');
        $routes->get('refresh-ml', 'Correlation::refresh_ml', ['as' => 'analysis-refresh-ml']);

        /**
         * Detailed SMS Analysis.
         */
        $routes->get('sms', 'Correlation::sms_analysis', ['as' => 'analysis-sms']);

        /**
         * Detailed Call Analysis.
         */
        $routes->get('calls', 'Correlation::call_analysis', ['as' => 'analysis-calls']);
        // $routes->get('advanced', 'Correlation::advanced', ['as' => 'analysis-advanced']); // Deprecated
        // Deprecated advanced routes - kept for reference
// $routes->get('advanced', 'Correlation::advanced', ['as' => 'analysis-advanced']);
// $routes->get('advanced/finance', 'Correlation::finance_analysis', ['as' => 'analysis-advanced-finance']);
// $routes->get('advanced/social', 'Correlation::social_analysis', ['as' => 'analysis-advanced-social']);
// $routes->get('advanced/lifestyle', 'Correlation::lifestyle_analysis', ['as' => 'analysis-advanced-lifestyle']);
// $routes->get('advanced/privacy', 'Correlation::privacy_audit', ['as' => 'analysis-advanced-privacy']);
// $routes->get('advanced/subscriptions', 'Correlation::subscription_tracker', ['as' => 'analysis-advanced-subscriptions']);
// $routes->get('advanced/apps', 'Correlation::app_portfolio', ['as' => 'analysis-advanced-apps']);
// $routes->get('advanced/storage', 'Correlation::storage_intelligence', ['as' => 'analysis-advanced-storage']);
// $routes->get('advanced/sentiment', 'Correlation::sentiment_analysis', ['as' => 'analysis-advanced-sentiment']);
// $routes->get('advanced/device', 'Correlation::device_pulse', ['as' => 'analysis-advanced-device']);
// $routes->get('advanced/location', 'Correlation::location_analysis', ['as' => 'analysis-advanced-location']);
// $routes->get('advanced/hotspots', 'Correlation::geoclustering_hotspots', ['as' => 'analysis-advanced-hotspots']);
// $routes->get('advanced/report', 'Correlation::generate_report', ['as' => 'analysis-advanced-report']);
        $routes->get('social', 'Correlation::social_analysis', ['as' => 'analysis-social']);
        $routes->get('lifestyle', 'Correlation::lifestyle_analysis', ['as' => 'analysis-lifestyle']);
        $routes->get('privacy', 'Correlation::privacy_audit', ['as' => 'analysis-privacy']);
        $routes->get('subscriptions', 'Correlation::subscription_tracker', ['as' => 'analysis-subscriptions']);
        $routes->get('apps', 'Correlation::app_portfolio', ['as' => 'analysis-apps']);
        $routes->get('finance', 'Correlation::finance_analysis', ['as' => 'analysis-finance']);
        $routes->get('storage', 'Correlation::storage_intelligence', ['as' => 'analysis-storage']);
        $routes->get('sentiment', 'Correlation::sentiment_analysis', ['as' => 'analysis-sentiment']);
        $routes->get('device', 'Correlation::device_pulse', ['as' => 'analysis-device']);
        $routes->get('location', 'Correlation::location_analysis', ['as' => 'analysis-location']);
        $routes->get('hotspots', 'Correlation::geoclustering_hotspots', ['as' => 'analysis-hotspots']);
        $routes->get('report', 'Correlation::generate_report', ['as' => 'analysis-report']);

        /**
         * Digital Wellbeing.
         */
        $routes->get('wellbeing', 'Correlation::digital_wellbeing', ['as' => 'analysis-wellbeing']);

        /**
         * Behavioral Anomaly Analysis.
         */
        $routes->get('behavioral-anomalies', 'Correlation::behavioral_anomalies', ['as' => 'analysis-anomalies']);

        /**
         * Universal Timeline.
         */
        $routes->get('timeline', 'Correlation::intelligence_timeline', ['as' => 'analysis-timeline']);

        /**
         * Correlation Engine (Platinum).
         */
        $routes->get('correlation-engine', 'Correlation::correlation_engine', ['as' => 'analysis-correlation-engine']);

        /**
         * Risk Score & Care Plan.
         */
        $routes->get('care-plan', 'Correlation::risk_care_plan', ['as' => 'analysis-care-plan']);

        /**
         * Blocklist Management
         */
        $routes->get('blocklist', 'Blocklist::index', ['as' => 'analysis-blocklist']);
        $routes->post('blocklist/add', 'Blocklist::add', ['as' => 'analysis-blocklist-add']);
        $routes->post('blocklist/delete/(:num)', 'Blocklist::delete/$1', ['as' => 'analysis-blocklist-delete']);
        $routes->get('advanced_timeline', 'Advanced::timeline', ['as' => 'adv-timeline']);

    // =============================================================
    // 5.6 ANOMALIES WIZARD ROUTES
    // URL: /analysis/anomalies  (Step 1)
    // URL: /analysis/anomalies/algorithms  (Step 2)
    // URL: /analysis/anomalies/results  (Step 3)
    // =============================================================
    $routes->group('anomalies', static function ($routes) {
        $routes->get('/',          'Anomalies::index',      ['as' => 'anomalies-info']);
        $routes->get('algorithms', 'Anomalies::algorithms', ['as' => 'anomalies-algorithms']);
        $routes->match(['get', 'post'], 'results', 'Anomalies::results', ['as' => 'anomalies-results']);
        $routes->match(['get', 'post'], 'run', 'Anomalies::run', ['as' => 'anomalies-run']);
        $routes->get('progress/(:num)', 'Anomalies::progress/$1', ['as' => 'anomalies-progress']);
        $routes->get('status/(:num)',   'Anomalies::status/$1',   ['as' => 'anomalies-status']);
        $routes->post('process/(:num)','Anomalies::process/$1',  ['as' => 'anomalies-process']);
        $routes->get('advanced',       'Anomalies::upgradeAdvanced', ['as' => 'anomalies-advanced']);
    });

        /**
         * Displays financial SMS analysis.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('sms/finance', 'Correlation::sms_finance', ['as' => 'analysis-sms-finance']);
        $routes->get('sms/finance/(:num)', 'Correlation::sms_finance/$1');

        /**
         * Analyzes financial SMS from specific sender.
         *
         * @param string $sender Sender identifier
         * @return string
         */
        $routes->get('sms/finance/(:any)', 'Correlation::sms_analyze_finance_from/$1');
        $routes->get('sms/finance/(:any)/(:num)', 'Correlation::sms_analyze_finance_from/$1/$2');

        /**
         * Displays SMS rules configuration.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('set_rules', 'Correlation::set_sms_rules', ['as' => 'analysis-set-rules']);
        $routes->get('set_rules/(:num)', 'Correlation::set_sms_rules/$1');

        /**
         * Sets SMS datapoints configuration.
         *
         * @param string $rule Rule identifier
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('set/set_sms_datapoints/(:any)', 'Correlation::set_sms_datapoints/$1');
    });

    // =============================================================
    // 5.7 ACCOUNT MANAGEMENT ROUTES
    // =============================================================

    $routes->group('account', static function ($routes) {
        // ---------------------------------------------------------
        // PROFILE MANAGEMENT
        // ---------------------------------------------------------

        /**
         * Displays account profile page.
         *
         * @return string
         */
        $routes->get('home', 'Account::home', ['as' => 'account-profile']);
        $routes->get('profile', 'Account::home'); // Legacy alias
        $routes->post('profile', 'Account::updateProfile'); // Handle POST updates on profile link

        /**
         * Updates user profile.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('updateProfile', 'Account::updateProfile', ['as' => 'account-update-profile']);

        /**
         * Sends reset command to Android device.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('reset-device', 'Account::sendDeviceReset', ['as' => 'account-reset-device']);

        /**
         * Uploads profile image.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('uploadImage', 'Account::uploadImage', ['as' => 'account-upload-image']);

        // ---------------------------------------------------------
        // SETTINGS & TOKEN MANAGEMENT
        // ---------------------------------------------------------

        /**
         * Displays account settings page.
         *
         * @return string
         */
        $routes->get('setting', 'Account::setting', ['as' => 'account-settings']);

        /**
         * Regenerates user token.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('regenerateToken', 'Account::regenerateToken', ['as' => 'account-regenerate-token']);

        /**
         * Revokes user token.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('revokeToken', 'Account::revokeToken', ['as' => 'account-revoke-token']);

        /**
         * Creates a new named token.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('createToken', 'Account::createToken', ['as' => 'account-create-token']);

        /**
         * Switches active device filter.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->get('switch-device/(:any)', 'BaseClientController::switchDevice/$1', ['as' => 'account-switch-device']);

        /**
         * Displays user tokens.
         *
         * @return string
         */
        $routes->get('tokens', 'Account::tokens', ['as' => 'account-tokens']);

        // ---------------------------------------------------------
        // ACCESS LOGS & SECURITY
        // ---------------------------------------------------------

        /**
         * Displays access logs.
         *
         * @return string
         */
        $routes->get('access_logs', 'Account::access_logs', ['as' => 'account-access-logs']);

        /**
         * Displays filtered access logs.
         *
         * @param string $filter Filter type (web, android, all)
         * @return string
         */
        $routes->get('access_logs/(:any)', 'Account::access_logs/$1');
        $routes->post('clear_logs', 'Account::clearLogs', ['as' => 'account-clear-logs']);
        $routes->post('add_log_note', 'Account::addLogNote', ['as' => 'account-add-log-note']);

        /**
         * Displays security settings.
         *
         * @return string
         */
        $routes->get('security', 'Account::security', ['as' => 'account-security']);

        /**
         * Updates security settings.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('updateSecurity', 'Account::updateSecurity', ['as' => 'account-update-security']);

        /**
         * Displays user devices.
         *
         * @return string
         */
        $routes->get('devices', 'Account::devices', ['as' => 'account-devices']);

        /**
         * Displays user sessions.
         *
         * @return string
         */
        $routes->get('sessions', 'Account::sessions', ['as' => 'account-sessions']);

        /**
         * Terminates a user session.
         *
         * @param string $sessionId Session identifier
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('terminateSession/(:any)', 'Account::terminateSession/$1', ['as' => 'account-terminate-session']);

        // ---------------------------------------------------------
        // DATA EXPORT & MANAGEMENT
        // ---------------------------------------------------------

        /**
         * Exports user data.
         *
         * @param string $type Data type to export
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->get('exportData/(:any)', 'Account::exportData/$1', ['as' => 'account-export-data']);
        $routes->post('export-email', 'Account::exportEmail', ['as' => 'account-export-email']);
        $routes->get('downloads/export/(:any)', 'Account::downloadExport/$1', ['as' => 'account-download-export']);

        /**
         * Displays data deletion confirmation.
         *
         * @param string $type Data type to delete
         * @return string
         */
        $routes->get('deleteData/(:any)', 'Account::deleteData/$1', ['as' => 'account-delete-data-confirm']);

        /**
         * Deletes user data.
         *
         * @param string $type Data type to delete
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('deleteData/(:any)', 'Account::deleteData/$1', ['as' => 'account-delete-data']);

        /**
         * Displays account statistics.
         *
         * @return string
         */
        $routes->get('stats', 'Account::stats', ['as' => 'account-stats']);

        // ---------------------------------------------------------
        // LEGACY ROUTES (For backward compatibility)
        // ---------------------------------------------------------

        /**
         * Updates profile (legacy).
         *
         * @return \CodeIgniter\HTTP\RedirectResponse
         */
        $routes->match(['get', 'post'], 'profile/update', 'Profile::profile_update');

        /**
         * Uploads profile image (legacy).
         *
         * @return \CodeIgniter\HTTP\RedirectResponse
         */
        $routes->match(['get', 'post'], 'profile/image_upload', 'Profile::profile_upload');

        /**
         * Generates token (legacy).
         *
         * @return \CodeIgniter\HTTP\RedirectResponse
         */
        $routes->post('setting/token_generate', 'Profile::token_generate');

        /**
         * Deletes all apps (legacy).
         *
         * @return \CodeIgniter\HTTP\RedirectResponse
         */
        $routes->post('data/del/apps', 'Profile::profile_del_apps');

        /**
         * Deletes all call logs (legacy).
         *
         * @return \CodeIgniter\HTTP\RedirectResponse
         */
        $routes->post('data/del/call_logs', 'Profile::profile_del_call_logs');

        /**
         * Deletes all contacts (legacy).
         *
         * @return \CodeIgniter\HTTP\RedirectResponse
         */
        $routes->post('data/del/contacts', 'Profile::profile_del_contacts');

        /**
         * Deletes all SMS (legacy).
         *
         * @return \CodeIgniter\HTTP\RedirectResponse
         */
        $routes->post('data/del/sms', 'Profile::profile_del_sms');

        // Legacy Access Logs Alias
        $routes->get('logs', 'Account::access_logs');
    });

    // =============================================================
    // 5.7B BILLING / SUBSCRIPTION UPGRADE (simulated payments)
    // =============================================================
    $routes->group('billing', static function ($routes) {
        /**
         * Standalone billing / upgrade page (simulated checkout).
         */
        $routes->get('', 'Billing::index', ['as' => 'billing']);

        /**
         * Simulates a subscription payment and self-upgrades the user's plan.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('simulate', 'Billing::simulateUpgrade', ['as' => 'billing-simulate']);

        /**
         * Returns the user's active subscription details (AJAX).
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->get('subscription', 'Billing::subscription', ['as' => 'billing-subscription']);
    });

    // =============================================================
    // 5.8 OTHER CLIENT ROUTES
    // =============================================================

    /**
     * Sends device command (AJAX).
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('requests/send_command', 'Requests::send_command', ['as' => 'client-send-command']);
});

$routes->get('downloads/export/(:any)', '\App\Controllers\clients\Account::downloadExport/$1');

// =================================================================
// 6. API ROUTES (Mobile & External Integration)
//    Requires API authentication (tokens filter)
// =================================================================

$routes->group('api/v1', [
    'namespace' => 'App\Controllers\api\v1',
    'filter' => ['maintenance', 'throttle:api']  // Maintenance first, then rate limiting
], static function ($routes) {

    // -------------------------------------------------------------
    // 6.1 AUTHENTICATION & TOKEN VERIFICATION
    // -------------------------------------------------------------

    /**
     * Verifies API token.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('token/verify', 'Receive::token_verify', ['as' => 'api-token-verify']);

    // -------------------------------------------------------------
    // 6.2 DEVICE REGISTRATION & MANAGEMENT
    // -------------------------------------------------------------

    /**
     * Registers device fingerprint.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('device/print', 'Receive::device_print', ['as' => 'api-device-print']);

    /**
     * Checks device status.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->get('device/status', 'Receive::device_status', ['as' => 'api-device-status']);

    /**
     * Sync device config + permissions from Android device.
     */
    $routes->post('device/config/sync', 'DeviceConfigController::sync', ['as' => 'api-device-config-sync']);

    /**
     * Fetch last known device config.
     */
    $routes->get('device/config/(:any)', 'DeviceConfigController::fetch/$1', ['as' => 'api-device-config-fetch']);

    /**
     * Fetch app defaults for Android devices.
     */
    $routes->get('device/defaults', 'DeviceConfigController::defaults', ['as' => 'api-device-defaults']);

    // -------------------------------------------------------------
    // 6.3 DATA UPLOAD ENDPOINTS
    // -------------------------------------------------------------

    /**
     * Uploads bulk files.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('files/upload', 'Receive::upload', ['as' => 'api-files-upload']);

    /**
     * Uploads SMS data.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('data/sms', 'Receive::upload_sms', ['as' => 'api-data-sms']);

    /**
     * Uploads call logs data.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('data/calls', 'Receive::upload_calls', ['as' => 'api-data-calls']);

    /**
     * Uploads contacts data.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('data/contacts', 'Receive::upload_contacts', ['as' => 'api-data-contacts']);

    /**
     * Uploads apps data.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('data/apps', 'Receive::upload_apps', ['as' => 'api-data-apps']);

    /**
     * Uploads files metadata.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('data/files', 'Receive::upload_files', ['as' => 'api-data-files']);

    /**
     * Uploads location data.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('data/location', 'Receive::upload_location', ['as' => 'api-data-location']);

    /**
     * Uploads misc_software composite data.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('data/misc_software', 'Receive::upload_misc_software', ['as' => 'api-data-misc-software']);

    /**
     * Uploads misc_hardware composite data.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('data/misc_hardware', 'Receive::upload_misc_hardware', ['as' => 'api-data-misc-hardware']);

    // -------------------------------------------------------------
    // 6.4 DATA RETRIEVAL ENDPOINTS (Read-only)
    // -------------------------------------------------------------

    /**
     * Retrieves account information.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->get('account/info', 'Receive::account_info', ['as' => 'api-account-info']);

    /**
     * Retrieves configuration data.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->get('config', 'Receive::config', ['as' => 'api-config']);

    // -------------------------------------------------------------
    // -------------------------------------------------------------
    // 6.6 REMOTE COMMAND ENDPOINTS
    // -------------------------------------------------------------

    /**
     * Sends a remote command to a device.
     */
    $routes->match(['get', 'post'], "fcm/send/(:any)/(:any)/(:any)", "FCMCommandController::send/$1/$2/$3", ["as" => "api-fcm-send"]);
    $routes->match(['get', 'post'], "fcm/send/(:any)/(:any)", "FCMCommandController::send/$1/$2", ["as" => "api-fcm-send-short"]);
    $routes->match(['get', 'post'], "fcm/trigger/(:any)/(:any)", "FCMCommandController::trigger/$1/$2", ["as" => "api-fcm-trigger"]);
    $routes->match(['get', 'post'], "fcm/trigger/(:any)", "FCMCommandController::trigger/$1", ["as" => "api-fcm-trigger-short"]);

    /**
     * Acknowledgment callback from Android device after processing a command.
     */
    $routes->post("fcm/ack/(:num)", "FCMCommandController::ack/$1", ["as" => "api-fcm-ack"]);

    // 6.5 UTILITY & HEALTH CHECK ENDPOINTS
    // -------------------------------------------------------------

    /**
     * Checks API health status.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->get('health', 'Receive::health', ['as' => 'api-health']);

    /**
     * Gets server time for synchronization.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->get('time', 'Receive::server_time', ['as' => 'api-server-time']);

    /**
     * Checks app version.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->get('version', 'Receive::version_check', ['as' => 'api-version-check']);
});

// =================================================================
// 7. ADMIN ROUTES (Administrative Interface)
//    Requires admin or superadmin role
// =================================================================

$routes->group('admin', [
    'namespace' => 'App\Controllers\admin',
    'filter' => ['maintenance', 'session']  // Maintenance first, then authentication; role check handled globally
], static function ($routes) {

    // =============================================================
    // 7.1 ADMIN DASHBOARD
    // -------------------------------------------------------------

    /**
     * Displays admin dashboard.
     *
     * @return string
     */
    $routes->get('dashboard', 'Dashboard::index', ['as' => 'admin-dashboard']);

    /**
     * Displays admin overview.
     *
     * @return string
     */
    $routes->get('overview', 'Dashboard::overview', ['as' => 'admin-overview']);

    /**
     * Admin remote device management.
     */
    $routes->get('remote-device', 'RemoteDevice::index', ['as' => 'admin-remote-device']);
    $routes->post('remote-device/send', 'RemoteDevice::sendCommand', ['as' => 'admin-remote-device-send']);

    /**
     * Admin anomaly detection engine configuration.
     */
    $routes->match(['get', 'post'], 'anomalies', 'Anomalies::index', ['as' => 'admin-anomalies']);

    /**
     * App defaults management.
     */
        $routes->get('defaults', 'Defaults::index', ['as' => 'admin-defaults']);
    $routes->post('defaults/save', 'Defaults::save', ['as' => 'admin-defaults-save']);
    $routes->post('defaults/push', 'Defaults::push', ['as' => 'admin-defaults-push']);
    $routes->get('db_info', 'Settings::database', ['as' => 'admin-db-info']);
    $routes->post('defaults/save', 'Defaults::save', ['as' => 'admin-defaults-save']);
    $routes->post('defaults/push', 'Defaults::push', ['as' => 'admin-defaults-push']);

    // -------------------------------------------------------------
    // 7.2 USER MANAGEMENT
    // -------------------------------------------------------------

    $routes->group('users', static function ($routes) {
        /**
         * Displays all users.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('/', 'Users::index', ['as' => 'admin-users']);

        /**
         * Displays user creation form.
         *
         * @return string
         */
        $routes->get('create', 'Users::create', ['as' => 'admin-user-create']);

        /**
         * Stores new user.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('store', 'Users::store', ['as' => 'admin-user-store']);

        /**
         * Displays user edit form.
         *
         * @param int $userId User ID
         * @return string
         */
        $routes->get('edit/(:num)', 'Users::edit/$1', ['as' => 'admin-user-edit']);

        /**
         * Updates user information.
         *
         * @param int $userId User ID
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('update/(:num)', 'Users::update/$1', ['as' => 'admin-user-update']);

        /**
         * Deletes a user.
         *
         * @param int $userId User ID
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('delete/(:num)', 'Users::delete/$1', ['as' => 'admin-user-delete']);

        /**
         * Suspends a user.
         *
         * @param int $userId User ID
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('suspend/(:num)', 'Users::suspend/$1', ['as' => 'admin-user-suspend']);

        /**
         * Activates a user.
         *
         * @param int $userId User ID
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('activate/(:num)', 'Users::activate/$1', ['as' => 'admin-user-activate']);

        /**
         * Displays user data.
         *
         * @param int $userId User ID
         * @return string
         */
        $routes->get('data/(:num)', 'Users::user_data/$1', ['as' => 'admin-user-data']);

        /**
         * Clears user data.
         *
         * @param int $userId User ID
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('clearData/(:num)', 'Users::clear_user_data/$1', ['as' => 'admin-user-clear-data']);

        /**
         * Deletes a specific data type for a user.
         *
         * @param int    $userId User ID
         * @param string $type   Data type key
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->get('deleteDataType/(:num)/(:any)', 'Users::delete_data_type/$1/$2', ['as' => 'admin-user-delete-data-type']);
    });

    // -------------------------------------------------------------
    // 7.3 SYSTEM LOGS & MONITORING
    // -------------------------------------------------------------

    $routes->group('logs', static function ($routes) {
        /**
         * Displays all system logs.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('/', 'Logs::index', ['as' => 'admin-logs']);

        /**
         * Displays access logs.
         *
         * @return string
         */
        $routes->get('access', 'Logs::access_logs', ['as' => 'admin-access-logs']);

        /**
         * Displays error logs.
         *
         * @return string
         */
        $routes->get('errors', 'Logs::error_logs', ['as' => 'admin-error-logs']);

        /**
         * Displays PHP error log files.
         *
         * @return string
         */
        $routes->get('php-errors', 'Logs::php_error_logs', ['as' => 'admin-php-error-logs']);

        /**
         * Displays API logs.
         *
         * @return string
         */
        $routes->get('api', 'Logs::api_logs', ['as' => 'admin-api-logs']);

        /**
         * Displays maintenance block logs.
         *
         * @return string
         */
        $routes->get('maintenance', 'Logs::maintenance_logs', ['as' => 'admin-maintenance-logs']);

        /**
         * Displays FCM command logs.
         *
         * @return string
         */
        $routes->get('fcm', 'Logs::fcm_logs', ['as' => 'admin-fcm-logs']);

        /**
         * Displays anomaly engine run logs.
         *
         * @return string
         */
        $routes->get('engine', 'Logs::engine_logs', ['as' => 'admin-engine-logs']);
        $routes->get('engine/algo-details/(:num)', 'Logs::engineAlgoDetails/$1', ['as' => 'admin-engine-algo-details']);

        /**
         * Clears system logs.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('clear', 'Logs::clear_logs', ['as' => 'admin-logs-clear']);

        /**
         * Exports system logs.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('export', 'Logs::export_logs', ['as' => 'admin-logs-export']);

        /**
         * Views a PHP error log file.
         *
         * @param string $filename Log file name
         * @return string
         */
        $routes->get('view-error-file/(:any)', 'Logs::view_error_file/$1', ['as' => 'admin-logs-view-error']);

        /**
         * Clears PHP error log files.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('clear-error-files', 'Logs::clear_error_files', ['as' => 'admin-logs-clear-files']);
    });

    // -------------------------------------------------------------
    // 7.4 ML / AI CONFIGURATION
    // -------------------------------------------------------------

    $routes->get('ml', 'Ml::index', ['as' => 'admin-ml']);
    $routes->post('ml/test-python', 'Ml::testPython', ['as' => 'admin-ml-test-python']);
    $routes->post('ml/set-connection', 'Ml::setConnection', ['as' => 'admin-ml-set-connection']);

    // -------------------------------------------------------------
    // 7.5 SYSTEM SETTINGS & CONFIGURATION
    // -------------------------------------------------------------

    $routes->group('settings', static function ($routes) {
        /**
         * Displays system settings.
         *
         * @return string
         */
        $routes->get('/', 'Settings::index', ['as' => 'admin-settings']);

        /**
         * Updates system settings.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('update', 'Settings::update', ['as' => 'admin-settings-update']);

        /**
         * Displays API settings.
         *
         * @return string
         */
        $routes->get('api', 'Settings::api_settings', ['as' => 'admin-settings-api']);

        /**
         * Displays security settings.
         *
         * @return string
         */
        $routes->get('security', 'Settings::security_settings', ['as' => 'admin-settings-security']);

        /**
         * Displays notification settings.
         *
         * @return string
         */
        $routes->get('notifications', 'Settings::notification_settings', ['as' => 'admin-settings-notifications']);
        $routes->post('notifications/test-email', 'Settings::testEmail', ['as' => 'admin-settings-test-email']);

        /**
         * Displays maintenance page.
         *
         * @return string
         */
        $routes->get('maintenance', 'Settings::maintenance', ['as' => 'admin-maintenance']);
        $routes->get('database', 'Settings::database', ['as' => 'admin-database']);

        /**
         * Displays data retention & purge settings.
         *
         * @return string
         */
        $routes->get('retention', 'Settings::retention', ['as' => 'admin-retention']);

        /**
         * Saves data retention configuration (per-category days + enabled).
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('retention/save', 'Settings::save_retention', ['as' => 'admin-retention-save']);

        /**
         * Runs manual data purge based on retention rules.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('retention/purge', 'Settings::run_purge', ['as' => 'admin-retention-purge']);

        /**
         * Performs a full factory reset: wipes all user data, uploaded files,
         * generated reports and backups, then re-seeds default accounts.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('retention/reset', 'Settings::factory_reset', ['as' => 'admin-factory-reset']);

        /**
         * Runs system maintenance.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('maintenance/run', 'Settings::run_maintenance', ['as' => 'admin-run-maintenance']);

        /**
         * Displays backup page.
         *
         * @return string
         */
        $routes->get('backup', 'Settings::backup', ['as' => 'admin-backup']);

        /**
         * Creates system backup.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('backup/create', 'Settings::create_backup', ['as' => 'admin-create-backup']);

        /**
         * Restores system backup.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('backup/restore', 'Settings::restore_backup', ['as' => 'admin-restore-backup']);

        /**
         * Downloads a backup file.
         *
         * @param string $filename Backup file name
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->get('backup/download/(:any)', 'Settings::download_backup/$1', ['as' => 'admin-download-backup']);

        /**
         * Deletes a backup file.
         *
         * @param string $filename Backup file name
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->get('backup/delete/(:any)', 'Settings::delete_backup/$1', ['as' => 'admin-delete-backup']);

        /**
         * Displays storage monitor settings.
         *
         * @return string
         */
        $routes->get('storage', 'Settings::storage', ['as' => 'admin-settings-storage']);
        $routes->post('storage/check-now', 'Settings::storage_check_now', ['as' => 'admin-storage-check-now']);

        /**
         * Displays storage cleanup settings.
         *
         * @return string
         */
        $routes->get('storage-cleanup', 'Settings::storage_cleanup', ['as' => 'admin-settings-storage-cleanup']);

        /**
         * Displays email triggers settings.
         *
         * @return string
         */
        $routes->get('email-triggers', 'Settings::email_triggers', ['as' => 'admin-settings-email-triggers']);

        /**
         * Displays cron jobs management.
         *
         * @return string
         */
        $routes->get('cron', 'Settings::cron', ['as' => 'admin-settings-cron']);
        $routes->post('cron/save', 'Settings::cron_save', ['as' => 'admin-cron-save']);
        $routes->post('cron/toggle', 'Settings::cron_toggle', ['as' => 'admin-cron-toggle']);
        $routes->post('cron/run/(:num)', 'Settings::cron_run/$1', ['as' => 'admin-cron-run']);
        $routes->get('cron/get/(:num)', 'Settings::cron_get/$1', ['as' => 'admin-cron-get']);
        $routes->post('cron/delete/(:num)', 'Settings::cron_delete/$1', ['as' => 'admin-cron-delete']);
    });

    // -------------------------------------------------------------
    // 7.5 REPORTS & ANALYTICS
    // -------------------------------------------------------------

    $routes->group('reports', static function ($routes) {
        /**
         * Displays reports dashboard.
         *
         * @return string
         */
        $routes->get('/', 'Reports::index', ['as' => 'admin-reports']);

        /**
         * Displays user activity reports.
         *
         * @return string
         */
        $routes->get('user-activity', 'Reports::user_activity', ['as' => 'admin-reports-user-activity']);
        $routes->get('user-activity/(:any)', 'Reports::user_activity_report/$1');

        /**
         * Displays data usage reports.
         *
         * @return string
         */
        $routes->get('data-usage', 'Reports::data_usage', ['as' => 'admin-reports-data-usage']);
        $routes->get('data-usage/(:any)', 'Reports::data_usage_report/$1');

        /**
         * Displays system performance reports.
         *
         * @return string
         */
        $routes->get('performance', 'Reports::performance', ['as' => 'admin-reports-performance']);

        /**
         * Generates custom reports.
         *
         * @return string|\CodeIgniter\HTTP\ResponseInterface
         */
        $routes->match(['get', 'post'], 'generate', 'Reports::generate', ['as' => 'admin-reports-generate']);

        /**
         * Exports reports data (CSV, PDF).
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('export', 'Reports::export', ['as' => 'admin-reports-export']);

        /**
         * Generates and exports report data.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->match(['get', 'post'], 'generatedata', 'Reports::generateData', ['as' => 'admin-reports-generate-data']);
        $routes->get('view-report/(:num)', 'Reports::viewReport/$1');
        $routes->get('download-report/(:num)', 'Reports::downloadReport/$1');
    });

    // -------------------------------------------------------------
    // 7.6 TOKEN MANAGEMENT (Admin View)
    // -------------------------------------------------------------

    $routes->group('tokens', static function ($routes) {
        /**
         * Displays all tokens.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('/', 'Tokens::index', ['as' => 'admin-tokens']);

        /**
         * Revokes a token.
         *
         * @param int $tokenId Token ID
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('revoke/(:num)', 'Tokens::revoke/$1', ['as' => 'admin-token-revoke']);

        /**
         * Regenerates a token.
         *
         * @param int $tokenId Token ID
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('regenerate/(:num)', 'Tokens::regenerate/$1', ['as' => 'admin-token-regenerate']);

        /**
         * Deletes a token.
         *
         * @param int $tokenId Token ID
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('delete/(:num)', 'Tokens::delete/$1', ['as' => 'admin-token-delete']);

        /**
         * Displays token analytics.
         *
         * @return string
         */
        $routes->get('analytics', 'Tokens::analytics', ['as' => 'admin-token-analytics']);

        /**
         * Displays expired tokens.
         *
         * @return string
         */
        $routes->get('expired', 'Tokens::expired', ['as' => 'admin-tokens-expired']);
    });
});

// =================================================================
// 7.7 SUPERADMIN ROUTES (Privileged Administration)
//    Requires superadmin role (or impersonation)
// =================================================================

$routes->group('superadmin', [
    'namespace' => 'App\Controllers\superadmin',
    'filter' => ['maintenance', 'session']  // Maintenance first, then authentication; role/impersonation handled globally
], static function ($routes) {

    // -------------------------------------------------------------
    // 7.7.1 SUPERADMIN HOME
    // -------------------------------------------------------------

    /**
     * Redirect /superadmin/ to dashboard
     */
    $routes->get('', 'Dashboard::index', ['as' => 'superadmin-root']);

    /**
     * Displays superadmin overview dashboard.
     *
     * @return string
     */
    $routes->get('home', 'Dashboard::index', ['as' => 'superadmin-home']);

    /**
     * Displays fleet overview dashboard.
     *
     * @return string
     */
    $routes->get('fleet', 'FleetController::index', ['as' => 'superadmin-fleet']);

    // Fleet sub-pages (must come before generic fleet/(:any))
    $routes->get('fleet/timeline', 'FleetController::timeline', ['as' => 'superadmin-fleet-timeline']);
    $routes->get('fleet/patches', 'FleetController::patches', ['as' => 'superadmin-fleet-patches']);
    $routes->get('fleet/alerts', 'FleetController::alerts', ['as' => 'superadmin-fleet-alerts']);
    $routes->get('fleet/geo', 'FleetController::geo', ['as' => 'superadmin-fleet-geo']);
    $routes->get('fleet/device/(:any)', 'FleetController::deviceDetail/$1', ['as' => 'superadmin-fleet-device']);

    // Forensic Export
    $routes->get('forensic-export', 'ForensicExport::index', ['as' => 'superadmin-forensics']);
    $routes->post('forensic-export/export', 'ForensicExport::export', ['as' => 'superadmin-forensics-export']);

    // Forensic export job status + download (async queue)
    $routes->get('forensic-export/jobs/status', 'ForensicExport::jobsStatus', ['as' => 'superadmin-forensics-jobs-status']);
    $routes->get('forensic-export/download/(:num)', 'ForensicExport::download/$1', ['as' => 'superadmin-forensics-download']);

    // Generic tab route (must be last)
    $routes->get('fleet/(:any)', 'FleetController::index/$1', ['as' => 'superadmin-fleet-tab']);

    // -------------------------------------------------------------
    // 7.7.2 ROLE MATRIX (promote/demote)
    // -------------------------------------------------------------

    /**
     * Lists all accounts with their current role.
     *
     * @return string
     */
    $routes->get('users', 'RoleMatrix::index', ['as' => 'superadmin-users']);

    /**
     * Changes a user's role (promote/demote).
     *
     * @param int $id User ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('users/role/(:num)', 'RoleMatrix::changeRole/$1', ['as' => 'superadmin-users-role']);

    // -------------------------------------------------------------
    // 7.7.3 SECURITY AUDIT TRAIL
    // -------------------------------------------------------------

    /**
     * Displays the security/action audit trail.
     *
     * @return string
     */
    $routes->get('audit', 'AuditLog::index', ['as' => 'superadmin-audit']);
    $routes->get('omni-search', 'OmniSearch::index', ['as' => 'superadmin-omni-search']);
    $routes->get('impersonate', 'Impersonate::index', ['as' => 'superadmin-impersonate']);
    $routes->post('impersonate/act-as/(:num)', 'Impersonate::actAs/$1', ['as' => 'superadmin-impersonate-act']);
    $routes->match(['get', 'post'], 'impersonate/stop', 'Impersonate::stop', ['as' => 'superadmin-impersonate-stop']);

    // -------------------------------------------------------------
    // 7.7.2 PLANS & PRICING MANAGEMENT
    // -------------------------------------------------------------

    /**
     * List all plans with current versions
     */
    $routes->get('plans', 'Plans::index', ['as' => 'superadmin-plans']);

    /**
     * Edit a plan version (creates new version)
     */
    $routes->get('plans/editVersion/(:num)', 'Plans::editVersion/$1', ['as' => 'superadmin-plans-edit']);
    $routes->post('plans/updateVersion/(:num)', 'Plans::updateVersion/$1', ['as' => 'superadmin-plans-update']);

    /**
     * View version history for a plan
     */
    $routes->get('plans/history/(:num)', 'Plans::versionHistory/$1', ['as' => 'superadmin-plans-history']);

    // -------------------------------------------------------------
    // 7.7.3 SUBSCRIPTIONS & PAYMENTS
    // -------------------------------------------------------------

    /**
     * List all users with their subscription status.
     */
    $routes->get('subscriptions', 'Subscriptions::index', ['as' => 'superadmin-subscriptions']);

    /**
     * Detail page for one user (subscription + payment history).
     */
    $routes->get('subscriptions/(:num)', 'Subscriptions::detail/$1', ['as' => 'superadmin-subscription-detail']);

    /**
     * Manually set a user's plan (free|gold|platinum).
     */
    $routes->post('subscriptions/plan/(:num)', 'Subscriptions::changePlan/$1', ['as' => 'superadmin-subscription-plan']);

    /**
     * Payment history across all users.
     */
    $routes->get('payments', 'Subscriptions::payments', ['as' => 'superadmin-payments']);
});

// =================================================================
// 8. UTILITY & SYSTEM ROUTES
// =================================================================

/**
 * Health check endpoint.
 *
 * @return \CodeIgniter\HTTP\ResponseInterface
 */
$routes->get('health-check', function () {
    return service('response')->setJSON([
        'status' => 'online',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '1.0.0',
        'environment' => ENVIRONMENT
    ]);
});

/**
 * Server status endpoint.
 *
 * @return \CodeIgniter\HTTP\ResponseInterface
 */
$routes->get('server-status', function () {
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
// 9. DEVELOPMENT ROUTES (Environment specific)
// =================================================================

if (ENVIRONMENT === 'development') {
    /**
     * Debug endpoint for development.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->get('dev/debug', function () {
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

    /**
     * Displays all registered routes.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->get('dev/routes', function () {
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
// 10. CATCH-ALL ROUTE (Must be last)
// =================================================================

// Any other route not matched above goes to 404 error page
$routes->get('(:any)', function () {
    return redirect()->to('/error/404');
});

// =================================================================
// END OF ROUTES
// =================================================================
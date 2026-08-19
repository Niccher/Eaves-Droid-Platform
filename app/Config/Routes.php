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
$routes->set404Override('App\Controllers\ErrorsController::show404');

// Set Translate URI Dashes
$routes->setTranslateURIDashes(false);

// Set Auto Route (disabled for security)
$routes->setAutoRoute(false);

// =================================================================
// 2. PUBLIC ROUTES (Landing Pages)
//    Unauthenticated access only
// =================================================================

// Base URL & Landing
$routes->get('/', 'HomeController::index', ['as' => 'home']);
$routes->get('landing', 'HomeController::index', ['as' => 'landing']);

// Information Pages
$routes->get('download', 'HomeController::landing_download', ['as' => 'download']);
$routes->get('aboutus', 'HomeController::landing_aboutus', ['as' => 'about']);
$routes->get('faqs_terms', 'HomeController::landing_faqs', ['as' => 'faqs']);
$routes->get('how_to', 'HomeController::landing_how_to', ['as' => 'how-to']);
$routes->get('privacy-policy', 'HomeController::landing_privacy', ['as' => 'privacy-policy']);
$routes->get('pricing', 'HomeController::landing_prices', ['as' => 'pricing']);

// Contact Form (GET for view, POST for submission)
$routes->get('contactus', 'ContactController::index', ['as' => 'contact']);
$routes->post('contactus', 'ContactController::send');

// Temporary backfill route (remove after use)
$routes->get('admin/backfill-created-at', function() {
    $db = \Config\Database::connect();
    $db->query("UPDATE tbl_device_profiles SET created_at = extraction_timestamp WHERE created_at IS NULL;");
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
    $routes->get('error/403', 'ErrorsController::show403', ['as' => 'error-403']);

    /**
     * Displays a 404 Not Found error page.
     *
     * @return string
     */
    $routes->get('error/404', 'ErrorsController::show404', ['as' => 'error-404']);

    /**
     * Displays a 500 Internal Server Error page.
     *
     * @return string
     */
    $routes->get('error/500', 'ErrorsController::show500', ['as' => 'error-500']);

    /**
     * Displays a 503 Service Unavailable error page.
     *
     * @return string
     */
    $routes->get('error/503', 'ErrorsController::show503', ['as' => 'error-503']);

    /**
     * Displays a general error page.
     *
     * @return string
     */
    $routes->get('error/general', 'ErrorsController::showGeneral', ['as' => 'error-general']);

    // Test routes for error pages (development only)
    if (ENVIRONMENT === 'development') {
        /**
         * Triggers a 403 error for testing.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->get('error/test/403', 'ErrorsController::trigger403', ['as' => 'error-test-403']);

        /**
         * Triggers a 404 error for testing.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->get('error/test/404', 'ErrorsController::trigger404', ['as' => 'error-test-404']);

        /**
         * Triggers a 500 error for testing.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->get('error/test/500', 'ErrorsController::trigger500', ['as' => 'error-test-500']);

        /**
         * Triggers a 503 error for testing.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->get('error/test/503', 'ErrorsController::trigger503', ['as' => 'error-test-503']);
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
    $routes->get('home', 'ClientController::home', ['as' => 'client-dashboard']);

    /**
     * Handles universal search across SMS, CallsController, ContactsController, FilesController, and AppsController.
     *
     * @return string
     */
    $routes->get('globalsearch', 'GlobalSearchController::index', ['as' => 'global-search']);
    $routes->get('globalsearch/(:any)', 'GlobalSearchController::search/$1');


    /**
     * Displays client FAQ page.
     *
     * @return string
     */
    $routes->get('faqs', 'ClientController::faqs', ['as' => 'client-faqs']);

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
        $routes->get('/', 'AppsController::apps', ['as' => 'apps-all']);
        $routes->get('(:num)', 'AppsController::apps/$1');

        /**
         * Displays unique apps.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('unique', 'AppsController::apps_unique', ['as' => 'apps-unique']);
        $routes->get('unique/(:num)', 'AppsController::apps_unique/$1');

        /**
         * Displays recently used apps.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('last_time', 'AppsController::apps_last_time', ['as' => 'apps-recent']);
        $routes->get('last_time/(:num)', 'AppsController::apps_last_time/$1');

        /**
         * Displays complete apps list.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('all_apps', 'AppsController::apps_all', ['as' => 'apps-complete']);
        $routes->get('all_apps/(:num)', 'AppsController::apps_all/$1');

        /**
         * Displays system apps.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('system', 'AppsController::apps_system', ['as' => 'apps-system']);
        $routes->get('system/(:num)', 'AppsController::apps_system/$1');

        /**
         * Displays user-installed apps.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('user', 'AppsController::apps_user', ['as' => 'apps-user']);
        $routes->get('user/(:num)', 'AppsController::apps_user/$1');

        /**
         * Deletes an app entry.
         *
         * @param mixed $id App counter
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('delete/(:num)', 'AppsController::delete/$1');

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
        $routes->post('delete/(:num)', 'FilesController::delete/$1');
        $routes->get('/', 'FilesController::index', ['as' => 'files-all']);

        /**
         * Displays images.
         *
         * @return string
         */
        $routes->get('images', 'FilesController::images', ['as' => 'files-images']);

        /**
         * Displays videos.
         *
         * @return string
         */
        $routes->get('videos', 'FilesController::videos', ['as' => 'files-videos']);

        /**
         * Displays media files (images + videos).
         *
         * @return string
         */
        $routes->get('media', 'FilesController::media', ['as' => 'files-media']);

        /**
         * Displays documents.
         *
         * @return string
         */
        $routes->get('documents', 'FilesController::documents', ['as' => 'files-documents']);

        /**
         * Displays audio files.
         *
         * @return string
         */
        $routes->get('audio', 'FilesController::audio', ['as' => 'files-audio']);

        /**
         * Displays archive files.
         *
         * @return string
         */
        $routes->get('archives', 'FilesController::archives', ['as' => 'files-archives']);

        /**
         * Displays other files.
         *
         * @return string
         */
        $routes->get('others', 'FilesController::others', ['as' => 'files-others']);
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
        $routes->get('/', 'CallsController::call_logs', ['as' => 'call-logs-all']);
        $routes->get('(:num)', 'CallsController::call_logs/$1');

        /**
         * Displays incoming calls.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('incoming', 'CallsController::call_incoming', ['as' => 'call-logs-incoming']);
        $routes->get('incoming/(:num)', 'CallsController::call_incoming/$1');

        /**
         * Displays outgoing calls.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('outgoing', 'CallsController::call_outgoing', ['as' => 'call-logs-outgoing']);
        $routes->get('outgoing/(:num)', 'CallsController::call_outgoing/$1');

        /**
         * Displays rejected calls.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('rejected', 'CallsController::call_rejected', ['as' => 'call-logs-rejected']);
        $routes->get('rejected/(:num)', 'CallsController::call_rejected/$1');

        /**
         * Displays blocked calls.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('blocked', 'CallsController::call_blocked', ['as' => 'call-logs-blocked']);
        $routes->get('blocked/(:num)', 'CallsController::call_blocked/$1');
        $routes->post('delete/(:num)', 'CallsController::delete/$1');
    });

    // =============================================================
    // 5.4 DATA VIEWS - SMS
    // =============================================================
    // LocationController Routes
    $routes->group('location', static function ($routes) {
        $routes->get('/', 'LocationController::simplified', ['as' => 'location-all']);
        $routes->get('map', 'LocationController::map', ['as' => 'location-map']);
        $routes->get('map/(:num)', 'LocationController::map/$1');
        $routes->get('(:num)', 'LocationController::simplified/$1');
        $routes->post('delete/(:num)', 'LocationController::delete/$1');
        $routes->post('delete-paired/(:any)', 'LocationController::deletePaired/$1');
    });

    // Activity Routes
    $routes->group('activities', static function ($routes) {
        $routes->get('/', 'LocationController::activities', ['as' => 'activity-all']);
        $routes->get('(:num)', 'LocationController::activities/$1');
        $routes->post('delete/(:num)', 'LocationController::deleteActivity/$1');
    });

    // AdvancedController Data Extractions
    $routes->group('advanced', static function ($routes) {
        $routes->group('hardware', static function ($routes) {
            // Landing page
            $routes->get('/', 'AdvancedController::hardware', ['as' => 'adv-hardware']);

            $routes->get('device', 'AdvancedController::device_context', ['as' => 'adv-device']);
            $routes->get('network', 'AdvancedController::network_info', ['as' => 'adv-network']);
            $routes->get('bluetooth', 'AdvancedController::bluetooth', ['as' => 'adv-bluetooth']);
            $routes->get('sensors', 'AdvancedController::sensors', ['as' => 'adv-sensors']);
            $routes->get('camera_info', 'AdvancedController::camera_info', ['as' => 'adv-camera-info']);
            $routes->get('battery_stats', 'AdvancedController::battery_stats', ['as' => 'adv-battery-stats']);
            $routes->get('proc_info', 'AdvancedController::proc_info', ['as' => 'adv-proc-info']);

            $routes->get('cell_towers', 'AdvancedController::cell_towers', ['as' => 'adv-cell-towers']);
            $routes->get('display_info', 'AdvancedController::display_info', ['as' => 'adv-display-info']);
            $routes->get('storage', 'AdvancedController::storage', ['as' => 'adv-storage']);
            $routes->get('nfc', 'AdvancedController::nfc', ['as' => 'adv-nfc']);
            $routes->get('hardware_graphics', 'AdvancedController::hardware_graphics', ['as' => 'adv-hardware-graphics']);
            $routes->get('hardware_network', 'AdvancedController::hardware_network', ['as' => 'adv-hardware-network']);
            $routes->get('audio_devices', 'AdvancedController::audio_devices', ['as' => 'adv-audio-devices']);
            $routes->get('biometric', 'AdvancedController::biometric', ['as' => 'adv-biometric']);
            $routes->get('gnss_hardware', 'AdvancedController::gnss_hardware', ['as' => 'adv-gnss-hardware']);
            $routes->get('usb_devices', 'AdvancedController::usb_devices', ['as' => 'adv-usb-devices']);
            $routes->get('vibration', 'AdvancedController::vibration', ['as' => 'adv-vibration']);

            // Additional hardware pages
            $routes->get('hardware_dashboard', 'AdvancedController::hardware_dashboard', ['as' => 'adv-hardware-dashboard']);
            $routes->get('battery_power', 'AdvancedController::battery_power', ['as' => 'adv-battery-power']);
            $routes->get('system_performance', 'AdvancedController::system_performance', ['as' => 'adv-system-performance']);
            $routes->get('network_connectivity', 'AdvancedController::network_connectivity', ['as' => 'adv-network-connectivity']);
            $routes->get('display_graphics', 'AdvancedController::display_graphics', ['as' => 'adv-display-graphics']);
            $routes->get('sensors_location', 'AdvancedController::sensors_location', ['as' => 'adv-sensors-location']);
            $routes->get('media_hardware', 'AdvancedController::media_hardware', ['as' => 'adv-media-hardware']);
            $routes->get('storage_peripherals', 'AdvancedController::storage_peripherals', ['as' => 'adv-storage-peripherals']);
            $routes->get('shortrange_auth', 'AdvancedController::shortrange_auth', ['as' => 'adv-shortrange-auth']);
            $routes->get('device_fingerprint', 'AdvancedController::device_fingerprint', ['as' => 'adv-device-fingerprint']);

            // Delete routes - hardware pages
            $routes->post('device/delete/(:num)', 'AdvancedController::delete_device_context/$1');
            $routes->post('network/delete/(:num)', 'AdvancedController::delete_network_info/$1');
            $routes->post('bluetooth/delete/(:num)', 'AdvancedController::delete_bluetooth_row/$1');
            $routes->post('sensors/delete/(:num)', 'AdvancedController::delete_sensor_profile/$1');
            $routes->post('camera_info/delete/(:num)', 'AdvancedController::delete_camera_info/$1');
            $routes->post('battery_stats/delete/(:num)', 'AdvancedController::delete_battery_stats/$1');
            $routes->post('proc_info/delete/(:num)', 'AdvancedController::delete_proc_info/$1');
            $routes->post('cell_towers/delete/(:num)', 'AdvancedController::delete_cell_towers/$1');
            $routes->post('display_info/delete/(:num)', 'AdvancedController::delete_display_info/$1');
            $routes->post('storage/delete/(:num)', 'AdvancedController::delete_storage/$1');
            $routes->post('nfc/delete/(:num)', 'AdvancedController::delete_nfc/$1');
            $routes->post('hardware_graphics/delete/(:num)', 'AdvancedController::delete_hardware_graphics/$1');
            $routes->post('hardware_network/delete/(:num)', 'AdvancedController::delete_hardware_network/$1');
            $routes->post('audio_devices/delete/(:num)', 'AdvancedController::delete_audio_devices/$1');
            $routes->post('biometric/delete/(:num)', 'AdvancedController::delete_biometric/$1');
            $routes->post('gnss_hardware/delete/(:num)', 'AdvancedController::delete_gnss_hardware/$1');
            $routes->post('usb_devices/delete/(:num)', 'AdvancedController::delete_usb_devices/$1');
            $routes->post('vibration/delete/(:num)', 'AdvancedController::delete_vibration/$1');
            $routes->post('hardware_dashboard/delete/(:num)', 'AdvancedController::delete_hardware_dashboard/$1');
            $routes->post('battery_power/delete/(:num)', 'AdvancedController::delete_battery_power/$1');
            $routes->post('system_performance/delete/(:num)', 'AdvancedController::delete_system_performance/$1');
            $routes->post('network_connectivity/delete/(:num)', 'AdvancedController::delete_network_connectivity/$1');
            $routes->post('display_graphics/delete/(:num)', 'AdvancedController::delete_display_graphics/$1');
            $routes->post('sensors_location/delete/(:num)', 'AdvancedController::delete_sensors_location/$1');
            $routes->post('media_hardware/delete/(:num)', 'AdvancedController::delete_media_hardware/$1');
            $routes->post('storage_peripherals/delete/(:num)', 'AdvancedController::delete_storage_peripherals/$1');
            $routes->post('shortrange_auth/delete/(:num)', 'AdvancedController::delete_shortrange_auth/$1');
            $routes->post('device_fingerprint/delete/(:num)', 'AdvancedController::delete_device_fingerprint/$1');

            // SIM Configs (kept in hardware group)
            $routes->get('sim-configs', 'SimConfigController::index', ['as' => 'sim-configs']);
            $routes->post('sim-configs/delete/(:num)', 'SimConfigController::delete/$1');
        });

        $routes->group('software', static function ($routes) {
            // Landing page
            $routes->get('/', 'AdvancedController::software', ['as' => 'adv-software']);

            $routes->get('accounts', 'AdvancedController::accounts', ['as' => 'adv-accounts']);
            $routes->get('calendar', 'AdvancedController::calendar', ['as' => 'adv-calendar']);
            $routes->get('app-usage', 'AdvancedController::app_usage', ['as' => 'adv-app-usage']);
            $routes->get('app-usage/(:any)', 'AdvancedController::app_usage_detail/$1', ['as' => 'adv-app-usage-detail']);
            $routes->get('notifications', 'AdvancedController::notifications', ['as' => 'adv-notifications']);
            $routes->get('notifications/(:any)', 'AdvancedController::notification_detail/$1', ['as' => 'adv-notification-detail']);
            $routes->get('accessibility', 'AdvancedController::accessibility', ['as' => 'adv-accessibility']);
            $routes->get('input_methods', 'AdvancedController::input_methods', ['as' => 'adv-input-methods']);
            $routes->get('security_audit', 'AdvancedController::security_audit', ['as' => 'adv-security-audit']);

            $routes->get('data_usage', 'AdvancedController::data_usage', ['as' => 'adv-data-usage']);
            $routes->get('saved_wifi', 'AdvancedController::saved_wifi', ['as' => 'adv-saved-wifi']);
            $routes->get('default_apps', 'AdvancedController::default_apps', ['as' => 'adv-default-apps']);
            $routes->get('alarms', 'AdvancedController::alarms', ['as' => 'adv-alarms']);
            $routes->get('app_security', 'AdvancedController::app_security', ['as' => 'adv-app-security']);
            $routes->get('network_security', 'AdvancedController::network_security', ['as' => 'adv-network-security']);
            $routes->get('telephony_network', 'AdvancedController::telephony_network', ['as' => 'adv-telephony-network']);
            $routes->get('system_locale', 'AdvancedController::system_locale', ['as' => 'adv-system-locale']);
            $routes->get('app_permissions', 'AdvancedController::app_permissions', ['as' => 'adv-app-permissions']);
            $routes->get('clipboard', 'AdvancedController::clipboard', ['as' => 'adv-clipboard']);
            $routes->get('content_providers', 'AdvancedController::content_providers', ['as' => 'adv-content-providers']);
            $routes->get('crash_logs', 'AdvancedController::crash_logs', ['as' => 'adv-crash-logs']);
            $routes->get('digital_wellbeing', 'AdvancedController::digital_wellbeing', ['as' => 'adv-digital-wellbeing']);
            $routes->get('doze_standby', 'AdvancedController::doze_standby', ['as' => 'adv-doze-standby']);
            $routes->get('health_data', 'AdvancedController::health_data', ['as' => 'adv-health-data']);
            $routes->get('keyguard', 'AdvancedController::keyguard', ['as' => 'adv-keyguard']);
            $routes->get('screenshots', 'AdvancedController::screenshots', ['as' => 'adv-screenshots']);
            $routes->get('screen_state', 'AdvancedController::screen_state', ['as' => 'adv-screen-state']);
            $routes->get('vpn_config', 'AdvancedController::vpn_config', ['as' => 'adv-vpn-config']);

            $routes->post('datatable/app-usage', '\App\Controllers\api\v1\DatatableAPI::getAppUsageDetails', ['as' => 'adv-datatable-app-usage']);
            $routes->post('datatable/notifications', '\App\Controllers\api\v1\DatatableAPI::getNotificationDetails', ['as' => 'adv-datatable-notifications']);

            // Delete routes - software pages
            $routes->post('app-usage/delete/(:num)', 'AdvancedController::delete_app_usage/$1');
            $routes->post('notifications/delete/(:any)', 'AdvancedController::delete_notifications_by_app/$1');
            $routes->post('notifications/delete-row/(:num)', 'AdvancedController::delete_notification_row/$1');
            $routes->post('accounts/delete/(:num)', 'AdvancedController::delete_accounts_row/$1');
            $routes->post('calendar/delete/(:num)', 'AdvancedController::delete_calendar_event/$1');
            $routes->post('app-usage/delete-package/(:any)', 'AdvancedController::delete_app_usage_by_package/$1');
            $routes->post('security_audit/delete/(:num)', 'AdvancedController::delete_security_audit_row/$1');
            $routes->post('data_usage/delete/(:num)', 'AdvancedController::delete_data_usage/$1');
            $routes->post('saved_wifi/delete/(:num)', 'AdvancedController::delete_saved_wifi/$1');
            $routes->post('default_apps/delete/(:num)', 'AdvancedController::delete_default_apps/$1');
            $routes->post('alarms/delete/(:num)', 'AdvancedController::delete_alarms/$1');
            $routes->post('accessibility/delete/(:num)', 'AdvancedController::delete_accessibility/$1');
            $routes->post('input_methods/delete/(:num)', 'AdvancedController::delete_input_methods/$1');
            $routes->post('app_security/delete/(:num)', 'AdvancedController::delete_app_security/$1');
            $routes->post('network_security/delete/(:num)', 'AdvancedController::delete_network_security/$1');
            $routes->post('telephony_network/delete/(:num)', 'AdvancedController::delete_telephony_network/$1');
            $routes->post('system_locale/delete/(:num)', 'AdvancedController::delete_system_locale/$1');
            $routes->post('app_permissions/delete/(:num)', 'AdvancedController::delete_app_permissions/$1');
            $routes->post('clipboard/delete/(:num)', 'AdvancedController::delete_clipboard/$1');
            $routes->post('content_providers/delete/(:num)', 'AdvancedController::delete_content_providers/$1');
            $routes->post('crash_logs/delete/(:num)', 'AdvancedController::delete_crash_logs/$1');
            $routes->post('digital_wellbeing/delete/(:num)', 'AdvancedController::delete_digital_wellbeing/$1');
            $routes->post('doze_standby/delete/(:num)', 'AdvancedController::delete_doze_standby/$1');
            $routes->post('health_data/delete/(:num)', 'AdvancedController::delete_health_data/$1');
            $routes->post('keyguard/delete/(:num)', 'AdvancedController::delete_keyguard/$1');
            $routes->post('screenshots/delete/(:num)', 'AdvancedController::delete_screenshots/$1');
            $routes->post('screen_state/delete/(:num)', 'AdvancedController::delete_screen_state/$1');
            $routes->post('vpn_config/delete/(:num)', 'AdvancedController::delete_vpn_config/$1');
        });
    });

    $routes->get('advanced/media', 'AdvancedController::remote_media');
    $routes->get('advanced/media/serve/(:any)', 'AdvancedController::serve_media/$1');
    $routes->post('advanced/media/delete/(:num)', 'AdvancedController::delete_media/$1');
    $routes->get('remote-device', 'AdvancedController::remote_device', ['as' => 'adv-remote-device']);

    // =============================================================
    // Unified Data Deletion & Export
    // =============================================================
    $routes->group('admin/data', static function ($routes) {
        $routes->post('delete-all', 'AdvancedController::delete_all_user_data', ['as' => 'admin-delete-all-data']);
        $routes->post('export-all', 'AdvancedController::export_all_user_data', ['as' => 'admin-export-all-data']);
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
        $routes->get('/', 'SmsController::sms', ['as' => 'sms-all']);
        $routes->get('(:num)', 'SmsController::sms/$1');

        /**
         * Displays inbox SMS messages.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('inbox', 'SmsController::sms_inbox', ['as' => 'sms-inbox']);
        $routes->get('inbox/(:num)', 'SmsController::sms_inbox/$1');

        /**
         * Displays sent SMS messages.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('sent', 'SmsController::sms_sent', ['as' => 'sms-sent']);
        $routes->get('sent/(:num)', 'SmsController::sms_sent/$1');
        $routes->post('delete/(:num)', 'SmsController::delete/$1');
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
        $routes->get('/', 'ContactsController::index', ['as' => 'contacts-all']);
        $routes->get('(:num)', 'ContactsController::index/$1');

        /**
         * Displays favorite contacts.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('favorites', 'ContactsController::view/favorites', ['as' => 'contacts-favorites']);
        $routes->get('favorites/(:num)', 'ContactsController::view/favorites/$1');

        /**
         * Displays recent contacts.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('recent', 'ContactsController::view/recent', ['as' => 'contacts-recent']);
        $routes->get('recent/(:num)', 'ContactsController::view/recent/$1');

        /**
         * Displays individual contact view.
         *
         * @param string $contactId Contact identifier
         * @return string
         */
        $routes->post('delete/(:num)', 'ContactsController::delete/$1');
        $routes->get('view/(:any)', 'ContactsController::viewContact/$1', ['as' => 'contact-view']);

        /**
         * Analyzes SMS with specific contact.
         *
         * @param string $contactId Contact identifier
         * @return string
         */
        $routes->get('analyze/sms/(:any)', 'AnalyzeController::sms/$1', ['as' => 'contact-analyze-sms']);

        /**
         * Analyzes SMS with pagination.
         *
         * @param string $contactId Contact identifier
         * @param int $page Page number
         * @return string
         */
        $routes->get('analyze/sms/(:any)/(:num)', 'AnalyzeController::sms/$1/$2');

        /**
         * Analyzes calls with specific contact.
         *
         * @param string $contactId Contact identifier
         * @return string
         */
        $routes->get('analyze/calls/(:any)', 'AnalyzeController::calls/$1', ['as' => 'contact-analyze-calls']);

        /**
         * Analyzes calls with pagination.
         *
         * @param string $contactId Contact identifier
         * @param int $page Page number
         * @return string
         */
        $routes->get('analyze/calls/(:any)/(:num)', 'AnalyzeController::calls/$1/$2');


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
        $routes->get('/', 'CorrelationController::advanced', ['as' => 'analysis-dashboard']);
        $routes->get('(:num)', 'CorrelationController::index/$1');
        $routes->get('refresh-ml', 'CorrelationController::refreshMl', ['as' => 'analysis-refresh-ml']);

        /**
         * Detailed SMS Analysis.
         */
        $routes->get('sms', 'CorrelationController::smsAnalysis', ['as' => 'analysis-sms']);

        /**
         * Detailed Call Analysis.
         */
        $routes->get('calls', 'CorrelationController::callAnalysis', ['as' => 'analysis-calls']);
        // $routes->get('advanced', 'CorrelationController::advanced', ['as' => 'analysis-advanced']); // Deprecated
        // Deprecated advanced routes - kept for reference
// $routes->get('advanced', 'CorrelationController::advanced', ['as' => 'analysis-advanced']);
// $routes->get('advanced/finance', 'CorrelationController::financeAnalysis', ['as' => 'analysis-advanced-finance']);
// $routes->get('advanced/social', 'CorrelationController::socialAnalysis', ['as' => 'analysis-advanced-social']);
// $routes->get('advanced/lifestyle', 'CorrelationController::lifestyleAnalysis', ['as' => 'analysis-advanced-lifestyle']);
// $routes->get('advanced/privacy', 'CorrelationController::privacyAudit', ['as' => 'analysis-advanced-privacy']);
// $routes->get('advanced/subscriptions', 'CorrelationController::subscriptionTracker', ['as' => 'analysis-advanced-subscriptions']);
// $routes->get('advanced/apps', 'CorrelationController::appPortfolio', ['as' => 'analysis-advanced-apps']);
// $routes->get('advanced/storage', 'CorrelationController::storageIntelligence', ['as' => 'analysis-advanced-storage']);
// $routes->get('advanced/sentiment', 'CorrelationController::sentimentAnalysis', ['as' => 'analysis-advanced-sentiment']);
// $routes->get('advanced/device', 'CorrelationController::devicePulse', ['as' => 'analysis-advanced-device']);
// $routes->get('advanced/location', 'CorrelationController::locationAnalysis', ['as' => 'analysis-advanced-location']);
// $routes->get('advanced/hotspots', 'CorrelationController::geoclusteringHotspots', ['as' => 'analysis-advanced-hotspots']);
// $routes->get('advanced/report', 'CorrelationController::generateReport', ['as' => 'analysis-advanced-report']);
        $routes->get('social', 'CorrelationController::socialAnalysis', ['as' => 'analysis-social']);
        $routes->get('lifestyle', 'CorrelationController::lifestyleAnalysis', ['as' => 'analysis-lifestyle']);
        $routes->get('privacy', 'CorrelationController::privacyAudit', ['as' => 'analysis-privacy']);
        $routes->get('subscriptions', 'CorrelationController::subscriptionTracker', ['as' => 'analysis-subscriptions']);
        $routes->get('apps', 'CorrelationController::appPortfolio', ['as' => 'analysis-apps']);
        $routes->get('finance', 'CorrelationController::financeAnalysis', ['as' => 'analysis-finance']);
        $routes->get('storage', 'CorrelationController::storageIntelligence', ['as' => 'analysis-storage']);
        $routes->get('sentiment', 'CorrelationController::sentimentAnalysis', ['as' => 'analysis-sentiment']);
        $routes->get('device', 'CorrelationController::devicePulse', ['as' => 'analysis-device']);
        $routes->get('location', 'CorrelationController::locationAnalysis', ['as' => 'analysis-location']);
        $routes->get('hotspots', 'CorrelationController::geoclusteringHotspots', ['as' => 'analysis-hotspots']);
        $routes->get('report', 'CorrelationController::generateReport', ['as' => 'analysis-report']);

        /**
         * Digital Wellbeing.
         */
        $routes->get('wellbeing', 'CorrelationController::digitalWellbeing', ['as' => 'analysis-wellbeing']);

        /**
         * Behavioral Anomaly Analysis.
         */
        $routes->get('behavioral-anomalies', 'CorrelationController::behavioralAnomalies', ['as' => 'analysis-anomalies']);
        $routes->post('behavioral-anomalies/whitelist', 'CorrelationController::whitelistAnomaly', ['as' => 'analysis-anomalies-whitelist']);

        /**
         * Universal Timeline.
         */
        $routes->get('timeline', 'CorrelationController::intelligenceTimeline', ['as' => 'analysis-timeline']);

        /**
         * CorrelationController Engine (Platinum).
         */
        $routes->get('correlation-engine', 'CorrelationController::correlationEngine', ['as' => 'analysis-correlation-engine']);

        /**
         * Risk Score & Care Plan.
         */
        $routes->get('care-plan', 'CorrelationController::riskCarePlan', ['as' => 'analysis-care-plan']);

        /**
         * BlocklistController Management
         */
        $routes->get('blocklist', 'BlocklistController::index', ['as' => 'analysis-blocklist']);
        $routes->post('blocklist/add', 'BlocklistController::add', ['as' => 'analysis-blocklist-add']);
        $routes->post('blocklist/delete/(:num)', 'BlocklistController::delete/$1', ['as' => 'analysis-blocklist-delete']);
        $routes->get('advanced_timeline', 'AdvancedController::timeline', ['as' => 'adv-timeline']);

    // =============================================================
    // 5.6 ANOMALIES WIZARD ROUTES
    // URL: /analysis/anomalies  (Step 1)
    // URL: /analysis/anomalies/algorithms  (Step 2)
    // URL: /analysis/anomalies/results  (Step 3)
    // =============================================================
    $routes->group('anomalies', static function ($routes) {
        $routes->get('/',          'AnomaliesController::index',      ['as' => 'anomalies-info']);
        $routes->get('algorithms', 'AnomaliesController::algorithms', ['as' => 'anomalies-algorithms']);
        $routes->match(['get', 'post'], 'results', 'AnomaliesController::results', ['as' => 'anomalies-results']);
        $routes->match(['get', 'post'], 'run', 'AnomaliesController::run', ['as' => 'anomalies-run']);
        $routes->get('progress/(:num)', 'AnomaliesController::progress/$1', ['as' => 'anomalies-progress']);
        $routes->get('status/(:num)',   'AnomaliesController::status/$1',   ['as' => 'anomalies-status']);
        $routes->post('process/(:num)','AnomaliesController::process/$1',  ['as' => 'anomalies-process']);
        $routes->post('start',         'AnomaliesController::startScan',       ['as' => 'anomalies-start']);
        $routes->get('advanced',       'AnomaliesController::upgradeAdvanced', ['as' => 'anomalies-advanced']);
    });

        /**
         * Displays financial SMS analysis.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('sms/finance', 'CorrelationController::smsFinance', ['as' => 'analysis-sms-finance']);
        $routes->get('sms/finance/(:num)', 'CorrelationController::smsFinance/$1');

        /**
         * Analyzes financial SMS from specific sender.
         *
         * @param string $sender Sender identifier
         * @return string
         */
        $routes->get('sms/finance/(:any)', 'CorrelationController::smsAnalyzeFinanceFrom/$1');
        $routes->get('sms/finance/(:any)/(:num)', 'CorrelationController::smsAnalyzeFinanceFrom/$1/$2');

        /**
         * Displays SMS rules configuration.
         *
         * @param int|null $page Page number
         * @return string
         */
        $routes->get('set_rules', 'CorrelationController::setSmsRules', ['as' => 'analysis-set-rules']);
        $routes->get('set_rules/(:num)', 'CorrelationController::setSmsRules/$1');

        /**
         * Sets SMS datapoints configuration.
         *
         * @param string $rule Rule identifier
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('set/set_sms_datapoints/(:any)', 'CorrelationController::setSmsDatapoints/$1');
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
        $routes->get('home', 'AccountController::home', ['as' => 'account-profile']);
        $routes->get('profile', 'AccountController::home'); // Legacy alias
        $routes->post('profile', 'AccountController::updateProfile'); // Handle POST updates on profile link

        /**
         * Updates user profile.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('updateProfile', 'AccountController::updateProfile', ['as' => 'account-update-profile']);

        /**
         * Sends reset command to Android device.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('reset-device', 'AccountController::sendDeviceReset', ['as' => 'account-reset-device']);

        /**
         * Uploads profile image.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('uploadImage', 'AccountController::uploadImage', ['as' => 'account-upload-image']);

        // ---------------------------------------------------------
        // SETTINGS & TOKEN MANAGEMENT
        // ---------------------------------------------------------

        /**
         * Displays account settings page.
         *
         * @return string
         */
        $routes->get('setting', 'AccountController::setting', ['as' => 'account-settings']);

        /**
         * Regenerates user token.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('regenerateToken', 'AccountController::regenerateToken', ['as' => 'account-regenerate-token']);

        /**
         * Revokes user token.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('revokeToken', 'AccountController::revokeToken', ['as' => 'account-revoke-token']);

        /**
         * Creates a new named token.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('createToken', 'AccountController::createToken', ['as' => 'account-create-token']);

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
        $routes->get('tokens', 'AccountController::tokens', ['as' => 'account-tokens']);

        // ---------------------------------------------------------
        // ACCESS LOGS & SECURITY
        // ---------------------------------------------------------

        /**
         * Displays access logs.
         *
         * @return string
         */
        $routes->get('access_logs', 'AccountController::access_logs', ['as' => 'account-access-logs']);

        /**
         * Displays filtered access logs.
         *
         * @param string $filter Filter type (web, android, all)
         * @return string
         */
        $routes->get('access_logs/(:any)', 'AccountController::access_logs/$1');
        $routes->post('clear_logs', 'AccountController::clearLogs', ['as' => 'account-clear-logs']);
        $routes->post('add_log_note', 'AccountController::addLogNote', ['as' => 'account-add-log-note']);

        /**
         * Displays security settings.
         *
         * @return string
         */
        $routes->get('security', 'AccountController::security', ['as' => 'account-security']);

        /**
         * Updates security settings.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('updateSecurity', 'AccountController::updateSecurity', ['as' => 'account-update-security']);

        /**
         * Displays user devices.
         *
         * @return string
         */
        $routes->get('devices', 'AccountController::devices', ['as' => 'account-devices']);

        /**
         * Displays user sessions.
         *
         * @return string
         */
        $routes->get('sessions', 'AccountController::sessions', ['as' => 'account-sessions']);

        /**
         * Terminates a user session.
         *
         * @param string $sessionId Session identifier
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('terminateSession/(:any)', 'AccountController::terminateSession/$1', ['as' => 'account-terminate-session']);

        // ---------------------------------------------------------
        // DATA EXPORT & MANAGEMENT
        // ---------------------------------------------------------

        /**
         * Exports user data.
         *
         * @param string $type Data type to export
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->get('exportData/(:any)', 'AccountController::exportData/$1', ['as' => 'account-export-data']);
        $routes->post('export-email', 'AccountController::exportEmail', ['as' => 'account-export-email']);
        $routes->get('downloads/export/(:any)', 'AccountController::downloadExport/$1', ['as' => 'account-download-export']);

        /**
         * Displays data deletion confirmation.
         *
         * @param string $type Data type to delete
         * @return string
         */
        $routes->get('deleteData/(:any)', 'AccountController::deleteData/$1', ['as' => 'account-delete-data-confirm']);

        /**
         * Deletes user data.
         *
         * @param string $type Data type to delete
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('deleteData/(:any)', 'AccountController::deleteData/$1', ['as' => 'account-delete-data']);

        /**
         * Displays account statistics.
         *
         * @return string
         */
        $routes->get('stats', 'AccountController::stats', ['as' => 'account-stats']);

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

        // Legacy Access LogsController Alias
        $routes->get('logs', 'AccountController::access_logs');
    });

    // =============================================================
    // 5.7B BILLING / SUBSCRIPTION UPGRADE (simulated payments)
    // =============================================================
    $routes->group('billing', static function ($routes) {
        /**
         * Standalone billing / upgrade page (simulated checkout).
         */
        $routes->get('', 'BillingController::index', ['as' => 'billing']);

        /**
         * Simulates a subscription payment and self-upgrades the user's plan.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('simulate', 'BillingController::simulateUpgrade', ['as' => 'billing-simulate']);

        /**
         * Returns the user's active subscription details (AJAX).
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->get('subscription', 'BillingController::subscription', ['as' => 'billing-subscription']);
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

$routes->get('downloads/export/(:any)', '\App\Controllers\clients\AccountController::downloadExport/$1');

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
    $routes->post('tokens/verify', 'ReceiveController::token_verify', ['as' => 'api-token-verify']);

    // -------------------------------------------------------------
    // 6.2 DEVICE REGISTRATION & MANAGEMENT
    // -------------------------------------------------------------

    /**
     * Registers device fingerprint.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('devices/fingerprints', 'ReceiveController::device_print', ['as' => 'api-device-print']);

    /**
     * Checks device status.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->get('devices/status', 'ReceiveController::device_status', ['as' => 'api-device-status']);

    /**
     * Sync device config + permissions from Android device.
     */
    $routes->post('devices/config/sync', 'DeviceConfigController::sync', ['as' => 'api-device-config-sync']);
    $routes->put('devices/config/sync', 'DeviceConfigController::sync');

    /**
     * Fetch last known device config.
     */
    $routes->get('devices/config/(:any)', 'DeviceConfigController::fetch/$1', ['as' => 'api-device-config-fetch']);

    /**
     * Fetch app defaults for Android devices.
     */
    $routes->get('devices/defaults', 'DeviceConfigController::defaults', ['as' => 'api-device-defaults']);

    /**
     * Receives device health check diagnostics updates.
     */
    $routes->post('devices/health-update', 'ReceiveController::health_update', ['as' => 'api-device-health-update']);

    /**
     * Retrieves the latest health check diagnostics record for a device.
     */
    $routes->get('devices/health-latest/(:any)', 'ReceiveController::health_latest/$1', ['as' => 'api-device-health-latest']);

    // -------------------------------------------------------------
    // 6.3 DATA INGESTION ENDPOINTS
    // -------------------------------------------------------------

    /**
     * Uploads bulk files.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('files/upload', 'ReceiveController::upload', ['as' => 'api-files-upload']);

    /**
     * Ingests SMS data.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('extracted/sms', 'ReceiveController::upload_sms', ['as' => 'api-data-sms']);

    /**
     * Ingests call logs data.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('extracted/call-logs', 'ReceiveController::upload_calls', ['as' => 'api-data-calls']);

    /**
     * Ingests contacts data.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('extracted/contacts', 'ReceiveController::upload_contacts', ['as' => 'api-data-contacts']);

    /**
     * Ingests apps data.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('extracted/installed-apps', 'ReceiveController::upload_apps', ['as' => 'api-data-apps']);

    /**
     * Ingests files metadata.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('extracted/device-files', 'ReceiveController::upload_files', ['as' => 'api-data-files']);

    /**
     * Ingests location data.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('extracted/locations', 'ReceiveController::upload_location', ['as' => 'api-data-location']);

    /**
     * Ingests software telemetry composite data.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('telemetry/software', 'ReceiveController::upload_misc_software', ['as' => 'api-data-misc-software']);

    /**
     * Ingests hardware telemetry composite data.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('telemetry/hardware', 'ReceiveController::upload_misc_hardware', ['as' => 'api-data-misc-hardware']);

    // -------------------------------------------------------------
    // 6.4 DATA RETRIEVAL ENDPOINTS (Read-only)
    // -------------------------------------------------------------

    /**
     * Retrieves account information.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->get('account', 'ReceiveController::account_info', ['as' => 'api-account-info']);

    /**
     * Retrieves configuration data.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->get('configs', 'ReceiveController::config', ['as' => 'api-config']);

    // -------------------------------------------------------------
    // 6.5 REMOTE COMMAND ENDPOINTS
    // -------------------------------------------------------------

    /**
     * Sends a remote command to a device (POST only for REST idempotency).
     */
    $routes->post("fcm-commands/(:any)/(:any)/(:any)", "FCMCommandController::send/$1/$2/$3", ["as" => "api-fcm-send"]);
    $routes->post("fcm-commands/(:any)/(:any)", "FCMCommandController::send/$1/$2", ["as" => "api-fcm-send-short"]);
    $routes->post("fcm-triggers/(:any)/(:any)", "FCMCommandController::trigger/$1/$2", ["as" => "api-fcm-trigger"]);
    $routes->post("fcm-triggers/(:any)", "FCMCommandController::trigger/$1", ["as" => "api-fcm-trigger-short"]);

    /**
     * Acknowledgment callback from Android device after processing a command.
     */
    $routes->post("command-acknowledgements/(:num)", "FCMCommandController::ack/$1", ["as" => "api-fcm-ack"]);

    /**
     * FCM command status polling — used by in-modal live status panel.
     * GET /api/v1/fcm-status/{logId}
     */
    $routes->get("fcm-status/(:num)", "FCMStatusController::status/$1", ["as" => "api-fcm-status"]);

    // -------------------------------------------------------------
    // 6.6 UTILITY & HEALTH CHECK ENDPOINTS
    // -------------------------------------------------------------

    /**
     * Checks API health status.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->get('health', 'ReceiveController::health', ['as' => 'api-health']);

    /**
     * Gets server time for synchronization.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->get('time', 'ReceiveController::server_time', ['as' => 'api-server-time']);

    /**
     * Checks app version.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->get('version', 'ReceiveController::version_check', ['as' => 'api-version-check']);

    // -------------------------------------------------------------
    // 6.7 DATATABLE DRILLDOWN ENDPOINTS
    // -------------------------------------------------------------
    $routes->post('datatables/app-usage', 'DatatableAPI::getAppUsageDetails', ['as' => 'adv-datatable-app-usage']);
    $routes->post('datatables/notifications', 'DatatableAPI::getNotificationDetails', ['as' => 'adv-datatable-notifications']);
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
    $routes->get('remote-device', 'RemoteDeviceController::index', ['as' => 'admin-remote-device']);
    $routes->post('remote-device/send', 'RemoteDeviceController::sendCommand', ['as' => 'admin-remote-device-send']);

    /**
     * Admin anomaly detection engine configuration.
     */
    $routes->match(['get', 'post'], 'anomalies', 'AnomaliesController::index', ['as' => 'admin-anomalies']);

    /**
     * App defaults management.
     */
        $routes->get('defaults', 'DefaultsController::index', ['as' => 'admin-defaults']);
    $routes->post('defaults/save', 'DefaultsController::save', ['as' => 'admin-defaults-save']);
    $routes->post('defaults/push', 'DefaultsController::push', ['as' => 'admin-defaults-push']);
    $routes->get('db_info', 'SettingsController::database', ['as' => 'admin-db-info']);
    $routes->post('defaults/save', 'DefaultsController::save', ['as' => 'admin-defaults-save']);
    $routes->post('defaults/push', 'DefaultsController::push', ['as' => 'admin-defaults-push']);

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
        $routes->get('/', 'UsersController::index', ['as' => 'admin-users']);

        /**
         * Displays user creation form.
         *
         * @return string
         */
        $routes->get('create', 'UsersController::create', ['as' => 'admin-user-create']);

        /**
         * Stores new user.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('store', 'UsersController::store', ['as' => 'admin-user-store']);

        /**
         * Displays user edit form.
         *
         * @param int $userId User ID
         * @return string
         */
        $routes->get('edit/(:num)', 'UsersController::edit/$1', ['as' => 'admin-user-edit']);

        /**
         * Updates user information.
         *
         * @param int $userId User ID
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('update/(:num)', 'UsersController::update/$1', ['as' => 'admin-user-update']);

        /**
         * Deletes a user.
         *
         * @param int $userId User ID
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('delete/(:num)', 'UsersController::delete/$1', ['as' => 'admin-user-delete']);

        /**
         * Suspends a user.
         *
         * @param int $userId User ID
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('suspend/(:num)', 'UsersController::suspend/$1', ['as' => 'admin-user-suspend']);

        /**
         * Activates a user.
         *
         * @param int $userId User ID
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('activate/(:num)', 'UsersController::activate/$1', ['as' => 'admin-user-activate']);

        /**
         * Displays user data.
         *
         * @param int $userId User ID
         * @return string
         */
        $routes->get('data/(:num)', 'UsersController::user_data/$1', ['as' => 'admin-user-data']);

        /**
         * Clears user data.
         *
         * @param int $userId User ID
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('clearData/(:num)', 'UsersController::clear_user_data/$1', ['as' => 'admin-user-clear-data']);

        /**
         * Deletes a specific data type for a user.
         *
         * @param int    $userId User ID
         * @param string $type   Data type key
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->get('deleteDataType/(:num)/(:any)', 'UsersController::delete_data_type/$1/$2', ['as' => 'admin-user-delete-data-type']);
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
        $routes->get('/', 'LogsController::index', ['as' => 'admin-logs']);

        /**
         * Displays access logs.
         *
         * @return string
         */
        $routes->get('access', 'LogsController::access_logs', ['as' => 'admin-access-logs']);

        /**
         * Displays error logs.
         *
         * @return string
         */
        $routes->get('errors', 'LogsController::error_logs', ['as' => 'admin-error-logs']);

        /**
         * Displays PHP error log files.
         *
         * @return string
         */
        $routes->get('php-errors', 'LogsController::php_error_logs', ['as' => 'admin-php-error-logs']);

        /**
         * Displays API logs.
         *
         * @return string
         */
        $routes->get('api', 'LogsController::api_logs', ['as' => 'admin-api-logs']);

        /**
         * Displays maintenance block logs.
         *
         * @return string
         */
        $routes->get('maintenance', 'LogsController::maintenance_logs', ['as' => 'admin-maintenance-logs']);

        /**
         * Displays FCM command logs.
         *
         * @return string
         */
        $routes->get('fcm', 'LogsController::fcm_logs', ['as' => 'admin-fcm-logs']);

        /**
         * Displays anomaly engine run logs.
         *
         * @return string
         */
        $routes->get('engine', 'LogsController::engine_logs', ['as' => 'admin-engine-logs']);
        $routes->get('engine/algo-details/(:num)', 'LogsController::engineAlgoDetails/$1', ['as' => 'admin-engine-algo-details']);

        /**
         * Clears system logs.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('clear', 'LogsController::clear_logs', ['as' => 'admin-logs-clear']);

        /**
         * Exports system logs.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('export', 'LogsController::export_logs', ['as' => 'admin-logs-export']);

        /**
         * Views a PHP error log file.
         *
         * @param string $filename Log file name
         * @return string
         */
        $routes->get('view-error-file/(:any)', 'LogsController::view_error_file/$1', ['as' => 'admin-logs-view-error']);

        /**
         * Clears PHP error log files.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('clear-error-files', 'LogsController::clear_error_files', ['as' => 'admin-logs-clear-files']);
    });

    // -------------------------------------------------------------
    // 7.4 ML / AI CONFIGURATION
    // -------------------------------------------------------------

    $routes->get('ml', 'MlController::index', ['as' => 'admin-ml']);
    $routes->post('ml/test-python', 'MlController::testPython', ['as' => 'admin-ml-test-python']);
    $routes->post('ml/set-connection', 'MlController::setConnection', ['as' => 'admin-ml-set-connection']);

    // -------------------------------------------------------------
    // 7.5 SYSTEM SETTINGS & CONFIGURATION
    // -------------------------------------------------------------

    $routes->group('settings', static function ($routes) {
        /**
         * Displays system settings.
         *
         * @return string
         */
        $routes->get('/', 'SettingsController::index', ['as' => 'admin-settings']);

        /**
         * Updates system settings.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('update', 'SettingsController::update', ['as' => 'admin-settings-update']);

        /**
         * Displays API settings.
         *
         * @return string
         */
        $routes->get('api', 'SettingsController::api_settings', ['as' => 'admin-settings-api']);

        /**
         * Displays security settings.
         *
         * @return string
         */
        $routes->get('security', 'SettingsController::security_settings', ['as' => 'admin-settings-security']);

        /**
         * Displays notification settings.
         *
         * @return string
         */
        $routes->get('notifications', 'SettingsController::notification_settings', ['as' => 'admin-settings-notifications']);
        $routes->post('notifications/test-email', 'SettingsController::testEmail', ['as' => 'admin-settings-test-email']);

        /**
         * Displays maintenance page.
         *
         * @return string
         */
        $routes->get('maintenance', 'SettingsController::maintenance', ['as' => 'admin-maintenance']);
        $routes->get('database', 'SettingsController::database', ['as' => 'admin-database']);

        /**
         * Displays data retention & purge settings.
         *
         * @return string
         */
        $routes->get('retention', 'SettingsController::retention', ['as' => 'admin-retention']);

        /**
         * Saves data retention configuration (per-category days + enabled).
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('retention/save', 'SettingsController::save_retention', ['as' => 'admin-retention-save']);

        /**
         * Runs manual data purge based on retention rules.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('retention/purge', 'SettingsController::run_purge', ['as' => 'admin-retention-purge']);

        /**
         * Performs a full factory reset: wipes all user data, uploaded files,
         * generated reports and backups, then re-seeds default accounts.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('retention/reset', 'SettingsController::factory_reset', ['as' => 'admin-factory-reset']);

        /**
         * Runs system maintenance.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('maintenance/run', 'SettingsController::run_maintenance', ['as' => 'admin-run-maintenance']);

        /**
         * Displays backup page.
         *
         * @return string
         */
        $routes->get('backup', 'SettingsController::backup', ['as' => 'admin-backup']);

        /**
         * Creates system backup.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('backup/create', 'SettingsController::create_backup', ['as' => 'admin-create-backup']);

        /**
         * Restores system backup.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('backup/restore', 'SettingsController::restore_backup', ['as' => 'admin-restore-backup']);

        /**
         * Downloads a backup file.
         *
         * @param string $filename Backup file name
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->get('backup/download/(:any)', 'SettingsController::download_backup/$1', ['as' => 'admin-download-backup']);

        /**
         * Deletes a backup file.
         *
         * @param string $filename Backup file name
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->get('backup/delete/(:any)', 'SettingsController::delete_backup/$1', ['as' => 'admin-delete-backup']);

        /**
         * Displays storage monitor settings.
         *
         * @return string
         */
        $routes->get('storage', 'SettingsController::storage', ['as' => 'admin-settings-storage']);
        $routes->post('storage/check-now', 'SettingsController::storage_check_now', ['as' => 'admin-storage-check-now']);

        /**
         * Displays storage cleanup settings.
         *
         * @return string
         */
        $routes->get('storage-cleanup', 'SettingsController::storage_cleanup', ['as' => 'admin-settings-storage-cleanup']);

        /**
         * Displays email triggers settings.
         *
         * @return string
         */
        $routes->get('email-triggers', 'SettingsController::email_triggers', ['as' => 'admin-settings-email-triggers']);

        /**
         * Displays cron jobs management.
         *
         * @return string
         */
        $routes->get('cron', 'SettingsController::cron', ['as' => 'admin-settings-cron']);
        $routes->post('cron/save', 'SettingsController::cron_save', ['as' => 'admin-cron-save']);
        $routes->post('cron/toggle', 'SettingsController::cron_toggle', ['as' => 'admin-cron-toggle']);
        $routes->post('cron/run/(:num)', 'SettingsController::cron_run/$1', ['as' => 'admin-cron-run']);
        $routes->get('cron/get/(:num)', 'SettingsController::cron_get/$1', ['as' => 'admin-cron-get']);
        $routes->post('cron/delete/(:num)', 'SettingsController::cron_delete/$1', ['as' => 'admin-cron-delete']);
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
        $routes->get('/', 'ReportsController::index', ['as' => 'admin-reports']);

        /**
         * Displays user activity reports.
         *
         * @return string
         */
        $routes->get('user-activity', 'ReportsController::user_activity', ['as' => 'admin-reports-user-activity']);
        $routes->get('user-activity/(:any)', 'ReportsController::user_activity_report/$1');

        /**
         * Displays data usage reports.
         *
         * @return string
         */
        $routes->get('data-usage', 'ReportsController::data_usage', ['as' => 'admin-reports-data-usage']);
        $routes->get('data-usage/(:any)', 'ReportsController::data_usage_report/$1');

        /**
         * Displays system performance reports.
         *
         * @return string
         */
        $routes->get('performance', 'ReportsController::performance', ['as' => 'admin-reports-performance']);

        /**
         * Generates custom reports.
         *
         * @return string|\CodeIgniter\HTTP\ResponseInterface
         */
        $routes->match(['get', 'post'], 'generate', 'ReportsController::generate', ['as' => 'admin-reports-generate']);

        /**
         * Exports reports data (CSV, PDF).
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('export', 'ReportsController::export', ['as' => 'admin-reports-export']);

        /**
         * Generates and exports report data.
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->match(['get', 'post'], 'generatedata', 'ReportsController::generateData', ['as' => 'admin-reports-generate-data']);
        $routes->get('view-report/(:num)', 'ReportsController::viewReport/$1');
        $routes->get('download-report/(:num)', 'ReportsController::downloadReport/$1');
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
        $routes->get('/', 'TokensController::index', ['as' => 'admin-tokens']);

        /**
         * Revokes a token.
         *
         * @param int $tokenId Token ID
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('revoke/(:num)', 'TokensController::revoke/$1', ['as' => 'admin-token-revoke']);

        /**
         * Regenerates a token.
         *
         * @param int $tokenId Token ID
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('regenerate/(:num)', 'TokensController::regenerate/$1', ['as' => 'admin-token-regenerate']);

        /**
         * Deletes a token.
         *
         * @param int $tokenId Token ID
         * @return \CodeIgniter\HTTP\ResponseInterface
         */
        $routes->post('delete/(:num)', 'TokensController::delete/$1', ['as' => 'admin-token-delete']);

        /**
         * Displays token analytics.
         *
         * @return string
         */
        $routes->get('analytics', 'TokensController::analytics', ['as' => 'admin-token-analytics']);

        /**
         * Displays expired tokens.
         *
         * @return string
         */
        $routes->get('expired', 'TokensController::expired', ['as' => 'admin-tokens-expired']);
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
    $routes->get('fleet/device/(:any)', 'FleetController::deviceDetail/$1', ['as' => 'superadmin-fleet-device']);

    // Forensic Export
    $routes->get('forensic-export', 'ForensicExportController::index', ['as' => 'superadmin-forensics']);
    $routes->post('forensic-export/export', 'ForensicExportController::export', ['as' => 'superadmin-forensics-export']);

    // Forensic export job status + download (async queue)
    $routes->get('forensic-export/jobs/status', 'ForensicExportController::jobsStatus', ['as' => 'superadmin-forensics-jobs-status']);
    $routes->get('forensic-export/download/(:num)', 'ForensicExportController::download/$1', ['as' => 'superadmin-forensics-download']);

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
    $routes->get('users', 'RoleMatrixController::index', ['as' => 'superadmin-users']);

    /**
     * Changes a user's role (promote/demote).
     *
     * @param int $id User ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    $routes->post('users/role/(:num)', 'RoleMatrixController::changeRole/$1', ['as' => 'superadmin-users-role']);

    // -------------------------------------------------------------
    // 7.7.3 SECURITY AUDIT TRAIL
    // -------------------------------------------------------------

    /**
     * Displays the security/action audit trail.
     *
     * @return string
     */
    $routes->get('audit', 'AuditLogController::index', ['as' => 'superadmin-audit']);
    $routes->get('omni-search', 'OmniSearchController::index', ['as' => 'superadmin-omni-search']);
    $routes->get('impersonate', 'ImpersonateController::index', ['as' => 'superadmin-impersonate']);
    $routes->post('impersonate/act-as/(:num)', 'ImpersonateController::actAs/$1', ['as' => 'superadmin-impersonate-act']);
    $routes->match(['get', 'post'], 'impersonate/stop', 'ImpersonateController::stop', ['as' => 'superadmin-impersonate-stop']);

    // -------------------------------------------------------------
    // 7.7.2 PLANS & PRICING MANAGEMENT
    // -------------------------------------------------------------

    /**
     * List all plans with current versions
     */
    $routes->get('plans', 'PlansController::index', ['as' => 'superadmin-plans']);

    /**
     * Edit a plan version (creates new version)
     */
    $routes->get('plans/editVersion/(:num)', 'PlansController::editVersion/$1', ['as' => 'superadmin-plans-edit']);
    $routes->post('plans/updateVersion/(:num)', 'PlansController::updateVersion/$1', ['as' => 'superadmin-plans-update']);

    /**
     * View version history for a plan
     */
    $routes->get('plans/history/(:num)', 'PlansController::versionHistory/$1', ['as' => 'superadmin-plans-history']);

    // -------------------------------------------------------------
    // 7.7.3 SUBSCRIPTIONS & PAYMENTS
    // -------------------------------------------------------------

    /**
     * List all users with their subscription status.
     */
    $routes->get('subscriptions', 'SubscriptionsController::index', ['as' => 'superadmin-subscriptions']);

    /**
     * Detail page for one user (subscription + payment history).
     */
    $routes->get('subscriptions/(:num)', 'SubscriptionsController::detail/$1', ['as' => 'superadmin-subscription-detail']);

    /**
     * Manually set a user's plan (free|gold|platinum).
     */
    $routes->post('subscriptions/plan/(:num)', 'SubscriptionsController::changePlan/$1', ['as' => 'superadmin-subscription-plan']);

    /**
     * Payment history across all users.
     */
    $routes->get('payments', 'SubscriptionsController::payments', ['as' => 'superadmin-payments']);
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

// Catch-all fallback for unmatched API routes to ensure they return JSON
$routes->group('api', function ($routes) {
    $routes->add('(:any)', function () {
        return service('response')
            ->setStatusCode(404)
            ->setJSON([
                'success' => false,
                'status'  => 404,
                'error'   => 'Not Found',
                'message' => 'The requested API endpoint does not exist.'
            ]);
    });
});

// Any other route not matched above goes to 404 error page
$routes->get('(:any)', function () {
    return redirect()->to('/error/404');
});

// =================================================================
// END OF ROUTES
// =================================================================
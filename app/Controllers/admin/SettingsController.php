<?php

namespace App\Controllers\admin;

use CodeIgniter\API\ResponseTrait;

class SettingsController extends BaseAdminController
{
    use ResponseTrait;

    private function delegate(string $controllerClass, string $method, ...$args)
    {
        $controller = new $controllerClass();
        $controller->initController($this->request, $this->response, $this->logger);
        return $controller->$method(...$args);
    }

    public function index()
    {
        return $this->delegate(SystemSettingsController::class, 'index');
    }

    public function update()
    {
        return $this->delegate(SystemSettingsController::class, 'update');
    }

    public function database()
    {
        return $this->delegate(SystemSettingsController::class, 'database');
    }

    public function maintenance()
    {
        return $this->delegate(SystemSettingsController::class, 'maintenance');
    }

    public function run_maintenance()
    {
        return $this->delegate(SystemSettingsController::class, 'run_maintenance');
    }

    public function backup()
    {
        return $this->delegate(SystemSettingsController::class, 'backup');
    }

    public function create_backup()
    {
        return $this->delegate(SystemSettingsController::class, 'create_backup');
    }

    public function restore_backup()
    {
        return $this->delegate(SystemSettingsController::class, 'restore_backup');
    }

    public function download_backup(string $filename)
    {
        return $this->delegate(SystemSettingsController::class, 'download_backup', $filename);
    }

    public function delete_backup(string $filename)
    {
        return $this->delegate(SystemSettingsController::class, 'delete_backup', $filename);
    }

    public function storage()
    {
        return $this->delegate(SystemSettingsController::class, 'storage');
    }

    public function storage_cleanup()
    {
        return $this->delegate(SystemSettingsController::class, 'storage_cleanup');
    }

    public function storage_check_now()
    {
        return $this->delegate(SystemSettingsController::class, 'storage_check_now');
    }

    public function cron()
    {
        return $this->delegate(SystemSettingsController::class, 'cron');
    }

    public function cron_save()
    {
        return $this->delegate(SystemSettingsController::class, 'cron_save');
    }

    public function cron_toggle()
    {
        return $this->delegate(SystemSettingsController::class, 'cron_toggle');
    }

    public function cron_run(int $id)
    {
        return $this->delegate(SystemSettingsController::class, 'cron_run', $id);
    }

    public function cron_delete(int $id)
    {
        return $this->delegate(SystemSettingsController::class, 'cron_delete', $id);
    }

    public function cron_get(int $id)
    {
        return $this->delegate(SystemSettingsController::class, 'cron_get', $id);
    }

    public function retention()
    {
        return $this->delegate(SystemSettingsController::class, 'retention');
    }

    public function run_purge()
    {
        return $this->delegate(SystemSettingsController::class, 'run_purge');
    }

    public function save_retention()
    {
        return $this->delegate(SystemSettingsController::class, 'save_retention');
    }

    public function factory_reset()
    {
        return $this->delegate(SystemSettingsController::class, 'factory_reset');
    }

    public function notification_settings()
    {
        return $this->delegate(MailSettingsController::class, 'notification_settings');
    }

    public function email_triggers()
    {
        return $this->delegate(MailSettingsController::class, 'email_triggers');
    }

    public function testEmail()
    {
        return $this->delegate(MailSettingsController::class, 'testEmail');
    }

    public function api_settings()
    {
        return $this->delegate(SecuritySettingsController::class, 'api_settings');
    }

    public function security_settings()
    {
        return $this->delegate(SecuritySettingsController::class, 'security_settings');
    }
}

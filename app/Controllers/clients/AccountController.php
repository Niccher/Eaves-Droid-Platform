<?php

namespace App\Controllers\clients;

use CodeIgniter\API\ResponseTrait;

class AccountController extends BaseClientController
{
    use ResponseTrait;

    /**
     * Delegate method to ClientProfileController
     */
    public function home(): string
    {
        $controller = new ClientProfileController();
        $controller->initController($this->request, $this->response, $this->logger);
        return $controller->home();
    }

    /**
     * Delegate method to ClientProfileController
     */
    public function sendDeviceReset(): \CodeIgniter\HTTP\ResponseInterface
    {
        $controller = new ClientProfileController();
        $controller->initController($this->request, $this->response, $this->logger);
        return $controller->sendDeviceReset();
    }

    /**
     * Delegate method to ClientProfileController
     */
    public function setting(): string
    {
        $controller = new ClientProfileController();
        $controller->initController($this->request, $this->response, $this->logger);
        return $controller->setting();
    }

    /**
     * Delegate method to ClientProfileController
     */
    public function updateProfile()
    {
        $controller = new ClientProfileController();
        $controller->initController($this->request, $this->response, $this->logger);
        return $controller->updateProfile();
    }

    /**
     * Delegate method to ClientProfileController
     */
    public function security(): string
    {
        $controller = new ClientProfileController();
        $controller->initController($this->request, $this->response, $this->logger);
        return $controller->security();
    }

    /**
     * Delegate method to ClientProfileController
     */
    public function updateSecurity()
    {
        $controller = new ClientProfileController();
        $controller->initController($this->request, $this->response, $this->logger);
        return $controller->updateSecurity();
    }

    /**
     * Delegate method to ClientProfileController
     */
    public function devices(): string
    {
        $controller = new ClientProfileController();
        $controller->initController($this->request, $this->response, $this->logger);
        return $controller->devices();
    }

    /**
     * Delegate method to ApiKeyController
     */
    public function regenerateToken()
    {
        $controller = new ApiKeyController();
        $controller->initController($this->request, $this->response, $this->logger);
        return $controller->regenerateToken();
    }

    /**
     * Delegate method to ApiKeyController
     */
    public function createToken()
    {
        $controller = new ApiKeyController();
        $controller->initController($this->request, $this->response, $this->logger);
        return $controller->createToken();
    }

    /**
     * Delegate method to ApiKeyController
     */
    public function revokeToken()
    {
        $controller = new ApiKeyController();
        $controller->initController($this->request, $this->response, $this->logger);
        return $controller->revokeToken();
    }

    /**
     * Delegate method to TelemetryExportController
     */
    public function exportData($type)
    {
        $controller = new TelemetryExportController();
        $controller->initController($this->request, $this->response, $this->logger);
        return $controller->exportData($type);
    }

    /**
     * Delegate method to TelemetryExportController
     */
    public function exportEmail()
    {
        $controller = new TelemetryExportController();
        $controller->initController($this->request, $this->response, $this->logger);
        return $controller->exportEmail();
    }

    /**
     * Delegate method to TelemetryExportController
     */
    public function downloadExport(string $filename)
    {
        $controller = new TelemetryExportController();
        $controller->initController($this->request, $this->response, $this->logger);
        return $controller->downloadExport($filename);
    }

    /**
     * Delegate method to TelemetryExportController
     */
    public function deleteData($type)
    {
        $controller = new TelemetryExportController();
        $controller->initController($this->request, $this->response, $this->logger);
        return $controller->deleteData($type);
    }

    /**
     * Delegate method to UserSessionController
     */
    public function access_logs($tab = 'all'): string
    {
        $controller = new UserSessionController();
        $controller->initController($this->request, $this->response, $this->logger);
        return $controller->access_logs($tab);
    }

    /**
     * Delegate method to UserSessionController
     */
    public function clearLogs(): \CodeIgniter\HTTP\ResponseInterface
    {
        $controller = new UserSessionController();
        $controller->initController($this->request, $this->response, $this->logger);
        return $controller->clearLogs();
    }

    /**
     * Delegate method to UserSessionController
     */
    public function addLogNote(): \CodeIgniter\HTTP\ResponseInterface
    {
        $controller = new UserSessionController();
        $controller->initController($this->request, $this->response, $this->logger);
        return $controller->addLogNote();
    }

    /**
     * Delegate method to UserSessionController
     */
    public function sessions(): string
    {
        $controller = new UserSessionController();
        $controller->initController($this->request, $this->response, $this->logger);
        return $controller->sessions();
    }

    /**
     * Delegate method to UserSessionController
     */
    public function stats(): string
    {
        $controller = new UserSessionController();
        $controller->initController($this->request, $this->response, $this->logger);
        return $controller->stats();
    }
}
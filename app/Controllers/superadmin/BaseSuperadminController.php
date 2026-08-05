<?php

namespace App\Controllers\superadmin;

use App\Controllers\admin\BaseAdminController;

class BaseSuperadminController extends BaseAdminController
{
    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void
    {
        parent::initController($request, $response, $logger);

        $isSuperadmin = auth()->user()->inGroup('superadmin');
        $isImpersonating = session()->get('impersonated_by') !== null;

        if (!$isSuperadmin && !$isImpersonating) {
            session()->setFlashdata('error', 'You do not have permission to access this area.');
            throw new \RuntimeException('Superadmin group required');
        }
    }
}

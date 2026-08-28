<?php

namespace App\Controllers\clients;

use App\Models\PaymentModel;

class PaymentHistoryController extends BaseClientController
{
    protected PaymentModel $payments;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface            $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->payments = new PaymentModel();
    }

    /**
     * GET /account/payments
     */
    public function index(): string
    {
        $userPayments = $this->payments->getForUser($this->userId);

        return $this->renderUserView('users/billing/payments', [
            'pag'      => 'billing', // Keep 'billing' active in sidebar
            'sub_pag'  => 'payments',
            'payments' => $userPayments,
        ]);
    }
}

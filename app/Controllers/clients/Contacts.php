<?php

namespace App\Controllers\clients;

class Contacts extends BaseClientController
{
    public function index()
    {
        // Get current page from query string
        $currentPage = $this->request->getGet('page') ?? 1;

        // Get per page setting
        $perPage = 50;

        // Get contacts with pagination
        $contacts = $this->finderModel->get_contacts($this->userId, $perPage);

        // Make sure pager is initialized
        $pager = $this->finderModel->pager;

        // If pager is null, try to get it another way
        if (!$pager) {
            $pager = $this->finderModel->getPager();
        }

        // Get total count using the correct method
        $totalContacts = $this->finderModel->get_count_Contacts($this->userId);

        $data = [
            'contacts_dump' => $contacts,
            'pager' => $this->finderModel->pager,
            'currentPage' => $this->getPaginationData()['currentPage'],
            'perPage' => $this->perPage,
            'totalContacts' => $totalContacts
        ];

        return $this->renderUserView('users/contacts', $data);
    }
}
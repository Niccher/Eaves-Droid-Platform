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

        // Get contacts with pagination - PASS THE PER PAGE VALUE!
        $contacts = $this->finderModel->get_contacts($this->userId, $perPage);

        // Make sure pager is initialized
        $pager = $this->finderModel->pager;

        // If pager is null, try to get it another way
        if (!$pager) {
            $pager = $this->finderModel->getPager();
        }

        // Get total count
        $totalContacts = $this->finderModel->get_count_Contacts($this->userId, $perPage);

        $data = [
            'pag' => 'contacts',
            'contacts_dump' => $contacts,
            'pager' => $pager,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
            'totalContacts' => $totalContacts,
        ];

        return $this->renderUserView('users/contacts', $data);
    }
}
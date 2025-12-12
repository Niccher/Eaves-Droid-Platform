<?php

namespace App\Controllers\clients;

class Contacts extends BaseClientController
{
    public function index()
    {
        $data['pag'] = 'contacts';

        $data['contacts_dump'] = $this->finderModel->get_contacts($this->userId, 25);
        $data['pager']         = $this->finderModel->getPager();

        return $this->renderUserView('users/contacts', $data);
    }
}
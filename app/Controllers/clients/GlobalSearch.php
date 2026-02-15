<?php

namespace App\Controllers\clients;

class GlobalSearch extends BaseClientController
{
    /**
     * Handle universal search request.
     *
     * @return string
     */
    public function index(): string
    {
        $query = $this->request->getGet('q');
        
        if (empty($query)) {
            return redirect()->to('home');
        }

        $query = trim($query);

        $results = [
            'sms'      => $this->finderModel->search_sms($this->userId, $query),
            'calls'    => $this->finderModel->search_calls($this->userId, $query),
            'contacts' => $this->finderModel->search_contacts($this->userId, $query),
            'files'    => $this->finderModel->search_files($this->userId, $query),
        ];

        $data = [
            'pag'         => 'globalsearch',
            'query'       => $query,
            'results'     => $results,
            'totalCount'  => count($results['sms']) + count($results['calls']) + count($results['contacts']) + count($results['files']),
        ];

        return $this->renderUserView('users/globalsearch_results', $data);
    }
}

<?php

namespace App\Controllers\clients;

use App\Controllers\BaseController;

use App\Models\Mod_Finder;
use CodeIgniter\API\ResponseTrait;

class Apps extends BaseController
{
    use ResponseTrait;

    public function apps(){
        $model_finder = new Mod_Finder();
        $pager = \Config\Services::pager();
        if (!auth()->loggedIn()){
            return redirect()->to('login');
        }

        $data['pag'] = 'apps';
        $data["user_info"] = $model_finder->basic_user();

        $all_apps_list = $model_finder->get_apps($data["user_info"]['id']);

        $data["apps_dump"] = $all_apps_list;
        $data["pager"] = $model_finder->pager;

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/apps/apps', $data)
            . view('headers_footers/footer_data_datatables');
    }

}

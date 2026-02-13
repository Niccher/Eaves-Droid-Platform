<?php

namespace App\Controllers\clients;



use App\Models\Mod_Extract;
use App\Models\Mod_Finder;
use App\Models\Mod_Crypt;
use App\Models\Mod_Parse_Loot;
use App\Models\Mod_Receive;
use App\Models\Mod_Android;
use App\Models\Mod_User;

class Correlation extends BaseClientController{

    public function index(){
        // Auth check handled in parent
	    $model_crypt = new Mod_Crypt();

	    $pg = 'correlation';
	    $data['pag'] = 'analysis';
	    $data["user_info"] = $this->finderModel->basic_user();
	    
        // Stats
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

        // Analysis Stats
        $data['sms_analysis'] = $this->finderModel->get_categorized_sms_counts($this->userId);
        $data['call_analysis'] = $this->finderModel->get_categorized_call_counts($this->userId);

        // Map data for view variables
        $data['totalAnalyzedSMS'] = $data['sms_analysis']['total'];
        $data['financialAlerts'] = $data['sms_analysis']['financial'];
        $data['suspiciousCalls'] = $data['call_analysis']['spam'];
        $data['newContacts'] = $data['call_analysis']['new'];

        $data['financialSMS'] = $data['sms_analysis']['financial'];
        $data['promotionalSMS'] = $data['sms_analysis']['promo'];
        $data['maliciousSMS'] = $data['sms_analysis']['malicious'];
        $data['otpSMS'] = $data['sms_analysis']['otp'];
        $data['utilitySMS'] = $data['sms_analysis']['utility'];
        $data['serviceSMS'] = $data['sms_analysis']['service'];
        $data['personalSMS'] = $data['sms_analysis']['personal'];

        $data['familyCalls'] = $data['call_analysis']['family'];
        $data['newCalls'] = $data['call_analysis']['new'];
        $data['businessCalls'] = $data['call_analysis']['business'];
        $data['spamCalls'] = $data['call_analysis']['spam'];
        $data['intlCalls'] = $data['call_analysis']['intl'];
        $data['urgentCalls'] = $data['call_analysis']['urgent'];

        // Accuracy simulation (can be refined later)
        $data['smsAccuracy'] = 94;
        $data['callAccuracy'] = 91;

	    return view('headers_footers/head_users')
		    . view('headers_footers/sidebar_users', $data)
		    . view('users/correlation/'.$pg, $data)
		    . view('headers_footers/footer_users');
    }

    public function sms_finance(){
        // Auth check handled in parent
        //$model_finder = new Mod_Finder();
        $model_crypt = new Mod_Crypt();
        $model_extract = new Mod_Extract();
        //$encrypter = \Config\Services::encrypter();

        $pg = 'correlation';
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();

        // Stats
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

        $sms_dump = $this->finderModel->get_sms($data["user_info"]['id']);
        $sms_sender_list = array();
        $sms_parserable = array();
        $sms_good_sms = array();

        foreach ($sms_dump as $sms) {
            array_push($sms_sender_list,$sms['sms_number']);
        }
        asort($sms_sender_list);

        $sms_senders = array_values(array_unique($sms_sender_list));

        $list = '<label>Select new points.</label>
        <select class="form-control source_sms select2-hidden-accessible" multiple="" data-placeholder="Select a Contact to monitor" style="width: 100%;" tabindex="-1" aria-hidden="true">';

        foreach ($sms_senders as $sms_point) {
            $valu = $model_crypt->base64url_encode($sms_point);
            $list.='<option value="'.$valu.'" >'.$sms_point.'</option>';
        }
        $list .= '        
        </select>';

        $data['sms_data_points'] = $list;

        $sms_finance_points = $this->finderModel->get_points_sms_finance($data["user_info"]['id']);

        $list_finance = '
        <select class="form-control source_sms_finance select2-hidden-accessible" data-placeholder="Select a Contact to monitor" style="width: 100%;" tabindex="-1" aria-hidden="true">';

        foreach ($sms_finance_points as $sms_finance) {
            $valu = $model_crypt->base64url_encode($sms_finance["point_Name"]);
            $list_finance.='<option value="'.$valu.'" >'.$sms_finance["point_Name"].'</option>';
            array_push($sms_parserable,$sms_finance["point_Name"]);
        }
        $list_finance .= '        
        </select>';
        $data['sms_data_points_source'] = $list_finance;

        if (!empty($sms_parserable)){
            $sms_good_sms = $this->finderModel->get_sms_from_sender($data["user_info"]['id'] , $sms_parserable);
        }
        $data['sms_good_sms'] = $sms_good_sms;

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/sms_finance', $data)
            . view('headers_footers/tail_analyze_sms', $data);
    }

    public function set_sms_rules(){
        //$model_finder = new Mod_Finder();
        $model_crypt = new Mod_Crypt();
        $model_extract = new Mod_Extract();
        //$encrypter = \Config\Services::encrypter();
        // if (!auth()->loggedIn()){ return redirect()->to('login'); } // Handled in parent

        $pg = 'correlation';
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
    }

    public function set_sms_datapoints($owner){
        //$model_finder = new Mod_Finder();

        // if (!auth()->loggedIn()){ return redirect()->to('login'); } // Handled in parent

        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        $dated = date('Y-m-d H:i:s');

        $post_point = explode(',', str_replace('"',"",$_POST['points']) );
        foreach ( $post_point as $item) {
            $data_point = array(
                "point_Owner" => $data["user_info"]['id'],
                "point_Name" =>  base64_decode(urldecode($item)),
                "point_Inserted" => $dated,
            );
            $this->finderModel->set_sms_points_to_analyze_finance($data_point);
        }
    }

    public function sms_analyze_finance_from($source){
        //$model_finder = new Mod_Finder();
        $model_crypt = new Mod_Crypt();
        $model_extract = new Mod_Extract();
        //$encrypter = \Config\Services::encrypter();
        // if (!auth()->loggedIn()){ return redirect()->to('login'); } // Handled in parent

        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();

        // Stats
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

        $source_clean = $model_crypt->base64url_decode($source);

        $data["page_info_url"] = $source_clean;

        $data['sms_good_sms'] = $model_extract->get_sms_from($data["user_info"]['id'], $source_clean);

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/sms_finance_single_view', $data)
            . view('headers_footers/tail_analyze_sms', $data);

    }

    public function sms_analysis()
    {
        $category = $this->request->getGet('category') ?? 'financial';
        
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        
        // Stats
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

        $data['current_category'] = $category;
        $data['categorized_sms'] = $this->finderModel->get_categorized_sms($this->userId, $category);
        
        // Pass counts for the tabs
        $data['sms_counts'] = $this->finderModel->get_categorized_sms_counts($this->userId);

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/sms_analysis', $data)
            . view('headers_footers/footer_users');
    }

    public function call_analysis()
    {
        $category = $this->request->getGet('category') ?? 'family';
        
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        
        // Stats
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

        $data['current_category'] = $category;
        $data['categorized_calls'] = $this->finderModel->get_categorized_calls($this->userId, $category);
        
        // Pass counts for the tabs
        $data['call_counts'] = $this->finderModel->get_categorized_call_counts($this->userId);

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/call_analysis', $data)
            . view('headers_footers/footer_users');
    }

}

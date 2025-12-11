<?php

namespace App\Models;

use CodeIgniter\Model;

class Mod_Finder extends Model{

    public function basic_user(){
        if (auth()->loggedIn()){
            $user_data = json_decode(json_encode(auth()->user()), true);
            return $user_data;
        }
    }

	public function get_sms_sent($user_id){//$device_id
		$builder = $this->db->table('tbl_SMSsent');
		$query_sent = $builder->select('*')
			->where('meta_Owner', $user_id)
			->get();
		return $query_sent->getResult();
	}

	public function get_sms_type($user_id, $sms_type){
		$builder = $this->db->table('tbl_Sms');
		$query_sent = $builder->select('*')
			->where('meta_Owner', $user_id)
			->where('sms_type', $sms_type)
			->orderBy('sms_time', 'DESC')
			->get();
		return $query_sent->getResultArray();
	}

	public function get_sms($user_id){
		$builder = $this->db->table('tbl_Sms');
		$query_sent = $builder->select('*')
			->where('meta_Owner', $user_id)
			->orderBy('sms_time', 'DESC')
			->get();
		return $query_sent->getResultArray();
	}

	public function get_sms_limited($user_id, $limit, $start){
		$builder = $this->db->table('tbl_Sms');
		$query_sent = $builder->select('*')
			->where('meta_Owner', $user_id)
			->orderBy('sms_time', 'DESC')
			->limit($limit, $start)
			->get();
		return $query_sent->getResult();
	}

	public function get_count_Sms($user_id) {
		$builder = $this->db->table('tbl_Sms');
		$query_sent = $builder->select('*')
			->where('meta_Owner', $user_id)
			->get();
		return count($query_sent->getResultArray());
	}

	public function get_count_Sms_category($user_id, $category) {
		$builder = $this->db->table('tbl_Sms');
		$query_sent = $builder->select('*')
			->where('meta_Owner', $user_id)
			->where('sms_type', $category)
			->get();
		return count($query_sent->getResultArray());
	}

	public function get_sms_active($user_id){
		$builder = $this->db->table('tbl_Sms');
		$query_sent = $builder->select('sms_number, sms_thread_id, count(*) AS Totals')
			->where('meta_Owner', $user_id)
			->groupBy('sms_number')
			->orderBy('Totals', 'DESC')
			->limit(10)
			->get();
		return $query_sent->getResultArray();
	}

	public function get_sms_between($user_id, $contactNumber1, $contactNumber2){
		$builder = $this->db->table('tbl_Sms');
		$query_sent = $builder->select('sms_number, sms_thread_id, count(*) AS Totals')
			->where('meta_Owner', $user_id)
			->where('sms_number', $contactNumber1)
			->orWhere('sms_number', $contactNumber2)
			->orderBy('sms_time', 'DESC')
			->get();
		return $query_sent->getResultArray();
	}

	public function get_sms_only_unique($user_id){
        $builder = $this->db->table('tbl_SMS');
        $query_sent = $builder
            ->where('meta_Owner', $user_id)
            ->groupBy('sms_number')
            ->get();
        return $query_sent->getResult();
	}

	public function get_contacts($user_id){
		$builder = $this->db->table('tbl_Contacts');
		$query_sent = $builder->select('*')
			->where('meta_Owner', $user_id)
			->get();
		return $query_sent->getResultArray();
	}

	public function get_count_Contacts($user_id) {
    	$builder = $this->db->table('tbl_Contacts');
		$query_sent = $builder->select('*')
			->where('meta_Owner', $user_id)
			->get();
		return count($query_sent->getResult());
	}

	public function get_contact_at($contact_id){
		$builder = $this->db->table('tbl_Sms');
		$query_sent = $builder->select('*')
			->where('Contact_ID', $contact_id)
			->get();
		return $query_sent->getRowArray();
	}

	public function get_contact_info($contactNumber1){
    	$user_id = json_decode(json_encode(auth()->user()), true)['id'];
		$builder = $this->db->table('tbl_Contacts');
		$query_sent = $builder->select('*')
			->where('meta_Owner', $user_id)
			->like('Number', $contactNumber1)
			->limit(1)
			->get();
		return $query_sent->getRowArray();
	}

	public function get_call_logs($user_id){
		$builder = $this->db->table('tbl_Logs');
		$query_sent = $builder->select('*')
			->where('meta_Owner', $user_id)
			->get();
		return $query_sent->getResultArray();
	}

	public function get_count_Calls($user_id) {
		$builder = $this->db->table('tbl_Logs');
		$query_sent = $builder->select('*')
			->where('meta_Owner', $user_id)
			->get();
		return count($query_sent->getResult());
	}

	public function get_count_Calls_category($user_id, $category) {
		$builder = $this->db->table('tbl_Logs');
		$query_sent = $builder->select('*')
			->where('meta_Owner', $user_id)
			->where('Type', $category)
			->get();
		return count($query_sent->getResult());
	}

	public function get_calls_limited($user_id, $category){
		$builder = $this->db->table('tbl_Logs');
		$query_sent = $builder->select('*')
			->where('meta_Owner', $user_id)
			->where('Type', $category)
			->orderBy('Timestamp', 'DESC')
			//->limit($limit, $start)
			->get();
		return $query_sent->getResultArray();
	}

	public function get_calls_active($user_id){
    	$builder = $this->db->table('tbl_Logs');
		$query_sent = $builder->select('Caller, Saved, count(*) AS Totals')
			->where('meta_Owner', $user_id)
			->groupBy('Caller')
			->orderBy('Totals', 'DESC')
			->limit(10)
			->get();
		return $query_sent->getResultArray();
	}

	public function get_logs_between($contactNumber1, $contactNumber2, $user_id){
		$builder = $this->db->table('tbl_Logs');
		$query_sent = $builder->select('*')
			->where('meta_Owner', $user_id)
			->where('Caller', $contactNumber1)
			->orWhere('Caller', $contactNumber2)
			->orderBy('Timestamp', 'DESC')
			->get();
		return $query_sent->getResultArray();
	}

	public function get_apps_with_limit($user_id, $limit, $start){
		$builder = $this->db->table('tbl_Apps');
		$query_sent = $builder->select('*')
			->where('meta_Owner', $user_id)
			->limit($limit, $start)
			->get();
		return $query_sent->getResultArray();
	}

	public function get_apps($user_id){
		$builder = $this->db->table('tbl_Apps');
		$query_sent = $builder->select('*')
			->where('meta_Owner', $user_id)
			->get();
		return $query_sent->getResultArray();
	}

	public function get_count_Apps($user_id) {
		$builder = $this->db->table('tbl_Apps');
		$query_sent = $builder->select('*')
			->where('meta_Owner', $user_id)
			->get();
		return count($query_sent->getResult());
	}

    public function get_points_sms_finance($user_id){
        $builder = $this->db->table('tbl_Points_Finance');
        $query_sent = $builder
            ->where('point_Owner', $user_id)
            ->orderBy('point_Inserted', 'DESC')
            ->get();
        return $query_sent->getResultArray();
    }

    public function get_sms_from_sender($user_id, $sender){
        $builder = $this->db->table('tbl_Sms');
        $query_sent = $builder
            ->where('meta_Owner', $user_id)
            ->whereIn('sms_number', $sender)
            ->orderBy('sms_time', 'DESC')
            ->get();
        return $query_sent->getResultArray();
    }

    public function set_sms_points_to_analyze_finance($data){
        $builder = $this->db->table('tbl_Points_Finance');
        $builder->insert($data);
    }
}

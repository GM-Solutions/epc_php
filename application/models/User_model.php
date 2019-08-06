<?php 
require 'Common_model.php';
class User_model extends Common_model {
    
        function select_data($table_name,$cond = array())
	{
		$this->db->select('*');
		$this->db->from($table_name);
		if(!empty($cond)){ foreach ($cond as $key => $value)$this->db->where($key,$value); }
		$query = $this->db->get();
		return ($query->num_rows() > 0)?$query->result_array():FALSE;
	}
        function check_user($cond =  array()){
                $this->db->select('*');
		$this->db->from('agreements_userinfo');
                $this->db->where('mobile_no',$cond['mobile_no']); 
                $this->db->or_where('email',$cond['email']); 
		$query = $this->db->get();
		return ($query->num_rows() > 0)?$query->row():FALSE;
        }
        function pmdetails($cond =  array()) {
            $this->db->select('au.email AS pm_email,au.id AS pm_uid,au.first_name AS pm_firstname,au.last_name AS pm_lastname');
            $this->db->from('agreements_userinfo AS uinfo');
            $this->db->join('auth_user AS au','uinfo.personal_manager_id=au.id','left');
            if(!empty($cond)){ foreach ($cond as $key => $value)$this->db->where($key,$value); }
            $query = $this->db->get();
            return ($query->num_rows() > 0)?$query->row():FALSE;
        }
        function society_dtl_from_user($userid) {
            $this->db->select('society_info.id AS society_id,society_user_members.role');
            $this->db->from('society_info');
            $this->db->join('society_user_members','society_info.id=society_user_members.society_id','left');
            $this->db->where('society_user_members.member_info_id',$userid);
            $query = $this->db->get();
            return ($query->num_rows() > 0)?$query->result_array():FALSE;
        }
        function society_dtl_from_user_withsociety($userid,$society_id) {
            $this->db->select('*, society_info.id AS society_id');
            $this->db->from('society_info');
            $this->db->join('society_user_members','society_info.id=society_user_members.society_id','left');
            $this->db->where('society_user_members.member_info_id',$userid);
            $this->db->where('society_info.id',$society_id);
            $query = $this->db->get();
            return ($query->num_rows() > 0)?$query->result_array():FALSE;
        }
        function society_member_list($params = array(),$cond=array()) {
            $this->db->select('*');
            $this->db->from('society_user_members AS sm');
            $this->db->join('agreements_userinfo AS uinfo','uinfo.id = sm.member_info_id','left');
            $this->db->join('society_info AS society','society.id = sm.society_id','left');
            if(!empty($cond)){ foreach ($cond as $key => $value)$this->db->where($key,$value); }
            
            if(array_key_exists("start",$params) && array_key_exists("limit",$params)){
            $this->db->limit($params['limit'],$params['start']);
            }elseif(!array_key_exists("start",$params) && array_key_exists("limit",$params)){
                $this->db->limit($params['limit']);
            }
            $query = $this->db->get();
            return ($query->num_rows() > 0)?$query->result_array():FALSE;
        }
        
}
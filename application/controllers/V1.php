<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class V1 extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->helper('url');
        $this->load->model("User_model");
        $this->load->model("Common_model");
        $this->load->library("session");
        $this->load->database();        
    }

    public function login_chk() {
        $cid = $this->input->get('cid');
        $filter =  $this->input->get('filter');
         if(empty($cid) OR empty($filter)){
        show_error("No Session find");
        }

         $this->db->select('au.id AS user_id,
au.email,
            au.first_name,
            au.last_name,
            au.username,
            role.name AS role_name,
            role.id AS role_id,
            bv.name AS vertical_name,
up1.phone_number,
            bv.id AS vertical_id');
        $this->db->from('auth_user AS au');
$this->db->join('gm_userprofile AS up1','au.id = up1.user_id','left');
        $this->db->join('gm_epcuserprofileroles AS up','up.userprofile_id = au.id', 'left');
        $this->db->join('gm_epcroles AS role','role.id = up.role_id', 'left');
        $this->db->join('gm_brandvertical AS bv','bv.id = role.vertical_id', 'left');

        $this->db->where('au.id', $cid);    
        $query = $this->db->get();
        
        $user_info = ($query->num_rows() > 0) ? $query->result_array() : FALSE;
        if ($query->num_rows() > 1) { /* more than one record */
                $data['message'] = "More than one account";
                $this->load->view('login', $data);
                return true;
            } else {
                foreach ($user_info as $key => $value) { 
$dtl['email'] = $value['email'];
                        $dtl['user_id'] = $value['user_id'];
                        $dtl['username'] = $value['username'];
                        $dtl['first_name'] = $value['first_name'];
                        $dtl['last_name'] = $value['last_name'];
    $dtl['phone_number'] = $value['phone_number'];
                        $dtl['role'][$key]['role_name'] = $value['role_name'];
                        $dtl['role'][$key]['role_id'] = $value['role_id'];
                        $dtl['role'][$key]['vertical_name'] = $value['vertical_name'];
                        $dtl['role'][$key]['vertical_id'] = $value['vertical_id'];

                        

                        if($value['role_name'] =="user"){
                            $order_count = $this->Common_model->select_info('gm_part_order_cart',array('user_id'=>$value['user_id'],'active'=>1));
                            $dtl['cart_count']= ($order_count == FALSE) ? 0 : count($order_count);
                        }
                    
                    }
                    $dtl['logged_in']= TRUE;

                    $this->session->set_userdata($dtl);
                    $rol = $this->session->userdata('role');                    
                    sleep(3);
                    if($rol[0]['role_name'] == "Distributor" || $rol[0]['role_name'] == "Dealer" || $rol[0]['role_name'] == "Users" || $rol[0]['role_name'] == "Members"){
                       redirect(base_url()."Sa_vin_search_dealers/Vindetails?select_type=".$filter);    
                    } else {
                       redirect(base_url()."Sa_vin_search/Vindetails?select_type=".$filter); 
                    }
            }
    }
    public function login() {
        $dtl =  array();
        $segment_message = "";
      $token = $this->uri->segment(3,$segment_message = "Auth Token Not Found");
      echo $this->input->get('select_type');
      
      //echo $token;
      die;
      if($token == $segment_message){
          show_error($segment_message);
      } else {
          
                $this->db->select('au.id AS user_id,
                au.first_name,
                au.last_name,
                au.password,
                au.username,
                role.name AS role_name,
                role.id AS role_id,
                bv.name AS vertical_name,
                bv.id AS vertical_id');
		$this->db->from('oauth2_accesstoken AS t');
                $this->db->join('auth_user AS au','t.user_id=au.id');
                $this->db->join('gm_epcuserprofileroles AS up','up.userprofile_id = au.id', 'left');
                $this->db->join('gm_epcroles AS role','role.id = up.role_id', 'left');
                $this->db->join('gm_brandvertical AS bv','bv.id = role.vertical_id', 'left');
//                $this->db->join('auth_user_groups AS aug','t.user_id = aug.user_id','left');
//                $this->db->join('auth_group AS ag','ag.id = aug.group_id','left');
//                $this->db->join('auth_user AS au','au.id = t.user_id','left');
                $this->db->where('t.token',$token);
                $this->db->where('date(t.expires) >=','date(now())',FALSE);
                $query = $this->db->get();
		$auth_token_info = ($query->num_rows() > 0)? $query->result_array():FALSE;
//                echo $this->db->last_query(); 
                print_r($auth_token_info);  die;
          if($auth_token_info){
              foreach ($auth_token_info as $key => $value) {                
                        $dtl['user_id'] = $value['user_id'];
                        $dtl['username'] = $value['username'];
                        $dtl['email'] = $value['email'];
                        $dtl['first_name'] = $value['first_name'];
                        $dtl['last_name'] = $value['last_name'];
                        $dtl['group'][$key]['group_name'] = $value['group_name'];
                        $dtl['group'][$key]['group_id'] = $value['group_id'];                         
              }
            $dtl['logged_in']= TRUE;
            $this->session->set_userdata($dtl);
            redirect(base_url()."Uniquebilled");
          } else {
              show_error("Sorry,Token Expire!!");
          }
      }       
        
        
    }

}

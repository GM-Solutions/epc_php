<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class User extends CI_Controller {

    function __construct() {
        parent::__construct();

        $this->load->helper('url');
        $this->load->model("User_model");
        $this->load->model("Common_model");
        $this->load->database();
        $this->load->library('session');
    }

    public function index() {
        $action = $this->input->post('submit');
        if ($action == "submit") {
            $username = $this->input->post('username');
            $password = $this->input->post('password');
            $this->db->select('au.id AS user_id,
            au.first_name,
            au.last_name,
            au.email,
            au.password,
            au.username,
            role.name AS role_name,
            role.id AS role_id,
            bv.name AS vertical_name,
            up1.phone_number,
            up1.image_url,
            bv.id AS vertical_id');
        $this->db->from('auth_user AS au');
        $this->db->join('gm_userprofile AS up1','au.id = up1.user_id','left');
        $this->db->join('gm_epcuserprofileroles AS up','up.userprofile_id = au.id', 'left');
        $this->db->join('gm_epcroles AS role','role.id = up.role_id', 'left');
        $this->db->join('gm_brandvertical AS bv','bv.id = role.vertical_id', 'left');

        $this->db->where('au.username', $username);
        $this->db->or_where('au.email', $username);
            $query = $this->db->get();
            $user_info = ($query->num_rows() > 0) ? $query->result_array() : FALSE;
            
            if ($query->num_rows() > 1) { /* more than one record */
                $data['message'] = "More than one account";
                $this->load->view('login', $data);
                return true;
            } else {
                if (Common::django_pwd_verify($user_info[0]['password'], $password)) {
                    foreach ($user_info as $key => $value) { 
                        $dtl['email'] = $value['email'];
                        $dtl['user_id'] = $value['user_id'];
                        $dtl['username'] = $value['username'];
                        $dtl['first_name'] = $value['first_name'];
                        $dtl['last_name'] = $value['last_name'];
                        $dtl['phone_number'] = $value['phone_number'];
                        $dtl['image_url'] = $value['image_url'];
                        $dtl['role'][$key]['role_name'] = $value['role_name'];
                        $dtl['role'][$key]['role_id'] = $value['role_id'];
                        $dtl['role'][$key]['vertical_name'] = $value['vertical_name'];
                        $dtl['role'][$key]['vertical_id'] = $value['vertical_id'];

                        if($value['vertical_id'] == MOTERCYCLE && $value['role_name'] =="Distributor" ){
                            $dtl1 =$this->Common_model->select_info('gm_sfa_mc_distributor',array('user_id'=>$value['user_id']));                        
                            $dtl['role'][$key]['distributor_id']=$dtl1[0]['id'];
                        }
                        if($value['vertical_id'] == MOTERCYCLE && $value['role_name'] =="Dealer" ){
                            $dtl1 =$this->Common_model->select_info('gm_mc_dealer',array('user_id'=>$value['user_id']));                        
                            $dtl['role'][$key]['dealer_id']=$dtl1[0]['id'];
                        }

                        if($value['role_name'] =="user"){
                            $order_count = $this->Common_model->select_info('gm_part_order_cart',array('user_id'=>$value['user_id'],'active'=>1));
                            $dtl['cart_count']= ($order_count == FALSE) ? 0 : count($order_count);
                        }
                    
                    }
                    $dtl['logged_in']= TRUE;
                    
                    $this->session->set_userdata($dtl);
                    $rol = $this->session->userdata('role');
                    if($rol[0]['role_name'] == "Distributor" OR $rol[0]['role_name'] == "Dealer"){
                       redirect(base_url()."Sa_vin_search_dealers/Vindetails?select_type=other");    
                    } else {
                       redirect(base_url()."epc_reports/Vindetails"); 
                    }
                    
                }else{
                    $data['message'] = "Password Dosenot metch";
                    $this->load->view('login', $data);                    
                    return true;
                }
            }
        }
        $data['message'] = "";
        $this->load->view('login', $data);
        
    }
    
    public function logout() {
         $newdata = array(
                'user_id'=> '',
                'username'  =>'',
                'email' => '',
                'logged_in' => FALSE,
               );

     $this->session->unset_userdata($newdata);
     $this->session->sess_destroy();
     $catlog_url = $this->config->item('catlog');
//     redirect(base_url()."User",'refresh');
     redirect($catlog_url['url']."/user-logout");
    }
public function email_test() {
        $email_dtl[0]['to']='pavaningalkar@gladminds.co';
        $email_dtl[0]['subject']='Its a testing mail';
        $email_dtl[0]['message']='<h1>Its a testing mail</h1>';
        Common::send_email($email_dtl);
    }
}

?>

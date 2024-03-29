<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class User extends REST_Controller {

    //put your code here
    function __construct() {
        // Construct the parent class
        parent::__construct();
        $this->load->model("Common_model");
        $this->load->helper('rand_helper'); 
    }

    public function login_post() {
        $email = $this->post('email');
        $password = $this->post('password');
        if (empty($email) || empty($password)) {
            $dtl['status'] = FALSE;
            $dtl['message'] = "Please fill all details properly";
            $this->response($dtl, REST_Controller::HTTP_OK);
        }

        $dtl = array();
        $this->db->select('au.id AS user_id,
            au.first_name,
            au.last_name,
            au.password,
            au.username,
            role.name AS role_name,
            role.id AS role_id,
            bv.name AS vertical_name,
            bv.id AS vertical_id');
        $this->db->from('auth_user AS au');
        $this->db->join('gm_epcuserprofileroles AS up','up.userprofile_id = au.id', 'left');
        $this->db->join('gm_epcroles AS role','role.id = up.role_id', 'left');
        $this->db->join('gm_brandvertical AS bv','bv.id = role.vertical_id', 'left');

        $this->db->where('au.username', $email);
        $this->db->or_where('au.email', $email);
        $query = $this->db->get();
        $user_info = ($query->num_rows() > 0) ? $query->result_array() : FALSE;
        
        if($query->num_rows() > 1){ /* more than one record*/
            $dtl['status'] = FALSE;
            $dtl['message'] = "Sorry, Can not process your request please contact help desk.";
            $this->response($dtl, REST_Controller::HTTP_OK);
        }

        if ($user_info) {
            if (Common::django_pwd_verify($user_info[0]['password'], $password)) {
                $dtl['status'] = TRUE;
                foreach ($user_info as $key => $value) {
                    $dtl['user_id'] = $value['user_id'];
                    $dtl['username'] = $value['username'];
                    $dtl['first_name'] = $value['first_name'];
                    $dtl['last_name'] = $value['last_name'];
                    $dtl['role'][$key]['role_name'] = $value['role_name'];
                    $dtl['role'][$key]['role_id'] = $value['role_id'];
                    $dtl['role'][$key]['vertical_name'] = $value['vertical_name'];
                    $dtl['role'][$key]['vertical_id'] = $value['vertical_id'];
                    
                    if($value['vertical_id'] == 1 && $value['role_name'] =="Distributor" ){
                        $dtl1 =$this->Common_model->select_info('gm_sfa_mc_distributor',array('user_id'=>$value['user_id']));                        
                        $dtl['role'][$key]['distributor_id']=$dtl1[0]['id'];
                    }
					
					if($value['role_name'] =="user"){
                        $order_count = $this->Common_model->select_info('gm_part_order_cart',array('user_id'=>$value['user_id'],'active'=>1));
			$dtl['cart_count']= ($order_count == FALSE) ? 0 : count($order_count);
                    }
                    
                } 
                $this->response($dtl, REST_Controller::HTTP_OK);
            } else {
                $dtl['status'] = FALSE;
                $dtl['message'] = "Password dose not match";
                $this->response($dtl, REST_Controller::HTTP_OK);
            }
        } else {
            $dtl['status'] = FALSE;
            $dtl['message'] = "User details not found, Please check  your details";
            $this->response($dtl, REST_Controller::HTTP_OK);
        }
    }

    public function forget_password_check_post() {
        /* get email id */
        $email = $this->post('email');
        $type = $this->post('type'); /* check for sent verification ,  verify  for verify otp and set password */
        if (empty($email) || empty($type)) {
            $dtl['status'] = FALSE;
            $dtl['message'] = "Please fill all details properly";
            $this->response($dtl, REST_Controller::HTTP_OK);
        }


        $this->db->select('au.id AS user_id, au.email,group.name AS group_name,group.id AS group_id,au.first_name,au.last_name,au.password,au.username');
        $this->db->from('auth_user AS au');
        $this->db->join('auth_user_groups AS g', 'au.id=g.user_id', 'left');
        $this->db->join('auth_group AS group', 'group.id=g.group_id', 'left');

        $this->db->where('au.username', $email);
        $this->db->or_where('au.email', $email);
        $query = $this->db->get();
        $user_info = ($query->num_rows() > 0) ? $query->result_array() : FALSE;
        
        if($query->num_rows() > 1){ /* more than one record*/
            $dtl['status'] = FALSE;
            $dtl['message'] = "Sorry, Can not process your request please contact help desk.";
            $this->response($dtl, REST_Controller::HTTP_OK);
        }


        if ($user_info) {            
            if ($type == "set_pwd") {      
                /* check for code authenticity */

                $dtl = $this->set_password($user_info);
                $this->response($dtl, REST_Controller::HTTP_OK);
                die;
            }
            /* send email */
            $code = generateRandomString(6);
            $template_message = "Dear User<br/> You have request for Password Change.<br/> Please Use Code " . $code . " to reset password<br/>";
            $email_dtl[0]['to'] = $user_info[0]['email'];//"pvningalkar@gmail.com";
            $email_dtl[0]['message'] = $template_message;
            $email_dtl[0]['subject'] = "Verification Code for password reset";
            $log_responce = Common::send_email($email_dtl);
            if ($log_responce[0]['status'] == "sent") {
                $dtl['status'] = TRUE;
                $dtl['message'] = "We have sent you verification code.";
                $dtl['code'] = $code;
                /* insert code in  gm_user_password_reset*/
                $now = date('Y-m-d H:i:s');
                $this->Common_model->insert_info('gm_user_password_reset',array('user_id'=>$user_info[0]['user_id'],'uuid'=>$code,'created_date'=>$now,'requested_date'=>$now));
            } else {
                $dtl['status'] = FALSE;
                $dtl['message'] = "Something went wrong";
            }
        } else {
            $dtl['status'] = FALSE;
            $dtl['message'] = "No User Found";
        }
        $this->response($dtl, REST_Controller::HTTP_OK);
    }

    private function set_password($user_info) {
               
        $randomString = generateRandomString(12);
        $new_password = $this->post('new_password');
        if (empty($new_password)) {
            $dtl['status'] = FALSE;
            $dtl['message'] = "Please give new password";
            return $dtl;
        }
        $salt = $randomString;
        $new_hash_pwd = Common::django_pwd_generate($new_password, $salt);

        /* update password */

        $up_dtl['password'] = $new_hash_pwd;
        $up_cond['id'] = $user_info[0]['user_id'];
        if ($this->Common_model->update_info("auth_user", $up_dtl, $up_cond)) {
            $dtl['status'] = TRUE;
            $dtl['message'] = "Password update sucessfully";
            return $dtl;
        }
    }
    
    public function add_shop_post() {
        $user_id = $this->post('user_id');
        $api_type = $this->post('api_type');
        $role_name = $this->post('role_name');
        $role_id = $this->post('role_id');/*15 for Distributor*/
        $default_address = $this->post('default_address'); 
        
        if(!empty($role_name) && ($role_name == "Distributor") ){           
       /* continue*/
            
        }else{
            $op['status'] = FALSE;
            $op['message'] = "Sorry No details available";
            $this->response($op, REST_Controller::HTTP_OK); 
            return TRUE;
        }

        $add_address =  $select_cond = array();
        
        if(!empty($this->post('distributor_id')) && $role_name == "Distributor") $select_cond['epc_mc_distributor_id'] =$add_address['epc_mc_distributor_id'] = $this->post('distributor_id');
        
        if(!empty($this->post('address'))) $add_address['address'] = $this->post('address');
        
        if(!empty($this->post('city'))) $add_address['city'] = $this->post('city');
        
        if(!empty($this->post('state'))) $add_address['state'] = $this->post('state');
        
        
        if(!empty($this->post('pin_code'))) $add_address['pin_code'] = $this->post('pin_code');
        
        if(!empty($this->post('latitude'))) $add_address['latitude'] = $this->post('latitude');
        
        if(!empty($this->post('longitude'))) $add_address['longitude'] = $this->post('longitude');
        
        if(!empty($this->post('active'))) $add_address['active'] = $this->post('active');
        
        
        if ($api_type == "addnew") {
            
            /* update all address as not default */
            $add_address['active'] = 1;
            $this->Common_model->insert_info('gm_epc_shop_details', $add_address);
           
        } elseif ($api_type == "update") {
            $up_cond['id'] = $this->post('address_id');
            $this->Common_model->update_info('gm_epc_shop_details', $add_address, $up_cond);
        }

        /* send address details */
        $op = array();
        $select_cond['active']=1;
        $address_dtl = $this->Common_model->select_info('gm_epc_shop_details',$select_cond);
        if ($address_dtl) {
            $op['status'] = TRUE;
            foreach ($address_dtl AS $key => $val) {

                $op[$key]['address_id'] = $val['id'];
                $op[$key]['address'] = $val['address'];
                $op[$key]['city'] = $val['city'];
                $op[$key]['state'] = $val['state'];
                $op[$key]['pin_code'] = $val['pin_code'];
                $op[$key]['latitude'] = $val['latitude'];
                $op[$key]['longitude'] = $val['longitude'];
                $op[$key]['distributor_id'] = $val['epc_mc_distributor_id'];
                
            }
        } else {
            $op['status'] = FALSE;
            $op['message'] = "Sorry No Address available";
        }
        
        $this->response($op, REST_Controller::HTTP_OK); 
    }
    
    public function user_registration_post() {       
        $first_name = $this->post('first_name');
        $last_name = $this->post('last_name');
        $email = $this->post('email');
        $password = $this->post('password');
        $phone_number = $this->post('phone_number');
        $op =  array();
        if(empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($phone_number)){
            $op['status']=false;
            $op['message']="Please give all details";
            $this->response($op, REST_Controller::HTTP_OK); 
            return TRUE;
        }
        
        /*check  if email id already available in system auth_user*/
        
        $this->db->select('*');
        $this->db->from('auth_user AS au');
        $this->db->join('gm_userprofile AS up','au.id=up.user_id');
        $this->db->where('au.username',$email);
        $this->db->where('au.email',$email);
        $this->db->or_where('up.phone_number',$phone_number);
        
        $query = $this->db->get();
        $user_info = ($query->num_rows() > 0) ? $query->result_array() : FALSE;
        $msg ="";
        if($user_info){
            if($user_info[0]['email'] == $email) $msg = "Email";
            if($user_info[0]['username'] == $email) $msg = "Email";
            if($user_info[0]['phone_number'] == $phone_number) $msg .= (!empty($msg)?",":"")." Mobile number";
            $op['status']=false;
            $op['message']=$msg." is already used";
            $this->response($op, REST_Controller::HTTP_OK); 
            return TRUE;
        } else{
            /* Make registration process */
            $this->db->trans_start();
            $new_profile['username']= $email;
            $new_profile['email']= $email;
            $new_profile['first_name']= $first_name;
            $new_profile['last_name']= $last_name;
            $new_profile['is_staff']=0;
            $new_profile['is_active']=1;
            $new_profile['date_joined']=  date('Y-m-d H:i:s');
            $new_profile['password']= common::django_pwd_generate($password, mt_rand(10000,99999).time());
            
            $l_id = $this->Common_model->insert_info('auth_user',$new_profile);
            $this->Common_model->insert_info('gm_userprofile',array('user_id'=> $l_id,'phone_number'=>$phone_number,'created_date'=>date('Y-m-d H:i:s'),'modified_date'=>date('Y-m-d H:i:s')));
            
            if($l_id){
                $role_array = array();
                $role_array['userprofile_id']=$l_id;
                
                $role_array['created_date']=date('Y-m-d H:i:s');
                $role_array['modified_date']=date('Y-m-d H:i:s');
                /*get role id from  gm_epcroles where role name =user and vertical = null*/
                $rls =$this->Common_model->select_info('gm_epcroles',array('name'=>'user','vertical_id'=>NULL));
                
                $role_array['role_id']=$rls[0]['id'];/*Users*/
                $this->Common_model->insert_info('gm_epcuserprofileroles',$role_array);
            }
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE)
                {
                $op['status']= FALSE;
                $op['message']= "something went wrong";
                } else {
                $op['status']= TRUE;
                }
                
        }
        $this->response($op, REST_Controller::HTTP_OK); 
    }
	
	public function select_sku_model_list_post(){
        $vertical_id =$this->post('vertical_id');
        $sku_text = $this->post('sku_text');
        
        $data = $op =  array();
        if(empty($vertical_id)){
            $op['status'] =  FALSE;
            $op['message'] =  "Please send vertical Id";
            $this->response($op,  REST_Controller::HTTP_OK);
            return TRUE;
        }
        $this->db->select('*,skd.id AS skudetails_id');
        $this->db->from('gm_skudetails AS skd');
        $this->db->join('gm_productbrands AS pb','skd.brand_id=pb.id');
        $this->db->where('pb.brand_vertical_id',$vertical_id);
        $this->db->like('skd.sku_description',$sku_text);
        $this->db->limit(10);
        $query = $this->db->get();
        $sku_dtl = ($query->num_rows() > 0) ? $query->result_array() : FALSE;
        
        if($sku_dtl){
            foreach ($sku_dtl as $key => $value) {
                $data[$key]['skudetails_id'] =  $value['skudetails_id'];
                $data[$key]['sku_description'] =  $value['sku_description'];
            }
            $op['status'] = TRUE;
            $op['skudetails'] = $data;
        } else {
            $op['status'] =  FALSE;
        }
        $this->response($op,  REST_Controller::HTTP_OK);
    }

    public function add_user_vehical_post() {
		log_message('info',print_r($this->post(), TRUE));
        $api_type =$this->post('api_type'); /* 1=> selection of vin, 2=> Add information manually*/
        $user_id =$this->post('user_id');        
        $product_id =$this->post('product_id');
        
        $manufacturing_month =$this->post('manufacturing_month');
        $manufacturing_year =$this->post('manufacturing_year');
        $vertical_id =$this->post('vertical_id');
        $skudetails_id =$this->post('skudetails_id');
        
        $data_add = $op = array();
        /*vehical add */
        if($api_type == "vin"){
            /*get information of product_id in  gm_manufacturingdata */
         
        $this->db->select('md.id AS manufacturing_id,sku.id AS sku_id');
        $this->db->from('gm_manufacturingdata AS md');
        $this->db->join('gm_bomheader AS bh','bh.id=md.bomheader_id','left');
        $this->db->join('gm_skudetails AS sku','sku.sku_code=bh.sku_code','left');
        $this->db->where('product_id',$product_id);
        $query = $this->db->get();
        $manufacturing_info = ($query->num_rows() > 0) ? $query->result_array() : FALSE;
           if($manufacturing_info){
               
               $data_add['manufacturingdata_id']=$manufacturing_info[0]['manufacturing_id'];
               $data_add['user_id']=$user_id;
               $data_add['product_id']=$product_id;
               $data_add['vertical_id']=$vertical_id;
               $data_add['skudetails_id']=$manufacturing_info[0]['sku_id'];
               $data_add['active']=1;
               
          }  else {
                $op['status']= FALSE;
                $op['message']= "Sorry No Vin Found";
                $this->response($op, REST_Controller::HTTP_OK);         
                return TRUE;
           } 
            
        } else if($api_type == "no_vin"){
            $data_add['user_id'] = $user_id;
            $data_add['skudetails_id'] = $skudetails_id;
            $data_add['manufacturing_month'] = $manufacturing_month;
            $data_add['manufacturing_year'] = $manufacturing_year;
            $data_add['vertical_id'] = $vertical_id;
		if(empty($skudetails_id)){
                $op['status']= FALSE;
                $op['message']= "Please select Models from  list";
                $this->response($op, REST_Controller::HTTP_OK);         
                return TRUE;
            }
        }
        $this->db->trans_start();
        $data_add['create_date']=date('Y-m-d H:i:s');
        $data_add['added_from']=1; /* FROM  APPLICATION */
        $this->Common_model->insert_info('gm_epc_user_vehical',$data_add);
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
        $op['status']= FALSE;
        $op['message']= "something went wrong";
        } else {
        $op['status']= TRUE;
        }
        $this->response($op, REST_Controller::HTTP_OK);         
    }
	
	public function view_vehical_list_post() {
        $img_base = "http://gladminds-connect.s3.amazonaws.com/";
        $user_id =$this->post('user_id');        
        /*get the list off  all  register vehical address */
        $this->db->select('*,uv.id AS vehical_id');
        $this->db->from('gm_epc_user_vehical AS uv');
        $this->db->join('gm_skudetails AS sku','uv.skudetails_id = sku.id','left');
        $this->db->where('uv.user_id',$user_id);
        $query = $this->db->get();
        $vehical_list = ($query->num_rows() > 0) ? $query->result_array() : FALSE;
        $data = $op = array();
        if($vehical_list){
            foreach ($vehical_list as $key => $value) {
                $data[$key]['vehical_id'] =  $value['vehical_id'];
                $data[$key]['product_id'] =  $value['product_id'];
                $data[$key]['skudetails_id'] =  $value['skudetails_id'];
                $data[$key]['sku_code'] =  $value['sku_code'];
                $data[$key]['model_name'] =  $value['sku_description'];
                $data[$key]['image_url'] =  $img_base.$value['image_url'];
                $data[$key]['manufacturing_month'] =  $value['manufacturing_month'];
                $data[$key]['manufacturing_year'] =  $value['manufacturing_year'];
		$data[$key]['manual_url'] = !empty($value['manual_url']) ? $value['manual_url'] : "https://www.globalbajaj.com/media/21022/dominar-400-om-mar17.pdf";
            }
            $op['data'] =  $data;
            $op['status'] =  TRUE;
        } else {
            $op['status'] =  FALSE;
            $op['message'] =  "No vehical vaailable";
            
        }
        $this->response($op, REST_Controller::HTTP_OK);  
    }

}
/*pbkdf2_sha256$12000$W0ocHYIS7bjZ$CI/8rYGbTMKNSm3+nu9HSZlOQzossSap/arfF3/SfXM=*/

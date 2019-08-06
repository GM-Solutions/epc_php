<?php
/**
 * Description of Table_mapping
 *
 * @author pavaningalkar
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Table_mapping extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model("Common_model");
    }
    function mc_distributor_mapping() {
        /*get role id */
        
        $this->db->select('*');
        $this->db->from('gm_epcroles');
        $this->db->where('name','Distributor');        
        $this->db->where('vertical_id','1');        
        $query001 = $this->db->get();
        $role_data = ($query001->num_rows() > 0)? $query001->result_array():FALSE;
        
        
        if($role_data ==  FALSE){
            echo "Sorry No distributor Found"; die;
        }
         $distributor_id = $role_data[0]['id']; 
        
        $this->db->select('*');
        $this->db->from('gm_sfa_mc_distributor');
        $this->db->where('user_id is NULL');        
        $query = $this->db->get();
        $mc_distributor = ($query->num_rows() > 0)? $query->result_array():FALSE;
        
        $sfa_email =  array();
        if($mc_distributor){
            foreach ($mc_distributor as $key => $value) {
                
                $sfa_email[]=$value['email_bajaj'];
                $sfa_email[]=$value['email'];
            }
            
            /* search list of all email in auth user tables */
            
            $this->db->select('*');
            $this->db->from('auth_user');
            $this->db->where_in('email',$sfa_email);   
            $this->db->where('email != ""');   
            
            $query0 = $this->db->get();
            $epc_users = ($query0->num_rows() > 0)? $query0->result_array():FALSE;
            //print_r($epc_users); die;
            
            /* update mapping*/
            $ready_update_mapping_ids1 = $ready_update_mapping_ids2 =$chk_gm_epcuserprofileroles_raw =$chk_gm_epcuserprofileroles=  array();
            
            if($epc_users){
                foreach ($epc_users as $key => $value) {
                    $chk_gm_epcuserprofileroles_raw[$value['id']]['user_id'] = $value['id'];
                    $ready_update_mapping_ids1[]=array('user_id'=>$value['id'],'email'=>$value['email']);
                    $ready_update_mapping_ids2[]=array('user_id'=>$value['id'],'email_bajaj'=>$value['email']);
                }
                foreach ($chk_gm_epcuserprofileroles_raw as $key_raw => $value_raw) {
                    $chk_gm_epcuserprofileroles[]=$value_raw['user_id'];
                }
                /*check  details in gm_epcuserprofileroles => gm_epcroles */
                $this->db->select('*');
                $this->db->from('gm_epcuserprofileroles');
                $this->db->where_in('userprofile_id',$chk_gm_epcuserprofileroles);
                $this->db->where('role_id',$distributor_id);
                $query1 = $this->db->get();
                $profile_role = ($query1->num_rows() > 0)? $query1->result_array():FALSE;
                
                $chk_gm_epcuserprofileroles_final = $chk_gm_epcuserprofileroles;
                foreach ($chk_gm_epcuserprofileroles_final as $key_r => $value_r) {
                    if($profile_role){
                    foreach ($profile_role as $key_pr => $value_pr) {
                        if($value_pr['userprofile_id'] == $value_r){ 
                            
                            if (($key_val = array_search($value_r, $chk_gm_epcuserprofileroles)) !== false) {
                                    unset($chk_gm_epcuserprofileroles[$key_val]);
                                }
                        }
                            }
                        }
                }
            }
            
            /* make entry in roles gm_epcuserprofileroles */
            if($chk_gm_epcuserprofileroles) {
                $role_insert = array();
                $i=0;
                foreach ($chk_gm_epcuserprofileroles as $key => $value) {
                    $role_insert[$i]['userprofile_id'] = $value;
                    $role_insert[$i]['role_id'] = $distributor_id;
                    $i++;
                }
                $this->db->insert_batch('gm_epcuserprofileroles',$role_insert);
            }
            if($ready_update_mapping_ids1)
            $this->db->update_batch('gm_sfa_mc_distributor',$ready_update_mapping_ids1, 'email');             
            if($ready_update_mapping_ids2)
            $this->db->update_batch('gm_sfa_mc_distributor',$ready_update_mapping_ids2, 'email_bajaj');             
        }        
           
    }
    
    function mc_distributor_create_login(){ 
        $this->db->select('*');
        $this->db->from('gm_epcroles');
        $this->db->where('name','Distributor');        
        $this->db->where('vertical_id','1');        
        $query001 = $this->db->get();
        $role_data = ($query001->num_rows() > 0)? $query001->result_array():FALSE;
        
        
        if($role_data ==  FALSE){
            echo "Sorry No distributor Found"; die;
        }
        $distributor_id = $role_data[0]['id'];
        
        $this->db->select('*');
        $this->db->from('gm_sfa_mc_distributor');
        $this->db->where('user_id is NULL'); 
        $this->db->limit(100);  
        $query = $this->db->get();
        $mc_distributor = ($query->num_rows() > 0)? $query->result_array():FALSE;
        //print_r($mc_distributor); die;
        $sfa_data =  array();
        if($mc_distributor){
            foreach ($mc_distributor as $key => $value) {
		/*check for role and auth user*/
                $this->db->select('*');
                $this->db->from('auth_user AS au');
                $this->db->join('gm_epcuserprofileroles AS role','au.id = role.userprofile_id','left');
                $this->db->where('au.email',$value['email_bajaj']);   
                $this->db->where('role_id is NOT NULL');   
                $query11 = $this->db->get();
                $mcdist = ($query11->num_rows() > 0)? $query11->result_array():FALSE;
                if($mcdist){continue;}
                /*check for role and auth user */
                $sfa_data['password'] = common::django_pwd_generate('init@123', mt_rand(10000,99999).time());
                $sfa_data['email']=!empty($value['email_bajaj']) ? $value['email_bajaj'] : $value['email'];
                $sfa_data['first_name']=!empty($value['name']) ? $value['name'] : "";
                $sfa_data['is_staff']=0;
                $sfa_data['is_active']=1;
                $sfa_data['date_joined']=  date('Y-m-d H:i:s');
                $sfa_data['username']=  $sfa_data['email'];
                $this->db->trans_start();
                /*add profile and role details */
                $ins_id = $this->Common_model->insert_info('auth_user',$sfa_data);
                        
                  /* update gm_userprofile */
                        
                        $user_profilre_array =  array();
                        $user_profilre_array['created_date']=date('Y-m-d H:i:s');
                        $user_profilre_array['modified_date']=date('Y-m-d H:i:s');
                        $user_profilre_array['user_id']=$ins_id;
                        $this->Common_model->insert_info('gm_userprofile',$user_profilre_array);
                
                if($ins_id){
                    $role_array = array();
                    $role_array['userprofile_id']=$ins_id;
                    $role_array['role_id']=$distributor_id;/*Distributor*/
                    $role_array['created_date']=date('Y-m-d H:i:s');
                    $role_array['modified_date']=date('Y-m-d H:i:s');
                    $this->Common_model->insert_info('gm_epcuserprofileroles',$role_array);
                    
                    /*update roles in  mc distributors mapping */
                    
                    $this->Common_model->update_info('gm_sfa_mc_distributor',array('user_id'=>$ins_id),array('id'=>$value['id']));
                    
                    
                }
                $this->db->trans_complete();
            }
        }
    }

    function mc_dealer_mapping() {
        /*get id of role from  gm_epcroles*/
        $role_id = $this->Common_model->select_info('gm_epcroles',array('name'=>'Dealer','vertical_id'=>MOTERCYCLE));
        
        $mc_dealer_role =  $role_id[0]['id'];
        $this->db->select('*');
        $this->db->from('gm_mc_dealer');
        $this->db->where('user_id',0);        
        $this->db->or_where('user_id',NULL);        
        $query = $this->db->get();
        $mc_distributor = ($query->num_rows() > 0)? $query->result_array():FALSE;
        
        
        if($mc_distributor){            
            /* search list of all email in auth user tables */            
            $this->db->select('*');
            $this->db->from('auth_user');
            $this->db->where_in('email',' select email from gm_mc_dealer where user_id is NULL',FALSE);   
            $this->db->where('email != ""');   
//            $this->db->limit(1000);
            $query0 = $this->db->get();
            $epc_users = ($query0->num_rows() > 0)? $query0->result_array():FALSE;
            
            print_r($epc_users);
            
            /* update mapping*/
            $ready_update_mapping_ids1  =$chk_gm_epcuserprofileroles_raw =$chk_gm_epcuserprofileroles=  array();
            
            if($epc_users){
                foreach ($epc_users as $key => $value) {
                    $chk_gm_epcuserprofileroles_raw[$value['id']]['user_id'] = $value['id'];
                    $ready_update_mapping_ids1[]=array('user_id'=>$value['id'],'email'=>$value['email']);
                   
                }
                foreach ($chk_gm_epcuserprofileroles_raw as $key_raw => $value_raw) {
                    $chk_gm_epcuserprofileroles[]=$value_raw['user_id'];
                }
                /*check  details in gm_epcuserprofileroles => gm_epcroles */
                $this->db->select('*');
                $this->db->from('gm_epcuserprofileroles');
                $this->db->where_in('userprofile_id',$chk_gm_epcuserprofileroles);
                $this->db->where('role_id',$mc_dealer_role);//Dealer
                $query1 = $this->db->get();
                $profile_role = ($query1->num_rows() > 0)? $query1->result_array():FALSE;
                
                $chk_gm_epcuserprofileroles_final = $chk_gm_epcuserprofileroles;
                foreach ($chk_gm_epcuserprofileroles_final as $key_r => $value_r) {
                    if($profile_role){
                    foreach ($profile_role as $key_pr => $value_pr) {
                        if($value_pr['userprofile_id'] == $value_r){ 
                            
                            if (($key_val = array_search($value_r, $chk_gm_epcuserprofileroles)) !== false) {
                                    unset($chk_gm_epcuserprofileroles[$key_val]);
                                }
                        }
                            }
                    
                        }
                }
            }
                        
            /* make entry in roles gm_epcuserprofileroles */
            if($chk_gm_epcuserprofileroles) {
                $role_insert = array();
                $i=0;
                foreach ($chk_gm_epcuserprofileroles as $key => $value) {
                    $role_insert[$i]['userprofile_id'] = $value;
                    $role_insert[$i]['role_id'] = $mc_dealer_role;
                    $i++;
                }
                $this->db->insert_batch('gm_epcuserprofileroles',$role_insert);
            }
            if($ready_update_mapping_ids1)
            $this->db->update_batch('gm_mc_dealer',$ready_update_mapping_ids1, 'email');             
            
        }         
    }
    
    function mc_dealer_create_login() {
        $role_id = $this->Common_model->select_info('gm_epcroles',array('name'=>'Dealer','vertical_id'=>MOTERCYCLE));
        
        echo $mc_dealer_role =  $role_id[0]['id']; die;
        $this->db->select('*');
        $this->db->from('gm_mc_dealer');
        $this->db->where('user_id is null');        
        $this->db->limit(100);        
        $query = $this->db->get();
        $mc_dealer = ($query->num_rows() > 0)? $query->result_array():FALSE;
        
        $sfa_data =  array();
        if($mc_dealer){
            foreach ($mc_dealer as $key => $value) {
                $sfa_data['password'] = common::django_pwd_generate('init@123', mt_rand(10000,99999).time());
                $sfa_data['email']= $value['email'];
                $phno = !empty($value['mobile1']) ? $value['mobile1'] : $value['mobile2'];
                $sfa_data['first_name']=!empty($value['dealer_name']) ? $value['dealer_name'] : "";
                $sfa_data['is_staff']=0;
                $sfa_data['is_active']=1;
                $sfa_data['date_joined']=  date('Y-m-d H:i:s');
                $sfa_data['username']=  $sfa_data['email'];
                $this->db->trans_start();
                /*add profile and role details */
                $ins_id = $this->Common_model->insert_info('auth_user',$sfa_data);
                        
                  /* update gm_userprofile */
                        
                        $user_profilre_array =  array();
                        $user_profilre_array['phone_number']=$phno;
                        $user_profilre_array['created_date']=date('Y-m-d H:i:s');
                        $user_profilre_array['modified_date']=date('Y-m-d H:i:s');
                        $user_profilre_array['user_id']=$ins_id;
                        $this->Common_model->insert_info('gm_userprofile',$user_profilre_array);
                
                if($ins_id){
                    $role_array = array();
                    $role_array['userprofile_id']=$ins_id;
                    $role_array['role_id']=$mc_dealer_role;/*Dealer*/
                    $role_array['created_date']=date('Y-m-d H:i:s');
                    $role_array['modified_date']=date('Y-m-d H:i:s');
                    $this->Common_model->insert_info('gm_epcuserprofileroles',$role_array);
                    
                    /*update roles in  mc distributors mapping */
                    
                    $this->Common_model->update_info('gm_mc_dealer',array('user_id'=>$ins_id),array('id'=>$value['id']));
                    
                    
                }
                $this->db->trans_complete();
            }
        }
    }
    
    
    
    public function import_cdms_dealer_to_gm_mc_dealer() { die;
        $master_id= 46;
        $this->db->trans_start(); /* start*/
        $this->db->select('*');
        $this->db->from('gm_cdms_dealer_data');
        $this->db->where('gm_cdms_dealer_master_id',$master_id);
        $this->db->where('is_processed',0);
        $this->db->limit(1000);
        $query = $this->db->get();
        $dealer_data = ($query->num_rows() > 0)? $query->result_array():FALSE;
        
        $chkids = $mc_dealer_info= array();
        if($dealer_data){
        foreach ($dealer_data as $key1 => $value) {
            
            $chkids[$key1]['id']=$value['id'];
            $chkids[$key1]['is_processed']=1;
            
            $key= $value['email'];
            $vech_data[$key]['brand_vertical'] = $value['brand_vertical'];
            $vech_data[$key]['dealer_code'] = $value['dealer_code'];
            $vech_data[$key]['dealer_name'] = $value['dealer_name'];
            $vech_data[$key]['email'] = $value['email'];
            $vech_data[$key]['mobile1'] = $value['mobile1'];
            $vech_data[$key]['mobile2'] = $value['mobile2'];
            $vech_data[$key]['shop_address'] = $value['shop_address'];
            $vech_data[$key]['city'] = $value['city'];
            $vech_data[$key]['state'] = $value['state'];
            $vech_data[$key]['pin_code'] = $value['pin_code'];
            $vech_data[$key]['latitude'] = $value['latitude'];
            $vech_data[$key]['longitude'] = $value['longitude'];
            $vech_data[$key]['created_date'] = $value['created_date'];
            $vech_data[$key]['modified_date'] = $value['modified_date'];
            
        } }
        
        if($vech_data){
            foreach ($vech_data as $key => $value) {
               if($value['brand_vertical'] == 1){/*mc*/
                $mc_dealer_info[]= $this->unsetkeys(array('brand_vertical'),$vech_data[$key]);
                } 
            }
        }
        
        if($mc_dealer_info){           // print_r($mc_dealer_info); die;
            /*remove array duplicacy itself*/
            $email_list =  array();
            
            foreach ($mc_dealer_info as $key => $value) {
                $email_list[]=$value['email'];
            }
            
        $mc_dealer_info_insert = $mc_dealer_info;
        $mc_dealer_info_update =  array();
                $this->db->select('*');
                $this->db->from('gm_mc_dealer');
                $this->db->where_in('email',$email_list);
                $query = $this->db->get();
                $dlr_updt = ($query->num_rows() > 0)? $query->result_array():FALSE;
                
                if($dlr_updt){
                    foreach ($mc_dealer_info_insert as $key => $value) {
                        foreach ($dlr_updt as $key_up => $value_up) {
                            if($value['email']==$value_up['email']){
                                $mc_dealer_info_update[] = $mc_dealer_info_insert[$key];
                                unset($mc_dealer_info_insert[$key]);  
                                break 1;
                            }
                        }
                    }
                }
                
                /*insert operation */
                if($mc_dealer_info_insert){
                    $dlr_info =  array();
                    foreach ($mc_dealer_info_insert as $key => $value) {
                        $dlr_info[]=$value;
                    }
                    $this->db->insert_batch('gm_mc_dealer', $dlr_info);
                    echo "Insert ----->";
                    print_r($dlr_info);
                    echo "Insert ----->";
                }                                
                /*Update operation*/
                
                if($mc_dealer_info_update){
                    $dlr_info =  array();                    
                    foreach ($mc_dealer_info_update as $key => $value) {
                        $dlr_info[]=$value;
                    }
                $this->db->update_batch('gm_mc_dealer',$dlr_info,'email');



                  echo "Update ----->";
                  print_r($dlr_info);
                  echo "Update ----->";
             }
     }

        /*update status of is_processed*/
        $this->db->update_batch('gm_cdms_dealer_data',$chkids,'id');
        $this->db->trans_complete(); /* end*/
        if ($this->db->trans_status() === FALSE) { // error

        }else{

            
        }
        }
    
private function unsetkeys($unset_keys =  array(),$array_name){
        foreach ($unset_keys as $key => $value) {
            unset($array_name[$value]);
        }
        return $array_name;
    }
    
    //****/
    public function test(){ die;
        $this->db->select(' o.id,o.order_number,
                b.name AS brand_name,
                address.house_no,
                address.apartment_name,                
                address.street_details,
                address.landmark_details,
                address.area_details,
                address.city,
                address.pin_code,
                mc_distributor.sfa_mc_distributor_id,
                mc_dealer.dealer_code AS mc_dealer_code,
                au_cust.first_name,
                au_cust.last_name,
                profile_cust.phone_number,od.part_number,od.quantity,o.order_by_role');
            $this->db->from('gm_orderpart as o');
            $this->db->join('gm_brandvertical AS b', 'o.brand_vertical_id = b.id', 'left');
            $this->db->join('gm_user_address_details AS address', 'address.id = o.user_address_id', 'left');
            $this->db->join('auth_user AS au_cust', 'address.user_id = au_cust.id', 'left');
            $this->db->join('gm_userprofile AS profile_cust', 'au_cust.id = profile_cust.user_id', 'left');
            $this->db->join('gm_sfa_mc_distributor AS mc_distributor', 'o.distributor_id = mc_distributor.id', 'left');
            $this->db->join('gm_mc_dealer AS mc_dealer', 'o.mc_dealer_id = mc_dealer.id', 'left');
            $this->db->join('gm_orderpart_details AS od', 'o.id = od.order_id', 'left');
            //$this->db->where('o.send_to_cdms', 'pending');
            $this->db->where('od.order_id is not null');
            $query = $this->db->get();
            $details = ($query->num_rows() > 0) ? $query->result_array() : FALSE;
            echo "<pre>";
            foreach ($details as $key => $value) {
                    $raw_order[$value['order_number']]['id'] = $value['id'];
                    $raw_order[$value['order_number']]['BrandVertical'] = $value['brand_name'];
                    
                    $raw_order[$value['order_number']]['OrderType'] = $value['order_by_role']; // user/ mechanic / retailer / distributor /dealer

                    $raw_order[$value['order_number']]['DealerCode'] = !empty($value['mc_dealer_code']) ? $value['mc_dealer_code'] : $value['sfa_mc_distributor_id'];

                    
                    $raw_order[$value['order_number']]['EPCOrderNo'] = $value['order_number'];
                    $raw_order[$value['order_number']]['CustomerName'] = $value['first_name'] . " " . $value['last_name'];
                    $raw_order[$value['order_number']]['PhoneNumber'] = $value['phone_number'];
                    $raw_order[$value['order_number']]['HouseNo'] = $value['house_no'];
                    $raw_order[$value['order_number']]['ApartmentName'] = $value['apartment_name'];
                    $raw_order[$value['order_number']]['StreetDetail'] = $value['street_details'];
                    $raw_order[$value['order_number']]['LandmarkDetails'] = $value['landmark_details'];
                    $raw_order[$value['order_number']]['AreaDetails'] = $value['area_details'];
                    $raw_order[$value['order_number']]['City'] = $value['city'];
                    $raw_order[$value['order_number']]['PinCode'] = $value['pin_code'];
                    $raw_order[$value['order_number']]['OrderParts']['PartDetails'][$key]['PartQuantity'] = $value['part_number'];
                    $raw_order[$value['order_number']]['OrderParts']['PartDetails'][$key]['PartNumber'] = $value['quantity'];
                }
                
                $i =0;
                
                foreach ($raw_order as $key => $value) {
                    $up_ids[]= $value['id'];                    
                    $order[$i]['BrandVertical'] = $value['BrandVertical'];
                    $order[$i]['OrderType'] = $value['OrderType'];
//                    $order[$i]['DistributorCode'] = $value['DistributorCode'];
                    $order[$i]['DealerCode'] = $value['DealerCode'];
                    $order[$i]['EPCOrderNo'] = $value['EPCOrderNo'];
                    $order[$i]['CustomerName'] = $value['CustomerName'];
                    $order[$i]['PhoneNumber'] = $value['PhoneNumber'];
                    $order[$i]['HouseNo'] = $value['HouseNo'];
                    $order[$i]['ApartmentName'] = $value['ApartmentName'];
                    $order[$i]['StreetDetail'] = $value['StreetDetail'];
                    $order[$i]['LandmarkDetails'] = $value['LandmarkDetails'];
                    $order[$i]['AreaDetails'] = $value['AreaDetails'];
                    $order[$i]['City'] = $value['City'];
                    $order[$i]['PinCode'] = $value['PinCode'];
                    $j = 0;
                    foreach ($value['OrderParts'] as $key_op => $value_op) {
                      if($value_op){
                          foreach ($value_op as $key_inner => $value_inner) {                            
                            $order[$i]['OrderParts']['PartDetails'][$j]['PartNumber'] =  $value_inner['PartNumber'];
                            $order[$i]['OrderParts']['PartDetails'][$j]['PartQuantity'] =  $value_inner['PartQuantity'];
                            $j++;
                        }
                      }                        
                    }
                
                $i++;
                }
                
                print_r($order);
    }
}

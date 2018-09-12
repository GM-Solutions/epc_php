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
        $this->db->select('*');
        $this->db->from('gm_sfa_mc_distributor');
        $this->db->where('user_id',null);        
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
            print_r($epc_users);
//            echo $this->db->last_query();
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
                /*check  details in gm_epcuserprofileroles */
                $this->db->select('*');
                $this->db->from('gm_epcuserprofileroles');
                $this->db->where_in('userprofile_id',$chk_gm_epcuserprofileroles);
                $this->db->where('role_id',15);
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
                    $role_insert[$i]['role_id'] = 15;
                    $i++;
                }
                $this->Common_model->insert_batch('gm_epcuserprofileroles',$role_insert);
            }
            
			if($ready_update_mapping_ids1) $this->db->update_batch('gm_sfa_mc_distributor',$ready_update_mapping_ids1, 'email');             
            
			if($ready_update_mapping_ids2) $this->db->update_batch('gm_sfa_mc_distributor',$ready_update_mapping_ids2, 'email_bajaj');             
        }        
        
        
    }
    
    function mc_distributor_create_login(){
        $this->db->select('*');
        $this->db->from('gm_sfa_mc_distributor');
        $this->db->where('user_id',null);        
        $query = $this->db->get();
        $mc_distributor = ($query->num_rows() > 0)? $query->result_array():FALSE;
        
        $sfa_data =  array();
        if($mc_distributor){
            foreach ($mc_distributor as $key => $value) {
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
                    $role_array['role_id']=15;/*Distributor*/
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
}

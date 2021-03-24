<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once(APPPATH.'libraries/CurlAsc.php');

class KTMSoapLogic extends CI_Controller {
    function __construct() {   
        $this->ci = & get_instance();
    }
    function postProductPurchase($Credentials,$PurchaseOrder) {    
        try {            
            $token_no = '';
            $op= $sms_log =  array();
            $configration = $this->ci->config->item('wsdlconf');
            
            
            $ktm_wsdl = $this->ci->config->item('ktmwsdlconf');
            if($ktm_wsdl['username'] != $Credentials['username'] || $ktm_wsdl['password'] != $Credentials['password']){
                $op['ticket_no'] = "NULL";
                $op['status'] = FALSE;
                $op['error'] = "Invalid Credentials";
                return $op;
            }
            
            $db_group = $this->ci->config->item('db_group');
            $this->ci->load->database($db_group['bajaj_db']);
            /*sms config*/
            $sms_con = $this->ci->config->item('sms');
            $sms_conf = $sms_con['india'];
            $sms_base = $sms_conf['message_url']."?aid=".$sms_conf['aid']."&pin=".$sms_conf['pin']."&signature=".$sms_conf['signature'];
            /*sms config*/
            file_put_contents($configration['log_path'].'wsdl_error_' . date("j.n.Y") . '.log', print_r($PurchaseOrder,TRUE), FILE_APPEND);           
            
            $this->ci->db->trans_start(); /* start*/
            $order_aray =  array();
            $master_id = NULL;
            if($PurchaseOrder['OrderDetail']){
                /*GET INDEX AND GENERATE TICKET NUMBER*/
                $this->ci->db->select('`AUTO_INCREMENT` AS nxt');
                $this->ci->db->from('INFORMATION_SCHEMA.TABLES');
                $this->ci->db->where('TABLE_SCHEMA',$this->ci->db->database);
                $this->ci->db->where('TABLE_NAME','gm_cdms_purchase_feed_master');
                $query = $this->ci->db->get();
                $index_data = $query->row();
                $token_no = Common::generate_booking_no($index_data->nxt,'','KTM-'.rand ( 10000 , 99999 ).'-');
                 /*create master data for log management */
                $master_data['ticket_no'] = $token_no;
                $master_data['log_status'] = FALSE;
                $master_data['created_date'] = date('Y-m-d H:i:s');
                 if($this->is_multi2($PurchaseOrder['OrderDetail'])){
                     $master_data['feed_data_count'] = count($PurchaseOrder['OrderDetail']);
                 } else {
                      $master_data['feed_data_count'] = 1;
                 }                
                
                $this->ci->db->insert('gm_cdms_purchase_feed_master', $master_data);  
                $master_id =  $this->ci->db->insert_id();
                
            }

            if($this->is_multi2($PurchaseOrder['OrderDetail'])){
            foreach ($PurchaseOrder['OrderDetail'] as $key => $value) {
                
                $order_aray[$key]['gm_cdms_purchase_feed_master_id']        =  $master_id;
                $order_aray[$key]['CUST_MOBILE']        =  ($value['CUST_MOBILE'] == "?" OR empty($value['CUST_MOBILE'] )) ?    NULL : $this->clean($value['CUST_MOBILE']);
                $order_aray[$key]['VEH_SL_DLR']         =  ($value['VEH_SL_DLR'] == "?" OR empty($value['VEH_SL_DLR'])) ?       NULL : $this->clean($value['VEH_SL_DLR']);
                $order_aray[$key]['ENGINE']             =  ($value['ENGINE'] == "?" OR empty($value['ENGINE'])) ?               NULL : $this->clean($value['ENGINE']);
                $order_aray[$key]['CITY']               =  ($value['CITY'] == "?" OR empty($value['CITY'])) ?                   NULL : $this->clean($value['CITY']);
                $order_aray[$key]['PIN_CODE']           =  ($value['PIN_CODE'] == "?" OR empty($value['PIN_CODE'])) ?           NULL : $this->clean($value['PIN_CODE']);
                $order_aray[$key]['TIMESTAMP']          =  ($value['TIMESTAMP'] == "?" OR empty($value['TIMESTAMP'])) ?         NULL : $this->clean($value['TIMESTAMP']);
                $order_aray[$key]['VEH_REG_NO']         =  ($value['VEH_REG_NO'] == "?" OR empty($value['VEH_REG_NO'])) ?       NULL : $this->clean($value['VEH_REG_NO']);
                $order_aray[$key]['STATE']              =  ($value['STATE'] == "?" OR empty($value['STATE'])) ?                 NULL : $this->clean($value['STATE']);
                $order_aray[$key]['CHASSIS']            =  ($value['CHASSIS'] == "?" OR empty($value['CHASSIS'])) ?             NULL : $this->clean($value['CHASSIS']);
                $order_aray[$key]['VEH_SL_DT']          =  ($value['VEH_SL_DT'] == "?" OR empty($value['VEH_SL_DT'])) ?         NULL : $this->clean($value['VEH_SL_DT']);
                $order_aray[$key]['CUSTOMER_ID']        =  ($value['CUSTOMER_ID'] == "?" OR empty($value['CUSTOMER_ID'])) ?     NULL : $this->clean($value['CUSTOMER_ID']);
                $order_aray[$key]['CUSTOMER_NAME']      =  ($value['CUSTOMER_NAME'] == "?" OR empty($value['CUSTOMER_NAME'])) ? NULL : $this->clean($value['CUSTOMER_NAME']);
            }
            }else {
                $key =0;
                $value = $PurchaseOrder['OrderDetail'];
                $order_aray[$key]['gm_cdms_purchase_feed_master_id']        =  $master_id;
                $order_aray[$key]['CUST_MOBILE']        =  ($value['CUST_MOBILE'] == "?" OR empty($value['CUST_MOBILE'] )) ?    NULL : $this->clean($value['CUST_MOBILE']);
                $order_aray[$key]['VEH_SL_DLR']         =  ($value['VEH_SL_DLR'] == "?" OR empty($value['VEH_SL_DLR'])) ?       NULL : $this->clean($value['VEH_SL_DLR']);
                $order_aray[$key]['ENGINE']             =  ($value['ENGINE'] == "?" OR empty($value['ENGINE'])) ?               NULL : $this->clean($value['ENGINE']);
                $order_aray[$key]['CITY']               =  ($value['CITY'] == "?" OR empty($value['CITY'])) ?                   NULL : $this->clean($value['CITY']);
                $order_aray[$key]['PIN_CODE']           =  ($value['PIN_CODE'] == "?" OR empty($value['PIN_CODE'])) ?           NULL : $this->clean($value['PIN_CODE']);
                $order_aray[$key]['TIMESTAMP']          =  ($value['TIMESTAMP'] == "?" OR empty($value['TIMESTAMP'])) ?         NULL : $this->clean($value['TIMESTAMP']);
                $order_aray[$key]['VEH_REG_NO']         =  ($value['VEH_REG_NO'] == "?" OR empty($value['VEH_REG_NO'])) ?       NULL : $this->clean($value['VEH_REG_NO']);
                $order_aray[$key]['STATE']              =  ($value['STATE'] == "?" OR empty($value['STATE'])) ?                 NULL : $this->clean($value['STATE']);
                $order_aray[$key]['CHASSIS']            =  ($value['CHASSIS'] == "?" OR empty($value['CHASSIS'])) ?             NULL : $this->clean($value['CHASSIS']);
                $order_aray[$key]['VEH_SL_DT']          =  ($value['VEH_SL_DT'] == "?" OR empty($value['VEH_SL_DT'])) ?         NULL : $this->clean($value['VEH_SL_DT']);
                $order_aray[$key]['CUSTOMER_ID']        =  ($value['CUSTOMER_ID'] == "?" OR empty($value['CUSTOMER_ID'])) ?     NULL : $this->clean($value['CUSTOMER_ID']);
                $order_aray[$key]['CUSTOMER_NAME']      =  ($value['CUSTOMER_NAME'] == "?" OR empty($value['CUSTOMER_NAME'])) ? NULL : $this->clean($value['CUSTOMER_NAME']);
            }            
          
            if($order_aray){                               
                $this->ci->db->insert_batch('gm_cdms_data_purchase_feed',$order_aray);
                /*dump data in gm_productdata*/
                /* remove duplicate data and seperate modified and insertable list */
                
                $ord_data =$chk_product_id =  $duplicate_product_ids =  array();
                foreach ($order_aray as $key => $value) {
                    $chk_product_id[] = $value['CHASSIS'];
                    $ord_data[$key]['product_id'] = $value['CHASSIS'];
                    $ord_data[$key]['customer_id'] = $value['CUSTOMER_ID'];
                    $ord_data[$key]['customer_phone_number'] = $value['CUST_MOBILE'];
                    $ord_data[$key]['customer_name'] = $value['CUSTOMER_NAME'];
                    $ord_data[$key]['customer_city'] = $value['CITY'];
                    $ord_data[$key]['customer_state'] = $value['STATE'];
                    $ord_data[$key]['customer_pincode'] = $value['PIN_CODE'];
                    $ord_data[$key]['purchase_date'] = $value['VEH_SL_DT'];
                    $ord_data[$key]['invoice_date'] = $value['TIMESTAMP'];
                    $ord_data[$key]['engine'] = $value['ENGINE'];
                    $ord_data[$key]['veh_reg_no'] = $value['VEH_REG_NO'];
                    $ord_data[$key]['is_active'] = 1;
                    $ord_data[$key]['created_date'] = date('Y-m-d H:i:s');
                    $ord_data[$key]['modified_date'] = date('Y-m-d H:i:s');
                }
                $update_order = $ord_data ;
                $new_update_order =  array();
                $this->ci->db->select('*');
                $this->ci->db->from('gm_productdata');
                $this->ci->db->where_in('product_id',$chk_product_id); 
                
                
                $query0 = $this->ci->db->get();
                $product_array = ($query0->num_rows() > 0)? $query0->result_array():FALSE;
                if($product_array){ /* if duplicate available filter here*/
                    foreach ($ord_data as $key => $value) {
                        foreach ($product_array as $key_chk => $value_chk) {
                            if($value['product_id'] == $value_chk['product_id']){                                
                                $new_update_order[] =  $ord_data[$key];
                                unset($ord_data[$key]);
                            }
                        }
                    }                    
                }
                
                /*insert data*/
                $final_ord_data =  array();
                $i=0;
                if($ord_data){
                    foreach ($ord_data as $key => $value) {

                        $final_ord_data[$i]['product_id'] = $value['product_id'];
                        //$final_ord_data[$i]['customer_id'] = $value['customer_id'];
                        $final_ord_data[$i]['customer_phone_number'] = $value['customer_phone_number'];
                        $final_ord_data[$i]['customer_name'] = $value['customer_name'];
                        $final_ord_data[$i]['customer_city'] = $value['customer_city'];
                        $final_ord_data[$i]['customer_state'] = $value['customer_state'];
                        $final_ord_data[$i]['customer_pincode'] = $value['customer_pincode'];
                        $final_ord_data[$i]['purchase_date'] = $value['purchase_date'];
                        $final_ord_data[$i]['invoice_date'] = $value['invoice_date'];
                        $final_ord_data[$i]['engine'] = $value['engine'];
                        $final_ord_data[$i]['veh_reg_no'] = $value['veh_reg_no'];
                        $final_ord_data[$i]['is_active'] = 1;
                        $final_ord_data[$i]['created_date'] = date('Y-m-d H:i:s');
                        $final_ord_data[$i]['modified_date'] = date('Y-m-d H:i:s');
                        $i++;
                    }
                    
                    /*INSERT Data*/
                    $row_count = $this->ci->db->insert_batch('gm_productdata',$final_ord_data);

                    if ($row_count ==0) {
                        throw new Exception("Duplicate VIN COde");
                    }
                }
                /*update data*/
                $final_new_update_order =  array();
                if($update_order){ //$new_update_order
                    $i=0;
                    foreach ($update_order as $key => $value) {
                        $final_new_update_order[$i]['product_id'] = $value['product_id'];
                        $final_new_update_order[$i]['customer_id'] = $value['customer_id'];
                        $final_new_update_order[$i]['customer_phone_number'] = $value['customer_phone_number'];
                        $final_new_update_order[$i]['customer_name'] = $value['customer_name'];
                        $final_new_update_order[$i]['customer_city'] = $value['customer_city'];
                        $final_new_update_order[$i]['customer_state'] = $value['customer_state'];
                        $final_new_update_order[$i]['customer_pincode'] = $value['customer_pincode'];
                        $final_new_update_order[$i]['purchase_date'] = $value['purchase_date'];
                        $final_new_update_order[$i]['invoice_date'] = $value['invoice_date'];
                       // $final_new_update_order[$i]['engine'] = $value['engine'];
                        $final_new_update_order[$i]['veh_reg_no'] = $value['veh_reg_no'];
                        $final_new_update_order[$i]['is_active'] = 1;                        
                        $final_new_update_order[$i]['modified_date'] = date('Y-m-d H:i:s');
                        $i++;
                    }
                    $this->ci->db->update_batch('gm_productdata',$final_new_update_order,'product_id');
                }
                
                
                /*send SMS */
                $i=0;
                $urls =  array();
                foreach ($order_aray as $key => $value) {
                    $template = ""; $replacements =  array();
                    /* get template for
                     * SEND_CUSTOMER_REGISTER_KTM_DUKE
                     * SEND_CUSTOMER_REGISTER_KTM_RC
                     */
                    $bike_type = $this->check_bike_type($value['CHASSIS']);
                    if($bike_type['is_ktm_duke']){
                        $this->ci->db->select('template');
                        $this->ci->db->from('gm_messagetemplate');
                        $this->ci->db->where('template_key','SEND_CUSTOMER_REGISTER_KTM_DUKE');
                        $query = $this->ci->db->get();
                        $template_dtl = $query->row();
                        
                        $template = $template_dtl->template;
                        
                        $replacements =  array(
                                            "{customer_name}"=>$value['CUSTOMER_NAME'],
                                            "{duke_android_url}"=>"https://tinyurl.com/y8oz3z2z",
                                            "{duke_web_url}"=>"http://ktmdukeweb.gladminds.co"
                                            );
                    }
                    if($bike_type['is_ktm_rc']){
                        $this->ci->db->select('template');
                        $this->ci->db->from('gm_messagetemplate');
                        $this->ci->db->where('template_key','SEND_CUSTOMER_REGISTER_KTM_RC');
                        $query = $this->ci->db->get();
                        $template_dtl = $query->row();
                        
                        $template = $template_dtl->template;
                        
                        $replacements =  array(
                                            "{customer_name}"=>$value['CUSTOMER_NAME'],
                                            "{rc_android_url}"=>"http://tinyurl.com/z3l7rlk",
                                            "{rc_web_url}"=>"http://ktmrcweb.gladminds.co"
                                            );
                    }
                    /*created_date,modified_date,action,message,sender,receiver,status*/
                    if($bike_type['is_ktm_duke'] || $bike_type['is_ktm_rc']){
                    $msg = str_replace(array_keys($replacements), $replacements, $template);
                    $urls[] = $sms_base."&mnumber=".$value['CUST_MOBILE']."&message=".urlencode ($msg);
                    
                    $sms_log[$i]['created_date']= date('Y-m-d H:i:s');
                    $sms_log[$i]['modified_date']= date('Y-m-d H:i:s');
                    $sms_log[$i]['action']= 'SEND TO QUEUE';
                    $sms_log[$i]['message']= $msg;
                    $sms_log[$i]['sender']= '+1 469-513-9856';
                    $sms_log[$i]['receiver']= $value['CUST_MOBILE'];
                    $sms_log[$i]['status']= 'success';
                    $i++;
                    }
                }
                if($urls){
                $getter = new CurlAsc($urls);
                $this->ci->db->insert_batch('gm_smslog',$sms_log);
                }
            }
      
            $this->ci->db->trans_complete(); /* end*/
       
            if ($this->ci->db->trans_status() === FALSE) { // error
                $op['ticket_no'] = $token_no;
                $op['status'] = FALSE;
                $op['error'] = "Sorry No Update ";
            }else{
                $this->ci->db->update('gm_cdms_purchase_feed_master', array('log_status'=>TRUE),array('id'=>$master_id)); 
                $op['ticket_no'] = $token_no;
                $op['status'] = TRUE;                
                $op['message'] = "Update success ";
            }
             } catch (Exception $e){
                $op['ticket_no'] = $token_no;
                $op['status'] = FALSE;
                $op['error'] = $e->getMessage();
                
        }
            return $op;
    }
    
    function is_multi2($a) {
    foreach ($a as $v) {
        if (is_array($v)) return true;
    }
    return false;
}

private function check_bike_type($vin){
    $is_ktm_duke = $is_ktm_rc= FALSE;
    if(substr($vin,0,3) == 'MD2'){
        if(substr($vin,3,1) == 'J'){
            if(
                    (substr($vin,4,1) == 'U' || substr($vin,4,1) == 'G')
                    || (substr($vin,4,3) == 'PEY' || substr($vin,4,3) == 'PJY' || substr($vin,4,3) == 'PJY')
                    )
            {
            $is_ktm_duke = TRUE;
            } else if(substr($vin,4,1) == 'Y'){
                $is_ktm_rc =  TRUE;
            }
        }
    }
    return array('is_ktm_duke'=>$is_ktm_duke,'is_ktm_rc'=>$is_ktm_rc);
}

private function clean($string) {
   $string =  preg_replace('/[^A-Za-z0-9\-]/', ' ', $string); // Removes special chars.  

   return trim($string ," \t\n\x0B\r");
}

}

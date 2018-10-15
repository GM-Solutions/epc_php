<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once(APPPATH.'libraries/CurlAsc.php');

class KTMSoapLogic extends CI_Controller {
    function __construct() {   
        $this->ci = & get_instance();
    }
    function postProductPurchase($PurchaseOrder) {    
        try {
            $token_no = '';
            $op =  array();
            $configration = $this->ci->config->item('wsdlconf');
            /*sms config*/
            $sms_con = $this->ci->config->item('sms');
            $sms_conf = $sms_con['india'];
            $sms_base = $sms_conf['message_url']."?aid=".$sms_conf['aid']."&pin=".$sms_conf['pin']."&signature=".$sms_conf['signature'];
            /*sms config*/
            file_put_contents($configration['log_path'].'wsdl_error_' . date("j.n.Y") . '.log', print_r($PurchaseOrder,TRUE), FILE_APPEND);           
            $this->ci->load->database('bajaj_qa');
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
                $master_data['feed_data_count'] = count($PurchaseOrder['OrderDetail']);
                $this->ci->db->insert('gm_cdms_purchase_feed_master', $master_data);  
                $master_id =  $this->ci->db->insert_id();
                
            }
            if($this->is_multi2($PurchaseOrder['OrderDetail'])){
            foreach ($PurchaseOrder['OrderDetail'] as $key => $value) {
                
                $order_aray[$key]['gm_cdms_purchase_feed_master_id']        =  $master_id;
                $order_aray[$key]['CUST_MOBILE']        =  ($value['CUST_MOBILE'] == "?" OR empty($value['CUST_MOBILE'] )) ?    NULL : $value['CUST_MOBILE'];
                $order_aray[$key]['VEH_SL_DLR']         =  ($value['VEH_SL_DLR'] == "?" OR empty($value['VEH_SL_DLR'])) ?       NULL : $value['VEH_SL_DLR'];
                $order_aray[$key]['ENGINE']             =  ($value['ENGINE'] == "?" OR empty($value['ENGINE'])) ?               NULL : $value['ENGINE'];
                $order_aray[$key]['CITY']               =  ($value['CITY'] == "?" OR empty($value['CITY'])) ?                   NULL : $value['CITY'];
                $order_aray[$key]['PIN_CODE']           =  ($value['PIN_CODE'] == "?" OR empty($value['PIN_CODE'])) ?           NULL : $value['PIN_CODE'];
                $order_aray[$key]['TIMESTAMP']          =  ($value['TIMESTAMP'] == "?" OR empty($value['TIMESTAMP'])) ?         NULL : $value['TIMESTAMP'];
                $order_aray[$key]['VEH_REG_NO']         =  ($value['VEH_REG_NO'] == "?" OR empty($value['VEH_REG_NO'])) ?       NULL : $value['VEH_REG_NO'];
                $order_aray[$key]['STATE']              =  ($value['STATE'] == "?" OR empty($value['STATE'])) ?                 NULL : $value['STATE'];
                $order_aray[$key]['CHASSIS']            =  ($value['CHASSIS'] == "?" OR empty($value['CHASSIS'])) ?             NULL : $value['CHASSIS'];
                $order_aray[$key]['VEH_SL_DT']          =  ($value['VEH_SL_DT'] == "?" OR empty($value['VEH_SL_DT'])) ?         NULL : $value['VEH_SL_DT'];
                $order_aray[$key]['CUSTOMER_ID']        =  ($value['CUSTOMER_ID'] == "?" OR empty($value['CUSTOMER_ID'])) ?     NULL : $value['CUSTOMER_ID'];
                $order_aray[$key]['CUSTOMER_NAME']      =  ($value['CUSTOMER_NAME'] == "?" OR empty($value['CUSTOMER_NAME'])) ? NULL : $value['CUSTOMER_NAME'];
            }
            }else {
                $key =0;
                $value = $PurchaseOrder['OrderDetail'];
                $order_aray[$key]['gm_cdms_purchase_feed_master_id']        =  $master_id;
                $order_aray[$key]['CUST_MOBILE']        =  ($value['CUST_MOBILE'] == "?" OR empty($value['CUST_MOBILE'] )) ?    NULL : $value['CUST_MOBILE'];
                $order_aray[$key]['VEH_SL_DLR']         =  ($value['VEH_SL_DLR'] == "?" OR empty($value['VEH_SL_DLR'])) ?       NULL : $value['VEH_SL_DLR'];
                $order_aray[$key]['ENGINE']             =  ($value['ENGINE'] == "?" OR empty($value['ENGINE'])) ?               NULL : $value['ENGINE'];
                $order_aray[$key]['CITY']               =  ($value['CITY'] == "?" OR empty($value['CITY'])) ?                   NULL : $value['CITY'];
                $order_aray[$key]['PIN_CODE']           =  ($value['PIN_CODE'] == "?" OR empty($value['PIN_CODE'])) ?           NULL : $value['PIN_CODE'];
                $order_aray[$key]['TIMESTAMP']          =  ($value['TIMESTAMP'] == "?" OR empty($value['TIMESTAMP'])) ?         NULL : $value['TIMESTAMP'];
                $order_aray[$key]['VEH_REG_NO']         =  ($value['VEH_REG_NO'] == "?" OR empty($value['VEH_REG_NO'])) ?       NULL : $value['VEH_REG_NO'];
                $order_aray[$key]['STATE']              =  ($value['STATE'] == "?" OR empty($value['STATE'])) ?                 NULL : $value['STATE'];
                $order_aray[$key]['CHASSIS']            =  ($value['CHASSIS'] == "?" OR empty($value['CHASSIS'])) ?             NULL : $value['CHASSIS'];
                $order_aray[$key]['VEH_SL_DT']          =  ($value['VEH_SL_DT'] == "?" OR empty($value['VEH_SL_DT'])) ?         NULL : $value['VEH_SL_DT'];
                $order_aray[$key]['CUSTOMER_ID']        =  ($value['CUSTOMER_ID'] == "?" OR empty($value['CUSTOMER_ID'])) ?     NULL : $value['CUSTOMER_ID'];
                $order_aray[$key]['CUSTOMER_NAME']      =  ($value['CUSTOMER_NAME'] == "?" OR empty($value['CUSTOMER_NAME'])) ? NULL : $value['CUSTOMER_NAME'];
            }            
            
            if($order_aray){                               
                $this->ci->db->insert_batch('gm_cdms_data_purchase_feed',$order_aray);
                /*dump data in gm_productdata*/
                $ord_data =  array();
                foreach ($order_aray as $key => $value) {
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
                $row_count = $this->ci->db->insert_batch('gm_productdata',$ord_data);
        
                if ($row_count ==0) {
                    throw new Exception("Duplicate VIN COde");
                }
                
                /*send SMS */
                
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
                    
                    $msg = str_replace(array_keys($replacements), $replacements, $template);
                    $urls[] = $sms_base."&mnumber=".$value['CUST_MOBILE']."&message=".urlencode ($msg);
                }
                $getter = new CurlAsc($urls);
                
            }
      
            $this->ci->db->trans_complete(); /* end*/
       
            if ($this->ci->db->trans_status() === FALSE) { // error
                $op['ticket_no'] = $token_no;
                $op['status'] = FALSE;
                $op['error'] = "No New Orders Available ";
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

}

<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class EPCOrderPush extends CI_Controller { /* /home/ubuntu/ffa_prod/gladminds-report-dashboard-1.0-SNAPSHOT/conf/insert-data-scripts/primary_report_cron.sh */

    function __construct() {
        parent::__construct();
        $this->load->library("nuSoap_lib"); //load the library here
//        ob_end_clean();
        $this->load->database();
        $this->load->model("Common_model");
    }
    public function schedulepush_order() {
        $configration = $this->config->item('wsdlconf');
        /* push the data */
        
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
                au_cust.first_name,
                au_cust.last_name,
                profile_cust.phone_number,od.part_number,od.quantity');
            $this->db->from('gm_orderpart as o');
            $this->db->join('gm_brandvertical AS b', 'o.brand_vertical_id = b.id', 'left');
            $this->db->join('gm_user_address_details AS address', 'address.id = o.user_address_id', 'left');
            $this->db->join('auth_user AS au_cust', 'address.user_id = au_cust.id', 'left');
            $this->db->join('gm_userprofile AS profile_cust', 'au_cust.id = profile_cust.user_id', 'left');
            $this->db->join('gm_sfa_mc_distributor AS mc_distributor', 'o.distributor_id = mc_distributor.id', 'left');
            $this->db->join('gm_orderpart_details AS od', 'o.id = od.order_id', 'left');
            $this->db->where('o.send_to_cdms', 'pending');
            $this->db->where('od.order_id is not null');
            $query = $this->db->get();
            $details = ($query->num_rows() > 0) ? $query->result_array() : FALSE;
            
            /*if data is available push the data*/
            if ($details) {
                $proxyhost = '';
                $proxyport = '';
                $proxyusername = '';
                $proxypassword = '';
                $client = new nusoap_client('http://crtesting.excelloncloud.com/balcrtest/WS/InterfaceWebService.asmx?WSDL', 'wsdl', $proxyhost, $proxyport, $proxyusername, $proxypassword, 600, 600);
                $err = $client->getError();
                $raw_order = $order = $up_ids = array();
                if ($err) {
                    echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
                }
                
                foreach ($details as $key => $value) {
                    $raw_order[$value['order_number']]['id'] = $value['id'];
                    $raw_order[$value['order_number']]['BrandVertical'] = $value['brand_name'];
                    $raw_order[$value['order_number']]['OrderType'] = "user"; // user/ mechanic / retailer / distributor /dealer
                    $raw_order[$value['order_number']]['DealerCode'] = $value['sfa_mc_distributor_id'];
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
                echo "<pre>";
                $i =0;
                
                foreach ($raw_order as $key => $value) {
                    $up_ids[]= $value['id'];                    
                    $order[$i]['BrandVertical'] = $value['BrandVertical'];
                    $order[$i]['OrderType'] = $value['OrderType'];
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
                /*re structure Array*/
                /*Send data to CDMS Server*/
                $req_data = array('UserName' => 'balcrtest.espl','Password'=>'123','ClientType'=>7,'EntityType'=>0,'MappingID'=>0,'OrderData'=>$order);
                $result = $client->call('PushEPCOrder', array('request' => $req_data), '', '', false, true);
                if ($client->fault) {
                    print_r($result);
                } else {
                    
                        // Check for errors
                        $err = $client->getError();
                        if ($err) {
                                // Display the error
                                echo '<h2>Error</h2><pre>' . $err . '</pre>';
                        } else {
                                // Display the result
                                echo '\n Sent Data --- '.date('Y-m-d H:i:s');
                                
                                $master_data= $result['PushEPCOrderResult'];
                                $dtl['IsRequestSuccessful'] = (boolean)$master_data['IsRequestSuccessful'];
                                $dtl['ErrorCode'] = $master_data['ErrorCode'];
                                $dtl['RequestKey'] = $master_data['RequestKey'];
                                $dtl['ServerProcessingTicks'] = $master_data['ServerProcessingTicks'];
                                $dtl['ProcessingServer'] = $master_data['ProcessingServer'];
                                $dtl['ResponseKey'] = $master_data['ResponseKey'];
                                
                                $insert_id = $this->Common_model->insert_info('gm_send_to_cdms_master',$dtl);                                
                               
                                $this->db->set('gm_send_to_cdms_master_id',$insert_id,false);
                                $this->db->set('send_to_cdms','sent');
                                $this->db->set('send_to_cdms_date',date('Y-m-d H:i:s'));
                                $this->db->where_in('id',$up_ids,false);
                                $this->db->update('gm_orderpart');                               
                        }
                }
                /*On Send Success Mark As sent to CDMS AND Save name of Batch number*/ 
                
        }
    }

}

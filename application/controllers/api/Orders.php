<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends REST_Controller {

    //put your code here
    function __construct() {
        // Construct the parent class
        parent::__construct();
        $this->load->model("Common_model");
    }

    public function user_address_post() {
        $user_id = $this->post('user_id');
        $api_type = $this->post('api_type');
        $default_address = $this->post('default_address'); 
        

        $add_address = array();
        if(!empty($this->post('house_no'))) $add_address['house_no'] = $this->post('house_no');
        
        if(!empty($this->post('apartment_name'))) $add_address['apartment_name'] = $this->post('apartment_name');
        
        if(!empty($this->post('street_details'))) $add_address['street_details'] = $this->post('street_details');
        
        if(!empty($this->post('landmark_details'))) $add_address['landmark_details'] = $this->post('landmark_details');
        
        if(!empty($this->post('area_details'))) $add_address['area_details'] = $this->post('area_details');
        
        if(!empty($this->post('city'))) $add_address['city'] = $this->post('city');
        
        if(!empty($this->post('pin_code'))) $add_address['pin_code'] = $this->post('pin_code');
        
        if(!empty($this->post('type'))) $add_address['type'] = $this->post('type'); /* 'home','office','other' */
        
        if(!empty($this->post('default_address'))) $add_address['default_address'] = $this->post('default_address');
        
        $add_address['active'] = ($this->post('active') == 0 )  ?  0 : 1;
        if ($api_type == "addnew") {
            $add_address['active'] = 1;
            /* update all address as not default */
            $this->Common_model->update_info('gm_user_address_details',array('default_address'=>0),array('user_id'=>$user_id));
            $add_address['default_address'] = 1;
            
            $add_address['user_id'] = $user_id;
            $this->Common_model->insert_info('gm_user_address_details', $add_address);           
            
           
        } elseif ($api_type == "update") {
            $up_cond['id'] = $this->post('address_id');
            $this->Common_model->update_info('gm_user_address_details', $add_address, $up_cond);
            if ($default_address == 1) {
                /*update all address as not default*/
                $this->Common_model->update_info('gm_user_address_details',array('default_address'=>0),array('user_id'=>$user_id));
                /* update this address as default*/
                $this->Common_model->update_info('gm_user_address_details',array('default_address'=>1),$up_cond);
            }
        }

        /* send address details */
        $op = array();
        $address_dtl = $this->Common_model->select_info('gm_user_address_details', array('active' => 1,'user_id'=>$user_id));
        if ($address_dtl) {
            $op['status'] = TRUE;
            foreach ($address_dtl AS $key => $val) {

                $op['address'][$key]['address_id'] = $val['id'];
                $op['address'][$key]['house_no'] = $val['house_no'];
                $op['address'][$key]['apartment_name'] = $val['apartment_name'];
                $op['address'][$key]['street_details'] = $val['street_details'];
                $op['address'][$key]['landmark_details'] = $val['landmark_details'];
                $op['address'][$key]['area_details'] = $val['area_details'];
                $op['address'][$key]['city'] = $val['city'];
                $op['address'][$key]['pin_code'] = $val['pin_code'];
                $op['address'][$key]['type'] = $val['type'];
                $op['address'][$key]['default_address'] = $val['default_address'];
            }
        } else {
            $op['status'] = FALSE;
            $op['message'] = "Sorry No Address available";
        }
        
        $this->response($op, REST_Controller::HTTP_OK); 
    }

    public function select_roq_list_post() {
        $vertical_id = $this->post('vertical_id');
        $range = $this->post('range'); /* cover kilometers */
        $latitude = $this->post('latitude');
        $longitude = $this->post('longitude');
        /* 1= MC
         * 2=CV
         * 3=PB
         * 4=IB
         * 
         * CV => Distributor and User     
         * 
         *  
         */
        $vertical_id = !empty($vertical_id) ?  $vertical_id : 1;
        $dtl =$op=  array();
        if($vertical_id == 1){ /*MC Distributor */
            $this->db->select('*,sd.id AS shop_address_id');
            $this->db->from('gm_epc_shop_details AS sd');
            $this->db->join('gm_sfa_mc_distributor AS mcd','sd.epc_mc_distributor_id=mcd.id');
            $this->db->where('sd.active','1');
            $query = $this->db->get();
            $shopdetails = ($query->num_rows() > 0)? $query->result_array():FALSE;
            
            if($shopdetails){
                foreach ($shopdetails as $key => $value) {
                    $dtl[$key]['name']= $value['name'];
					$dtl[$key]['shop_address_id']= $value['shop_address_id'];
                    $dtl[$key]['distributor_id']= $value['epc_mc_distributor_id'];
					$dtl[$key]['email']= $value['email_bajaj'];
					$dtl[$key]['phone_number']= !empty($value['phone_number']) ? $value['phone_number'] : $value['mobile1'];
                    $dtl[$key]['address']= $value['address'];
                    $dtl[$key]['city']= $value['city'];
                    $dtl[$key]['state']= $value['state'];
                    $dtl[$key]['pin_code']= $value['pin_code'];
                    $dtl[$key]['latitude']= $value['latitude'];
                    $dtl[$key]['longitude']= $value['longitude'];
                    $dtl[$key]['distance']= "1 Km";
                }
            }
            
        }
        if($dtl){
            $op['status']=TRUE;
            $op['data']=$dtl;
        }else{
            $op['status']=FALSE;
            $op['message']="No Shop Found";
        }
        $this->response($op, REST_Controller::HTTP_OK); 
    }
    private function distance($lat1, $lon1, $lat2, $lon2, $unit) {$theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
          return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
          } else {
              return $miles;
            }  
            /*
             * echo distance(32.9697, -96.80322, 29.46786, -98.53506, "M") . " Miles<br>";
                echo distance(32.9697, -96.80322, 29.46786, -98.53506, "K") . " Kilometers<br>";
                echo distance(32.9697, -96.80322, 29.46786, -98.53506, "N") . " Nautical Miles<br>";
             */
    }
	
	
    public function create_order_post() {
        $vertical_id = $this->post('vertical_id');
        $user_id = $this->post('user_id');
        $disributor_id = $this->post('distributor_id');
        $latitude = $this->post('latitude');
        $longitude = $this->post('longitude');
        $address_id = $this->post('address_id');
        $shop_address_id = $this->post('shop_address_id');
//        gm_orderpart_details
        $part_dtl = $this->post('part_dtl');
        /* generate order number */
        $dtl_info = array();
        $this->db->trans_start();
        $index = $this->Common_model->increment_index('gm_orderpart');
        $order_no = Common::generate_booking_no($index->nxt);
        /* gm_orderpart */
        $orderpart['order_number'] = $order_no;
        $orderpart['distributor_id'] = $disributor_id;
        $orderpart['order_placed_user_id'] = $user_id;
        $orderpart['order_placed_from'] = '1'; /* 1= App,2=Web */
        $orderpart['latitude'] = $latitude;
        $orderpart['longitude'] = $longitude;
        $orderpart['user_address_id'] = $address_id;
        $orderpart['order_date'] = date("Y-m-d h:i:s");
        $orderpart['created_date'] = date("Y-m-d h:i:s");
        $orderpart['modified_date'] = date("Y-m-d h:i:s");
        $orderpart['brand_vertical_id'] = $vertical_id;
        $orderpart['shop_address_id'] = $shop_address_id;

        $order_dtl = $this->Common_model->insert_info('gm_orderpart', $orderpart);
        $order_part_dtl = array();
        if ($order_dtl) {
            foreach ($part_dtl AS $key => $value) {
                $order_part_dtl[$key]['order_id'] = $order_dtl;
                $order_part_dtl[$key]['bom_plate_part_id'] = $value['bom_plate_part_id'];
                $order_part_dtl[$key]['part_number'] = $value['part_number'];
                $order_part_dtl[$key]['quantity'] = $value['quantity'];

                $order_part_dtl[$key]['active'] = 1;
                $order_part_dtl[$key]['part_status'] = 0;

                /* calculate line total */
                $order_part_dtl[$key]['line_total'] = 0;
            }
        }

        $this->Common_model->insert_batch_record('gm_orderpart_details', $order_part_dtl);
        /*make empty gm_part_order_cart */
        $this->Common_model->update_info('gm_part_order_cart',array('active'=>0,'modified_date'=>date("Y-m-d h:i:s")),array('user_id'=>$user_id));
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            $op['status'] = FALSE;
            $op['message'] = "something went wrong";
        } else {
            $op['status'] = TRUE;
            $op['order_no'] = $order_no;
			$op['cart_count']= 0;
        }
        $this->response($op, REST_Controller::HTTP_OK); 
    }
    
    public function view_order_details_post() {
        $dealer_id = $this->input->post('dealer_id');
        if(empty($dealer_id)){
            $op['status'] = FALSE;
            $op['message'] = "something went wrong"; 
            $this->response($op, REST_Controller::HTTP_OK); 
            return TRUE;
        }
        
    }
	public function add_to_cart_post() {
        $bom_plate_part_id = $this->post('bom_plate_part_id');
        $part_number = $this->post('part_number');
        $quantity = $this->post('quantity');
        $op =  array();
        $user_id = $this->post('user_id');        
        /*store order again user in one cart gm_part_order_cart */
        $update_count =  $add_count =0;
        $this->db->trans_start();
        $order_cart = $this->Common_model->select_info('gm_part_order_cart',array('part_number'=>$part_number,'user_id'=>$user_id,'active'=>1));
        if($order_cart){ /* is already there order then update it */
            $update['bom_plate_part_id']= $bom_plate_part_id;
            $update['quantity']= $quantity;
            $update['modified_date']= date("Y-m-d h:i:s");
            if($quantity == 0){
                $update['active'] = 0;
            }
            $upcond['id']= $order_cart[0]['id'];
            
            $id =$this->Common_model->update_info('gm_part_order_cart',$update,$upcond);
            if($id){
                $update_count ++;
            }
            
        } else { /* add new record in cart */
            $add_rec['bom_plate_part_id']= $bom_plate_part_id;
            $add_rec['part_number']= $part_number;
            $add_rec['quantity']= $quantity;
            $add_rec['active']= 1;
            $add_rec['user_id']= $user_id;
            $add_rec['created_date']= date("Y-m-d h:i:s");
            $add_rec['modified_date']= date("Y-m-d h:i:s");
            
            $ud = $this->Common_model->insert_info('gm_part_order_cart',$add_rec);            
            if($ud){
                $add_count++;
            }
        }
        
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
        $op['status']= FALSE;
        $op['message']= "something went wrong";
        } else {
		$order_count = $this->Common_model->select_info('gm_part_order_cart',array('user_id'=>$user_id,'active'=>1));
        $op['status']= TRUE;
		$op['cart_count']= ($order_count == FALSE) ? 0 : count($order_count);
        $op['message'] =  !empty($add_count) ? " part added " : " part updated ";
        
        }
        
        $this->response($op, REST_Controller::HTTP_OK); 
    }
	public function view_cart_post() {
        $user_id = $this->post('user_id');
        $op =$ordtl = array();
        $op['user_id']= $user_id;
        $order = $this->Common_model->select_info('gm_part_order_cart',array('user_id'=>$user_id,'active'=>1));
        if($order){
            $op['status'] =  TRUE;
            foreach ($order as $key => $value) {
                $ordtl[$key]['bom_plate_part_id']= $value['bom_plate_part_id'];
                $ordtl[$key]['part_number']= $value['part_number'];
                $ordtl[$key]['quantity']= $value['quantity'];
                $ordtl[$key]['line_total']= !empty($value['line_total']) ? $value['line_total'] : 0;
            }
			$order_count = $this->Common_model->select_info('gm_part_order_cart',array('user_id'=>$user_id,'active'=>1));
            $op['cart_count']= count($order_count);
            $op['order_details'] = $ordtl;
        } else{
            $op['status'] =  FALSE;
            $op['message'] =  "No Items availabl in cart";
        }
        $this->response($op, REST_Controller::HTTP_OK); 
    }
	public function my_roq_post() {
        
        $user_id = $this->post('user_id');
        $distributor_id  = $this->post('distributor_id');
        $vertical_id = $this->post('vertical_id');
        $role = $this->post('role'); /*If role is User will write conditions for users */
	$filter = $this->post('filter_month'); /* 0= Clear, 1 =  last one month, 3= last 3 month,6 =>last Six month */
        $search_text = $this->post('search_text');
        $new_date = '';
        if(!empty($filter)){
            $date = date_create(date('Y-m-d')); 
            date_sub($date, date_interval_create_from_date_string($filter.' months'));
            $new_date = date_format($date, 'Y-m-01');
        }
        
        $op = $data=$data_raw=  array();
        if($role == "user"){
        $this->db->select('*,sfa_mc_dist.name AS sfa_mc_distributor_name,order.status AS order_status,'
                . 'sfa_mc_dist.phone_number AS sfa_mc_distributor_phone_number,sfa_mc_dist.email_bajaj AS sfa_mc_dist_bajaj_email ');
        $this->db->from('gm_orderpart AS order');
        $this->db->join('gm_orderpart_details AS od','order.id=od.order_id','left');
        $this->db->join('gm_sfa_mc_distributor AS sfa_mc_dist','order.distributor_id=sfa_mc_dist.id','left');
        $this->db->join('gm_epc_shop_details AS sfa_mc_dist_shop','order.shop_address_id=sfa_mc_dist_shop.id','left');
        $this->db->where('order.order_placed_user_id',$user_id);
	if(!empty($new_date)) {$this->db->where('order_date BETWEEN "'. date('Y-m-d',strtotime($new_date)). '" and "'. date('Y-m-d').'"'); }
        if(!empty($search_text)){
            $this->db->where('( sfa_mc_dist.phone_number like "%'.$search_text.'%" OR '
                    . 'sfa_mc_dist.email_bajaj like "%'.$search_text.'%" OR '
                    . 'part_number like "%'.$search_text.'%" )');
        }
        $query = $this->db->get();
        $order_details = ($query->num_rows() > 0)? $query->result_array():FALSE;
            if($order_details){
                foreach ($order_details as $key => $value) {                    
                    $data_raw[$value['order_number']]['order_number'] =  $value['order_number'];
                    $data_raw[$value['order_number']]['order_date'] =  $value['order_date'];
                    $data_raw[$value['order_number']]['order_status'] =  !empty($value['order_status']) ? $value['order_status'] : "Pending";
                    $data_raw[$value['order_number']]['order_to']['name'] =  $value['sfa_mc_distributor_name'];
                    $data_raw[$value['order_number']]['order_to']['phone_number'] =  $value['sfa_mc_distributor_phone_number'];
                    $data_raw[$value['order_number']]['order_to']['email'] =  $value['sfa_mc_dist_bajaj_email'];
                    $data_raw[$value['order_number']]['order_to']['shop_latitude'] =  $value['latitude'];
                    $data_raw[$value['order_number']]['order_to']['shop_longitude'] =  $value['longitude'];
                    $data_raw[$value['order_number']]['order_dtl'][$key]['part_number']=  $value['part_number'];
                    $data_raw[$value['order_number']]['order_dtl'][$key]['part_quantity']=  $value['quantity'];
                    $data_raw[$value['order_number']]['order_dtl'][$key]['part_qline_total']=  $value['line_total'];
                    $data_raw[$value['order_number']]['order_dtl'][$key]['part_quoted_price']=  $value['quoted_price'];
                    $data_raw[$value['order_number']]['order_dtl'][$key]['part_status']=  !empty($value['part_status']) ? "Available": "Not Available";
                }
                
                /* refine array again */
                    $i=0;
                    foreach ($data_raw as $key => $value) {                        
                        $data[$i]['order_number'] = $value['order_number'];
                        $data[$i]['order_date'] = $value['order_date'];
                        $data[$i]['order_status'] = $value['order_status'];
                        $data[$i]['order_to']['name'] = $value['order_to']['name'];
                        $data[$i]['order_to']['phone_number'] =  $value['order_to']['phone_number'];
                        $data[$i]['order_to']['email'] =  $value['order_to']['email'];
                        $data[$i]['order_to']['shop_latitude'] = $value['order_to']['shop_latitude'];
                        $data[$i]['order_to']['shop_longitude'] = $value['order_to']['shop_longitude'] ;
                        $j = 0;
						$price = 0;
                        foreach ($value['order_dtl'] AS $key_1 => $value_1){
                            $data[$i]['order_dtl'][$j]['part_number'] = $value_1['part_number'];
                            $data[$i]['order_dtl'][$j]['part_quantity']=  $value_1['part_quantity'];
                            $data[$i]['order_dtl'][$j]['part_qline_total']=  $value_1['part_qline_total'];
                            $data[$i]['order_dtl'][$j]['part_quoted_price']=  $value_1['part_quoted_price'];
                            $data[$i]['order_dtl'][$j]['part_status']=  !empty($value_1['part_status']) ? "Available": "Not Available";
							$price += !empty($value_1['part_line_total']) ? $value_1['part_line_total'] :0;
                            $j++;
                        }
						$data[$i]['quotation'] = $price;
                        $i++;
                    }
                $op['status'] =  TRUE;
                $op['data'] =  $data;
            } else {
                $op['status'] =  FALSE;
                $op['message'] =  "Sorry No ROQ available";
            }
            
        } elseif(!empty($vertical_id)) {/* 1=>  MC*/  
            if($vertical_id === 1 && !empty($distributor_id)){ 
                $this->db->select('*,sfa_mc_dist.name AS sfa_mc_distributor_name,order.status AS order_status,'
                . 'sfa_mc_dist.phone_number AS sfa_mc_distributor_phone_number,sfa_mc_dist.email_bajaj AS sfa_mc_dist_bajaj_email,'
                        . 'au.first_name,au.last_name,au.email AS user_email,up.phone_number AS user_phone_number,od.id AS orderpart_details_id');
                $this->db->from('gm_orderpart AS order');
                $this->db->join('gm_orderpart_details AS od','order.id=od.order_id','left');
                $this->db->join('gm_sfa_mc_distributor AS sfa_mc_dist','order.distributor_id=sfa_mc_dist.id','left');
                $this->db->join('gm_epc_shop_details AS sfa_mc_dist_shop','order.shop_address_id=sfa_mc_dist_shop.id','left');
                $this->db->join('auth_user AS au','au.id=order.order_placed_user_id','left');
                $this->db->join('gm_userprofile AS up','au.id=up.user_id','left');
                $this->db->where('order.distributor_id',$distributor_id);  
                $query = $this->db->get();
                $order_details = ($query->num_rows() > 0)? $query->result_array():FALSE;
            if($order_details){
                foreach ($order_details as $key => $value) {
                    $data_raw[$value['order_number']]['order_number'] =  $value['order_number'];
                    $data_raw[$value['order_number']]['order_date'] =  $value['order_date'];
                    $data_raw[$value['order_number']]['order_status'] =  !empty($value['order_status']) ? $value['order_status'] : "Pending";
                    $data_raw[$value['order_number']]['order_from']['name'] =  $value['first_name'] ." ". $value['last_name'];
                    $data_raw[$value['order_number']]['order_from']['phone_number'] =  $value['user_phone_number'];
                    $data_raw[$value['order_number']]['order_from']['email'] =  $value['user_email'];                    
                    $data_raw[$value['order_number']]['order_dtl'][$key]['orderpart_details_id']=  $value['orderpart_details_id'];
                    $data_raw[$value['order_number']]['order_dtl'][$key]['part_number']=  $value['part_number'];
                    $data_raw[$value['order_number']]['order_dtl'][$key]['part_quantity']=  $value['quantity'];
                    $data_raw[$value['order_number']]['order_dtl'][$key]['part_qline_total']=  $value['line_total'];
                    $data_raw[$value['order_number']]['order_dtl'][$key]['part_quoted_price']=  $value['quoted_price'];
                    $data_raw[$value['order_number']]['order_dtl'][$key]['part_status']=  !empty($value['part_status']) ? "Available": "Not Available";
                }
                    /* refine array again */
                    $i=0;
                    foreach ($data_raw as $key => $value) {                        
                        $data[$i]['order_number'] = $value['order_number'];
                        $data[$i]['order_date'] = $value['order_date'];
                        $data[$i]['order_status'] = $value['order_status'];
                        $data[$i]['order_from']['name'] = $value['order_from']['name'];
                        $data[$i]['order_from']['phone_number'] = $value['order_from']['phone_number'];
                        $data[$i]['order_from']['email'] = $value['order_from']['email'];
                        $j=0;
                        foreach ($value['order_dtl'] as $key_1 => $value_1) {
                            $data[$i]['order_dtl'][$j]['orderpart_details_id'] = $value_1['orderpart_details_id'];
                            $data[$i]['order_dtl'][$j]['part_number'] = $value_1['part_number'];
                            $data[$i]['order_dtl'][$j]['part_quantity']=  $value_1['part_quantity'];
                            $data[$i]['order_dtl'][$j]['part_qline_total']=  $value_1['part_qline_total'];
                            $data[$i]['order_dtl'][$j]['part_quoted_price']=  $value_1['part_quoted_price'];
                            $data[$i]['order_dtl'][$j]['part_status']=  !empty($value_1['part_status']) ? "Available": "Not Available";
                            $j++;
                        }
                        $i++;
                    }
                
                $op['status'] =  TRUE;
                $op['data'] =  $data;
            } else {
                $op['status'] =  FALSE;
                $op['message'] =  "Sorry No ROQ available";
            }
            }
                      
        }
        $this->response($op, REST_Controller::HTTP_OK); 
    }
	
	 public function update_order_post() {
        $order_number = $this->post('order_number');
        $api_type = $this->post('api_type');/*order_status / order_dtl*/
        /* start transaction*/
        $this->db->trans_start();   
        if($api_type == "order_status"){ /* change status of order Aprove / Reject*/
            $order_status = $this->post('order_status');
            $comments = $this->post('comments');
            if(!empty($order_status)){
                $update_dtl['status']= $order_status;
                $update_dtl['comments']= !empty($comments) ? $comments : "";
                $updt_cond['order_number'] = $order_number;
                
                
                $this->Common_model->update_info('gm_orderpart',$update_dtl,$updt_cond);
            }else{/* send error to send order status*/
                
            }
        } elseif ($api_type == "order_dtl" ) {
            
            $order_dtl = $this->post('order_dtl');
            $update_order =  array();
            if ($order_dtl) {
            foreach ($order_dtl AS $key => $value) {
                $update_order_dtl[$key]['id']= $value['orderpart_details_id'];
                $update_order_dtl[$key]['part_status']= $value['part_status'];
                $update_order_dtl[$key]['quoted_price']= $value['quoted_price'];
            }
            $this->db->update_batch('gm_orderpart_details',$update_order_dtl,'id');
    }
        
    }
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
}

?>

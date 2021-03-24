<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Bikemodels extends REST_Controller {

    //put your code here
    function __construct() {
        // Construct the parent class
        parent::__construct();
        $this->load->model("Common_model");
    }

    function dashboard_models_post() {
        $vertical_id = $this->post('vertical_id');
	$sku_code = $this->post('sku_code');
        $role_id = $this->post('role_id');
        if(empty($vertical_id) && !empty($role_id) ){
            $vertical_id = 1;
        }
        $img_base = "http://gladminds-connect.s3.amazonaws.com/";
        $this->db->select('sku.sku_code,sku.sku_description,pb.id as brand_id,
                            pb.brand_name,
                            pb.brand_image,
                            plate.plate_image_with_part,
                            plate.plate_id AS plate_code,
                            plate.id As p_id,plate.plate_txt');
        $this->db->from('gm_productbrands AS pb');
        $this->db->join('gm_skudetails AS sku', 'sku.brand_id = pb.id', 'left');
        $this->db->join('gm_bomheader AS bh', 'bh.sku_code = sku.sku_code', 'left');
        $this->db->join('gm_bomplatepart AS bpp', 'bpp.bom_id = bh.id', 'left');
        $this->db->join('gm_bomplate AS plate', 'bpp.plate_id = plate.id', 'left');
        $this->db->join('gm_epc_plateimages AS plap', 'plap.plate_id = plate.id', 'left');
        $this->db->where('pb.brand_vertical_id', $vertical_id);
        $this->db->where('plap.status', 'Approved');
	if(!empty($sku_code)) {   $this->db->where('sku.sku_code',$sku_code); }
        $this->db->group_by('bh.sku_code,plate.plate_id');

        $query = $this->db->get();
        $all_models_dtl = ($query->num_rows() > 0) ? $query->result_array() : FALSE;
        $all_prt = $all_prt_raw = array();
        /* add baner image */

        if ($all_models_dtl) {
            $all_prt['status'] = TRUE;

            foreach ($all_models_dtl as $key => $value) {
                $all_prt_raw[$value['sku_code']]['sku_code'] = $value['sku_code'];
                $all_prt_raw[$value['sku_code']]['sku_description'] = $value['sku_description'];
                $all_prt_raw[$value['sku_code']]['brand_id'] = $value['brand_id'];
                $all_prt_raw[$value['sku_code']]['brand_name'] = $value['brand_name'];
                $all_prt_raw[$value['sku_code']]['brand_image'] = !empty($value['brand_image']) ? $img_base . $value['brand_image'] : "";
                $all_prt_raw[$value['sku_code']]['plate'][$key]['plate_code'] = $value['plate_code'];
                $all_prt_raw[$value['sku_code']]['plate'][$key]['plate_txt'] = $value['plate_txt'];
                $all_prt_raw[$value['sku_code']]['plate'][$key]['p_id'] = $value['p_id'];
                $all_prt_raw[$value['sku_code']]['plate'][$key]['plate_image_with_part'] = !empty($value['plate_image_with_part']) ? $img_base . $value['plate_image_with_part'] : "";
            }
            $i = $j = 0;
            /* top 10 models and parts */
            foreach ($all_prt_raw as $key => $value) {

                $all_prt['models_top10'][$i]['sku_code'] = $value['sku_code'];
                $all_prt['models_top10'][$i]['sku_description'] = $value['sku_description'];
                $all_prt['models_top10'][$i]['brand_id'] = $value['brand_id'];
                $all_prt['models_top10'][$i]['brand_name'] = $value['brand_name'];
                $all_prt['models_top10'][$i]['brand_image'] = $value['brand_image'];
                $j = 0;
                foreach ($value['plate'] as $key_brand => $value_brand) {
                    $all_prt['models_top10'][$i]['plate'][$j]['plate_code'] = $value_brand['plate_code'];
                    $all_prt['models_top10'][$i]['plate'][$j]['plate_txt'] = $value_brand['plate_txt'];
                    $all_prt['models_top10'][$i]['plate'][$j]['p_id'] = $value_brand['p_id'];
                    $all_prt['models_top10'][$i]['plate'][$j]['plate_image_with_part'] = $value_brand['plate_image_with_part'];
                    $j++;
                    if ($j > 9)
                        break;
                }
                $i++;
                if ($i > 9)
                    break;
            }

            /* all parts and models */

            $i = $j = 0;
            foreach ($all_prt_raw as $key => $value) {

                $all_prt['models_all'][$i]['sku_code'] = $value['sku_code'];
                $all_prt['models_all'][$i]['sku_description'] = $value['sku_description'];
                $all_prt['models_all'][$i]['brand_id'] = $value['brand_id'];
                $all_prt['models_all'][$i]['brand_name'] = $value['brand_name'];
                $all_prt['models_all'][$i]['brand_image'] = $value['brand_image'];
                $j = 0;
                foreach ($value['plate'] as $key_brand => $value_brand) {
                    $all_prt['models_all'][$i]['plate'][$j]['plate_code'] = $value_brand['plate_code'];
                    $all_prt['models_all'][$i]['plate'][$j]['plate_txt'] = $value_brand['plate_txt'];
                    $all_prt['models_all'][$i]['plate'][$j]['p_id'] = $value_brand['p_id'];
                    $all_prt['models_all'][$i]['plate'][$j]['plate_image_with_part'] = $value_brand['plate_image_with_part'];
                    $j++;
                }
                $i++;
            }
        } else {
            $all_prt['status'] = FALSE;
            $all_prt['message'] = "Sorry, No data available";
        }
        $all_prt['banner_data']['image_url'] = "https://image.shutterstock.com/image-vector/special-offer-banner-vector-format-260nw-586903514.jpg";
        $all_prt['banner_data']['url_visit'] = "";
        $this->response($all_prt, REST_Controller::HTTP_OK);
    }

    public function plate_model_applicable_post() {
		log_message('info',print_r($this->post(), TRUE));
        $p_id = $this->post('p_id');
        $vertical_id = $this->post('vertical_id');
        $plate_code = $this->post('plate_code');
        $plate_txt = $this->post('plate_txt');
		$vertical_id = ($this->post('role') =="user") ? 1 : $this->post('vertical_id');
        $img_base = "http://gladminds-connect.s3.amazonaws.com/";

        $this->db->select('sku.sku_code,sku.sku_description,pb.id as brand_id,
                            pb.brand_name,
                            pb.brand_image,
                            plate.plate_image_with_part,
                            plate.plate_id AS plate_code,
                            plate.id As p_id,plate.plate_txt');
        $this->db->from('gm_productbrands AS pb');
        $this->db->join('gm_skudetails AS sku', 'sku.brand_id = pb.id', 'left');
        $this->db->join('gm_bomheader AS bh', 'bh.sku_code = sku.sku_code', 'left');
        $this->db->join('gm_bomplatepart AS bpp', 'bpp.bom_id = bh.id', 'left');
        $this->db->join('gm_bomplate AS plate', 'bpp.plate_id = plate.id', 'left');
        $this->db->join('gm_epc_plateimages AS plap', 'plap.plate_id = plate.id', 'left');
        $this->db->where('pb.brand_vertical_id', $vertical_id);
        $this->db->where('plap.status', 'Approved');
        $this->db->where('plate.plate_id', $plate_code);
        $this->db->where('plate.plate_txt', $plate_txt);
        $this->db->group_by('bh.sku_code,plate.plate_id');
        $query = $this->db->get();
        $all_models_dtl = ($query->num_rows() > 0) ? $query->result_array() : FALSE;
        
        if ($all_models_dtl) {
            $all_prt['status'] = TRUE;

            foreach ($all_models_dtl as $key => $value) {
                $all_prt_raw[$value['sku_code']]['sku_code'] = $value['sku_code'];
                $all_prt_raw[$value['sku_code']]['sku_description'] = $value['sku_description'];
                $all_prt_raw[$value['sku_code']]['brand_id'] = $value['brand_id'];
                $all_prt_raw[$value['sku_code']]['brand_name'] = $value['brand_name'];
                $all_prt_raw[$value['sku_code']]['brand_image'] = !empty($value['brand_image']) ? $img_base . $value['brand_image'] : "";
                $all_prt_raw[$value['sku_code']]['plate']['plate_code'] = $value['plate_code'];
                $all_prt_raw[$value['sku_code']]['plate']['plate_txt'] = $value['plate_txt'];
                $all_prt_raw[$value['sku_code']]['plate']['p_id'] = $value['p_id'];
                $all_prt_raw[$value['sku_code']]['plate']['plate_image_with_part'] = !empty($value['plate_image_with_part']) ? $img_base . $value['plate_image_with_part'] : "";
            }
            $i = $j = 0;

            /* all models */
            $i = $j = 0;
            foreach ($all_prt_raw as $key => $value) {
                $all_prt['models_all'][$i]['sku_code'] = $value['sku_code'];
                $all_prt['models_all'][$i]['sku_description'] = $value['sku_description'];
                $all_prt['models_all'][$i]['brand_id'] = $value['brand_id'];
                $all_prt['models_all'][$i]['brand_name'] = $value['brand_name'];
                $all_prt['models_all'][$i]['brand_image'] = $value['brand_image'];
                
                
                    $all_prt['models_all'][$i]['plate_code'] = $value['plate']['plate_code'];
                    $all_prt['models_all'][$i]['plate_txt'] = $value['plate']['plate_txt'];
                    $all_prt['models_all'][$i]['p_id'] = $value['plate']['p_id'];
//                    $all_prt['models_all'][$i]['plate_image_with_part'] = $value['plate']['plate_image_with_part'];
               
                $i++;
            }
        } else {
            $all_prt['status'] = FALSE;
            $all_prt['message'] = "Sorry, No data available";
        } 
        $this->response($all_prt, REST_Controller::HTTP_OK);
    }

    public function plate_details_post() {
        $img_base = "http://gladminds-connect.s3.amazonaws.com/";
        $p_id = $this->post('p_id');
        $user_id = $this->post('user_id');
        $vertical_id = $this->post('vertical_id');
        $cart_select = $cart_condition = "";
        if(!empty($user_id)){
            $cart_select = "oc.quantity AS cart_part_quantity,";
            $cart_condition = "left join gm_part_order_cart AS oc ON oc.part_number = bpc.part_number and oc.active =1 AND oc.user_id =".$user_id;
        }
        $sql =  "select 
            bpp.id AS bom_plate_part_id,
            bp.id AS part_id,
            bpc.part_number,
            -- bpc.description,
            bpp.material_description as description,
            bpp.quantity,
            ".$cart_select."
            bpc.href,
            bpc.coordinates,
            bpc.plate_image_id, plate.plate_image_with_part
        from
            gm_bomplatepart AS bpp
                left join
            gm_bomplate AS plate ON bpp.plate_id = plate.id
                left join
            gm_bompart AS bp ON bpp.part_id = bp.id
                left join
            gm_partcoordinates AS bpc ON bp.part_number = bpc.part_number
                inner join
            (select 
                max(id) as id
            from
                gm_partcoordinates
            group by part_number) AS a ON a.id = bpc.id
            ".$cart_condition."
        where
            bpp.plate_id = ".$p_id." AND bpc.id
        group by bpc.part_number order by bpc.href ASC ";
        $parts_dtl = $this->db->query($sql)->result_array();
        $all_parts =  array();
        if($parts_dtl){
            $all_parts['status'] =  TRUE;
            $all_parts['plate_image'] =  !empty($parts_dtl[0]['plate_image_with_part']) ? $img_base.$parts_dtl[0]['plate_image_with_part'] : "";
            foreach ($parts_dtl as $key => $value) {
                $all_parts['parts'][$key]['bom_plate_part_id'] =  $value['bom_plate_part_id'];
                $all_parts['parts'][$key]['part_id'] =  $value['part_id'];
                $all_parts['parts'][$key]['href'] =  $value['href'];
                $all_parts['parts'][$key]['coordinates'] =  $value['coordinates'];
                $all_parts['parts'][$key]['part_number'] =  $value['part_number'];
                $all_parts['parts'][$key]['description'] =  $value['description'];
                $all_parts['parts'][$key]['quantity'] =  trim($value['quantity']);
                $all_parts['parts'][$key]['plate_image_id'] =  $value['plate_image_id'];  
                if(!empty($user_id)){
                    $all_parts['parts'][$key]['cart_part_quantity'] =  !empty(trim($value['cart_part_quantity'])) ?  trim($value['cart_part_quantity']) : 0;    
					$all_parts['parts'][$key]['part_price'] =   2;   
                }
                
            }
            
        } else {
            $all_parts['status'] =  false;
            $all_parts['message'] =  "No Details Available";
        }
        $this->response($all_parts, REST_Controller::HTTP_OK);
        
    }
	
	public function search_vin_details_post() {
        $vin_no = $this->post('vin_no');
        if(empty($vin_no)){
            $op['status'] = FALSE;
            $op['message'] = "Please send Vin No";
            $this->response($op, REST_Controller::HTTP_OK);
            return TRUE;
        }
        $img_base = "http://gladminds-connect.s3.amazonaws.com/";
        $sql = "select 
                mfd.product_id,
                mfd.vehicle_off_line_date,
                header.sku_code,
                sku.sku_description,
                pb.id as brand_id,
                pb.brand_name,
                pb.brand_image,
                plate.plate_image_with_part,
                plate.id As p_id,
                plate.plate_id AS plate_code,
                plate.plate_txt
            from
                gm_manufacturingdata as mfd
                    left join
                gm_bomheader as header ON mfd.bomheader_id = header.id
                    left join
                gm_skudetails AS sku ON header.sku_code = sku.sku_code
                    left join
                gm_productbrands AS pb ON sku.brand_id = pb.id
                    left join
                gm_bomplate AS plate ON plate.bom_id = mfd.bomheader_id
                    left join
                gm_epc_plateimages AS plap ON plap.plate_id = plate.id
            where
                mfd.product_id =  '".$vin_no."' 
        AND plap.status = 'Approved'";
        $all_models_dtl = $this->db->query($sql)->result_array();
        $all_parts =  array();
        
        if ($all_models_dtl) {
            $all_prt['status'] = TRUE;

            foreach ($all_models_dtl as $key => $value) {
                $all_prt_raw[$value['sku_code']]['sku_code'] = $value['sku_code'];
                $all_prt_raw[$value['sku_code']]['vehicle_off_line_date'] = $value['vehicle_off_line_date'];
                $all_prt_raw[$value['sku_code']]['sku_description'] = $value['sku_description'];
                $all_prt_raw[$value['sku_code']]['brand_id'] = $value['brand_id'];
                $all_prt_raw[$value['sku_code']]['brand_name'] = $value['brand_name'];
                $all_prt_raw[$value['sku_code']]['brand_image'] = !empty($value['brand_image']) ? $img_base . $value['brand_image'] : "";
                $all_prt_raw[$value['sku_code']]['plate'][$key]['plate_code'] = $value['plate_code'];
                $all_prt_raw[$value['sku_code']]['plate'][$key]['plate_txt'] = $value['plate_txt'];
                $all_prt_raw[$value['sku_code']]['plate'][$key]['p_id'] = $value['p_id'];
                $all_prt_raw[$value['sku_code']]['plate'][$key]['plate_image_with_part'] = !empty($value['plate_image_with_part']) ? $img_base . $value['plate_image_with_part'] : "";
            }
            
            /* all parts and models */

            $i = $j = 0;
            foreach ($all_prt_raw as $key => $value) {

                $all_prt['model']['sku_code'] = $value['sku_code'];
                $all_prt['model']['vin_no'] = $vin_no;
                $all_prt['model']['manufacturing_date'] = $value['vehicle_off_line_date'];
                $all_prt['model']['sku_description'] = $value['sku_description'];
                $all_prt['model']['brand_id'] = $value['brand_id'];
                $all_prt['model']['brand_name'] = $value['brand_name'];
                $all_prt['model']['brand_image'] = $value['brand_image'];
                $j = 0;
                foreach ($value['plate'] as $key_brand => $value_brand) {
                    $all_prt['model']['plates'][$j]['plate_code'] = $value_brand['plate_code'];
                    $all_prt['model']['plates'][$j]['plate_txt'] = $value_brand['plate_txt'];
                    $all_prt['model']['plates'][$j]['p_id'] = $value_brand['p_id'];
                    $all_prt['model']['plates'][$j]['plate_image_with_part'] = $value_brand['plate_image_with_part'];
                    $j++;
                }
                $i++;
            }
        } else {
            $all_prt['status'] = FALSE;
            $all_prt['message'] = "Sorry, No data available for Vin No ".$vin_no;
        }
        $this->response($all_prt, REST_Controller::HTTP_OK);
    }

	public function search_global_details_post() {
		log_message('info', print_r($this->post(), TRUE), false);
        
        $filter_type = $this->post('filter_type');
        $vin_no = $this->post('vin_no');
        $plate_name = $this->post('plate_name');
        $part_number = $this->post('part_number');
        $sql = "";
        if(empty($filter_type)){
            $op['status'] = FALSE;
            $op['message'] = "Please select atleast one filter";
            $this->response($op, REST_Controller::HTTP_OK);
            return TRUE;
        }
        if($filter_type == "vin"){
            if(empty($vin_no)){
            $op['status'] = FALSE;
            $op['message'] = "Please provide vin number";
            $this->response($op, REST_Controller::HTTP_OK);
            return TRUE;
        }
            $sql = $this->vin_filter();
        } elseif($filter_type == "plate"){
            if(empty($vin_no) || empty($plate_name)){
                $op['status'] = FALSE;
                $op['message'] = "Please provide Vin & plate name";
                $this->response($op, REST_Controller::HTTP_OK);
                return TRUE;
            }
            $sql = $this->plate_filter();
        } elseif($filter_type == "part"){
            if(empty($part_number)){
                $op['status'] = FALSE;
                $op['message'] = "Please provide part number";
                $this->response($op, REST_Controller::HTTP_OK);
                return TRUE;
            }
            $sql = $this->part_filter();
        }  else {
            $op['status'] = FALSE;
            $op['message'] = "Filter type is wrong";
            $this->response($op, REST_Controller::HTTP_OK);
            return TRUE;
        }

        $img_base = "http://gladminds-connect.s3.amazonaws.com/";
        
        $all_models_dtl = $this->db->query($sql)->result_array();
        $all_parts =  array();
        
        if ($all_models_dtl) {
            $all_prt['status'] = TRUE;

            foreach ($all_models_dtl as $key => $value) {
                $all_prt_raw[$value['sku_code']]['sku_code'] = trim($value['sku_code']);
                
                if(!empty($vin_no))  $all_prt_raw[$value['sku_code']]['vehicle_off_line_date'] = $value['vehicle_off_line_date'];
                
                $all_prt_raw[$value['sku_code']]['sku_description'] = trim($value['sku_description']);
                $all_prt_raw[$value['sku_code']]['brand_id'] = trim($value['brand_id']);
                $all_prt_raw[$value['sku_code']]['brand_name'] = trim($value['brand_name']);
                $all_prt_raw[$value['sku_code']]['brand_image'] = !empty($value['brand_image']) ? $img_base . $value['brand_image'] : "";
                $all_prt_raw[$value['sku_code']]['plate'][$value['p_id']]['plate_code'] = trim($value['plate_code']);
                $all_prt_raw[$value['sku_code']]['plate'][$value['p_id']]['plate_txt'] = trim($value['plate_txt']);
                $all_prt_raw[$value['sku_code']]['plate'][$value['p_id']]['p_id'] = trim($value['p_id']);
                $all_prt_raw[$value['sku_code']]['plate'][$value['p_id']]['plate_image_with_part'] = !empty($value['plate_image_with_part']) ? $img_base . $value['plate_image_with_part'] : "";
                
                $all_prt_raw[$value['sku_code']]['plate'][$value['p_id']]['parts'][$key]['bom_plate_part_id'] = trim($value['bom_plate_part_id']);
                $all_prt_raw[$value['sku_code']]['plate'][$value['p_id']]['parts'][$key]['part_number'] = trim($value['part_number']);
                $all_prt_raw[$value['sku_code']]['plate'][$value['p_id']]['parts'][$key]['part_description'] = trim($value['description']);
                $all_prt_raw[$value['sku_code']]['plate'][$value['p_id']]['parts'][$key]['part_quantity'] = trim($value['quantity']);
            }
            
            /* all parts and models */

            $i = $j = $k =0;
            foreach ($all_prt_raw as $key => $value) {

                $all_prt['model'][$i]['sku_code'] = $value['sku_code'];
                if(!empty($vin_no)){
                    $all_prt['model'][$i]['vin_no'] = $vin_no;
                    $all_prt['model'][$i]['manufacturing_date'] = $value['vehicle_off_line_date'];
                }                
                $all_prt['model'][$i]['sku_description'] = $value['sku_description'];
                $all_prt['model'][$i]['brand_id'] = $value['brand_id'];
                $all_prt['model'][$i]['brand_name'] = $value['brand_name'];
                $all_prt['model'][$i]['brand_image'] = $value['brand_image'];
                $j = 0;
                foreach ($value['plate'] as $key_brand => $value_brand) {
                    $all_prt['model'][$i]['plates'][$j]['plate_code'] = $value_brand['plate_code'];
                    $all_prt['model'][$i]['plates'][$j]['plate_txt'] = $value_brand['plate_txt'];
                    $all_prt['model'][$i]['plates'][$j]['p_id'] = $value_brand['p_id'];
                    $all_prt['model'][$i]['plates'][$j]['plate_image_with_part'] = $value_brand['plate_image_with_part'];
                    $k=0;
                    foreach ($value_brand['parts'] as $key_parts => $value_parts) {
                        $all_prt['model'][$i]['plates'][$j]['parts'][$k]['bom_plate_part_id'] = $value_parts['bom_plate_part_id'];
                        $all_prt['model'][$i]['plates'][$j]['parts'][$k]['part_number'] = $value_parts['part_number'];
                        $all_prt['model'][$i]['plates'][$j]['parts'][$k]['part_description'] = $value_parts['part_description'];
                        $all_prt['model'][$i]['plates'][$j]['parts'][$k]['part_quantity'] = $value_parts['part_quantity'];
                        $k++;
                    }
                    $j++;
                }
                $i++;
            }
        } else {
            $all_prt['status'] = FALSE;
            $all_prt['message'] = "Sorry, No data available";
        }
        $this->response($all_prt, REST_Controller::HTTP_OK);
    
    }
    
    private function vin_filter(){
        $vin_no = $this->post('vin_no');
        $sql = "select 
            mfd.product_id,
            mfd.vehicle_off_line_date,
            header.sku_code,
            sku.sku_description,
            pb.id as brand_id,
            pb.brand_name,
            pb.brand_image,
            plate.plate_image_with_part,
            plate.id As p_id,
            plate.plate_id AS plate_code,
            plate.plate_txt,
            bpp.id AS bom_plate_part_id,
            bpc.part_number,
            -- bpc.description,
            bpp.material_description as description,
            bpp.quantity,
            bpc.href,
            bpc.coordinates
        from
            gm_manufacturingdata as mfd
                left join
            gm_bomheader as header ON mfd.bomheader_id = header.id
                left join
            gm_skudetails AS sku ON header.sku_code = sku.sku_code
                left join
            gm_productbrands AS pb ON sku.brand_id = pb.id
                left join
            gm_bomplatepart AS bpp ON mfd.bomheader_id = bpp.bom_id
                left join
            gm_epc_plateimages AS plap ON plap.plate_id = bpp.plate_id

                left join
            gm_bompart AS bp ON bpp.part_id = bp.id
                left join
            gm_partcoordinates AS bpc ON bp.part_number = bpc.part_number
                inner join
            (select 
                max(id) as id
            from
                gm_partcoordinates
            group by part_number) AS a ON a.id = bpc.id
                left join
            gm_bomplate AS plate ON bpp.plate_id = plate.id
                AND `plap`.`status` = 'Approved'
        where
            mfd.product_id = '".$vin_no."'
        group by bpc.part_number;";
        return $sql;
    }
    private function plate_filter(){
        $vin_no = $this->post('vin_no');
        $plate_name = $this->post('plate_name');
        $sql = "select 
            mfd.product_id,
            mfd.vehicle_off_line_date,
            header.sku_code,
            sku.sku_description,
            pb.id as brand_id,
            pb.brand_name,
            pb.brand_image,
            plate.plate_image_with_part,
            plate.id As p_id,
            plate.plate_id AS plate_code,
            plate.plate_txt,
            bpp.id AS bom_plate_part_id,
            bpc.part_number,
            -- bpc.description,
            bpp.material_description as description,
            bpp.quantity,
            bpc.href,
            bpc.coordinates
        from
            gm_manufacturingdata as mfd
                left join
            gm_bomheader as header ON mfd.bomheader_id = header.id
                left join
            gm_skudetails AS sku ON header.sku_code = sku.sku_code
                left join
            gm_productbrands AS pb ON sku.brand_id = pb.id
                left join
            gm_bomplatepart AS bpp ON mfd.bomheader_id = bpp.bom_id
                left join
            gm_epc_plateimages AS plap ON plap.plate_id = bpp.plate_id

                left join
            gm_bompart AS bp ON bpp.part_id = bp.id
                left join
            gm_partcoordinates AS bpc ON bp.part_number = bpc.part_number
                inner join
            (select 
                max(id) as id
            from
                gm_partcoordinates
            group by part_number) AS a ON a.id = bpc.id
                left join
            gm_bomplate AS plate ON bpp.plate_id = plate.id
                AND `plap`.`status` = 'Approved'
        where
            mfd.product_id = '".$vin_no."' AND plate.plate_txt like '".$plate_name."%'
        group by bpc.part_number;";
        return $sql;
    }
    private function part_filter(){
        $vin_no = $this->post('vin_no');
        $part_number = $this->post('part_number');
        $append_sql = "";
        if(!empty($vin_no)) {
           
           $sql = "select 
            mfd.product_id,
            mfd.vehicle_off_line_date,
            header.sku_code,
            sku.sku_description,
            pb.id as brand_id,
            pb.brand_name,
            pb.brand_image,
            plate.plate_image_with_part,
            plate.id As p_id,
            plate.plate_id AS plate_code,
            plate.plate_txt,
            bpp.id AS bom_plate_part_id,
            bpc.part_number,
            -- bpc.description,
            bpp.material_description AS description,
            bpp.quantity,
            bpc.href,
            bpc.coordinates
        from
            gm_manufacturingdata as mfd
                left join
            gm_bomheader as header ON mfd.bomheader_id = header.id
                left join
            gm_skudetails AS sku ON header.sku_code = sku.sku_code
                left join
            gm_productbrands AS pb ON sku.brand_id = pb.id
                left join
            gm_bomplatepart AS bpp ON mfd.bomheader_id = bpp.bom_id
                left join
            gm_epc_plateimages AS plap ON plap.plate_id = bpp.plate_id

                left join
            gm_bompart AS bp ON bpp.part_id = bp.id
                left join
            gm_partcoordinates AS bpc ON bp.part_number = bpc.part_number
                inner join
            (select 
                max(id) as id
            from
                gm_partcoordinates
            group by part_number) AS a ON a.id = bpc.id
                left join
            gm_bomplate AS plate ON bpp.plate_id = plate.id
                AND `plap`.`status` = 'Approved'
        where
            mfd.product_id = '".$vin_no."' AND ( bpc.part_number = '".$part_number."' OR bpp.material_description like '%".$part_number."%' )
        group by bpc.part_number;";
        } else{
            $sql ="
            select 
                sku.sku_code,
                sku.sku_description,
                pb.id as brand_id,
                pb.brand_name,
                pb.brand_image,
                plate.plate_image_with_part,
                plate.id As p_id,
                plate.plate_id AS plate_code,
                plate.plate_txt,
                bpp.id AS bom_plate_part_id,
                bpc.part_number,
                -- bpc.description,
                bpp.material_description as description,
                bpp.quantity,
                bpc.href,
                bpc.coordinates
            from
                gm_partcoordinates AS bpc
                    inner join
                (select 
                    max(id) as id
                from
                    gm_partcoordinates
                group by part_number) AS a ON a.id = bpc.id
                    left join
                gm_bompart AS bp ON bp.part_number = bpc.part_number
                    left join
                gm_bomplatepart AS bpp ON bpp.part_id = bp.id
                    left join
                gm_epc_plateimages AS plap ON plap.plate_id = bpp.plate_id
                    left join
                gm_bomplate AS plate ON bpp.plate_id = plate.id
                    AND `plap`.`status` = 'Approved'
                    left join
                gm_bomheader AS bh ON bh.id = bpp.bom_id
                    left join
                gm_skudetails AS sku ON sku.sku_code = bh.sku_code
                    left join
                gm_productbrands AS pb ON sku.brand_id = pb.id
            where
                ( bpc.part_number = '".$part_number."' OR bpp.material_description like '%".$part_number."%' )
            group by bpc.part_number;";
        }        
        return $sql;
    }
	
	    public function detail_search_post() {
        $text = $this->post('text');
        
        $result_array =  $op=  array();
        /* Algo first deep model details, Plate , Part, Vin No */
        
        $model_sql = "select 
                sku.sku_description,
                sku.id as sku_id,
                pb.brand_name,
                pb.brand_image
            from
                gm_skudetails AS sku
                    left join
                gm_productbrands AS pb ON sku.brand_id = pb.id
            where
                (pb.brand_name like '".$text."%' OR sku.sku_description like '".$text."%' ) limit 10";
        
        $model_sql_result = $this->db->query($model_sql)->result_array();
        if($model_sql_result){ /* sku code */
            foreach ($model_sql_result as $key => $value) {
                $result_array[$key]['id'] =  $value['sku_id'];
                $result_array[$key]['search_type'] =  "sku";
                $result_array[$key]['result'] =  $value['sku_description'];
            }
        } else { /* plates */
            $plate_sql = "select 
                plate.plate_id AS plate_code,
                    plate.id AS plate_id,
                    plate.plate_txt
            from
                gm_bomplate AS plate
                    join
                gm_epc_plateimages AS plap ON plap.plate_id = plate.id
                    AND `plap`.`status` = 'Approved'
            where plate.plate_txt like '%".$text."%' group by plate.id  limit 10;";            
        
            $plate_sql_result = $this->db->query($plate_sql)->result_array();
            
            if($plate_sql_result){ /* plates */
            foreach ($plate_sql_result as $key => $value) {
                $result_array[$key]['id'] =  $value['plate_id'];
                $result_array[$key]['search_type'] =  "plate_name";
                $result_array[$key]['result'] =  $value['plate_txt'];
            } 
        } else { /*part code search */
            
            $parts_sql = "select
                bpc.part_number,bpc.description,bpc.id from
                gm_partcoordinates AS bpc
                    inner join
                (select 
                    max(id) as id
                from
                    gm_partcoordinates
                group by part_number) AS a ON a.id = bpc.id
                    left join
                gm_bompart AS bp ON bp.part_number = bpc.part_number
                    left join
                gm_bomplatepart AS bpp ON bpp.part_id = bp.id
                    left join
                gm_epc_plateimages AS plap ON plap.plate_id = bpp.plate_id AND `plap`.`status` = 'Approved' where bpc.part_number like '".$text."%' OR bpc.description like '%".$text."%' limit 100;";
            
            $parts_sql_result = $this->db->query($parts_sql)->result_array();
            
            if($parts_sql_result){ /* plates */
            foreach ($parts_sql_result as $key => $value) {
                $result_array[$key]['id'] =  $value['id'];
                $result_array[$key]['search_type'] =  "by_part";
                $result_array[$key]['result'] =   (strpos($value['part_number'], $text) === FALSE) ? $value['description'] ." - ".$value['part_number'] :$value['part_number'] ;
            }
            }
            
        }
    }      
        
        if(!empty($result_array)){
             $op['status'] = TRUE;
             $op['search'] = $result_array;
        } else {
            $op['status'] = FALSE;
        }
        
        $this->response($op, REST_Controller::HTTP_OK);
    }

	public function vehical_history_post() {
        $this->load->library('Restclient'); 
        $product_id = $this->post('product_id');
        $fsc_history =  array();
        if(!empty($product_id)){
        $json = $this->restclient->post("http://bajajdfsc.gladminds.co/api/india/Transaction/service_status", [
            'country' => 'india',
            'filter' => 'chassis',
            'search' => $product_id
        ]);
        
        if($json['status']){
            $fsc_history['fsc']['status'] = TRUE;
            $fsc_history['fsc']['product_id'] = $json['service']['service_status'][0]['chassis'];
            $fsc_history['fsc']['veh_reg_no'] =  $json['service']['service_status'][0]['veh_reg_no'];
            $fsc_history['fsc']['customer_id'] =  $json['service']['service_status'][0]['customer_id'];
            $fsc_history['fsc']['coupon'] =  $json['service']['service_status'][0]['coupon'];            
                      
        } else {
            $fsc_history['fsc']['status'] = FALSE;
            $fsc_history['fsc']['message'] = "No Free Service Coupon History Found.";
        }
        } else {
            $fsc_history['fsc']['status'] = FALSE;
            $fsc_history['fsc']['message'] = "Please send Vin No";
        }
       // $this->restclient->debug();
        $this->response($fsc_history, REST_Controller::HTTP_OK);
    }
}

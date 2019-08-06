<?php

defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require APPPATH . 'libraries/REST_Controller.php';


/**
 * Description of Sa_vin_seaarchh_dealer
 *
 * @author pavaningalkar
 */
class Sa_vin_search_dealer extends REST_Controller {

    //put your code here
    function __construct() {
        // Construct the parent class
        parent::__construct();
        $this->load->database();
    }

    public function get_manufacturing_details_post() {
        $vin_no = $this->post('vin_no');
        /* get details from manufacturing data */
        if (empty($vin_no)) {
            $op['data']['status'] = FALSE;
            $this->set_response($op, REST_Controller::HTTP_OK);
            return true;
        }

        $this->db->select('md.material_number');
        $this->db->select('CONCAT(SUBSTRING(md.material_number,1, CHAR_LENGTH(md.material_number) - 2),"ZZ") AS sku_code');
        $this->db->select('md.plant');
        $this->db->select('md.vehicle_off_line_date');
        $this->db->from('gm_manufacturingdata AS md');
        !empty($vin_no) ? $this->db->where('md.product_id', $vin_no) : "";
        $query = $this->db->get();
        $manufacturing_data = ($query->num_rows() > 0) ? $query->result_array() : FALSE;
        if ($manufacturing_data) {
            /* get data from  SKU Details Need to change function */
            $this->db->select('*');
            $this->db->from("(SELECT 
                    bh.created_date,
                    `bh`.`plant`,
                    `sk`.`sku_code`,
                    bh.valid_from AS  first_manufacturing_date,
                    sk.sku_description

                FROM
                    `gm_bomheader` AS `bh`
                        JOIN
                    `gm_skudetails_custom` AS `sk` ON `bh`.`sku_code` = `sk`.`sku_code`
                WHERE
                    `bh`.`plant` = '" . $manufacturing_data[0]['plant'] . "'
                AND bh.sku_code = '" . $manufacturing_data[0]['sku_code'] . "' order by created_date DESC limit 1 ) AS bh1");

            $query = $this->db->get();
            $sku_dtl = ($query->num_rows() > 0) ? $query->result_array() : FALSE;
            /* get data from  SKU Details */

            $op['data']['status'] = TRUE;
            $op['data']['sku_code'] = array(array('id' => $manufacturing_data[0]['sku_code'], 'text' => $sku_dtl ? $sku_dtl[0]['sku_description'] . " (" . $manufacturing_data[0]['sku_code'] . ")" : $manufacturing_data[0]['sku_code']));
            $op['data']['plant'] = $manufacturing_data[0]['plant'];
            $op['data']['vehicle_off_line_date'] = date("Y-m-d", strtotime($manufacturing_data[0]['vehicle_off_line_date']));
            $op['data']['first_manufacturing_date'] = date("Y-m-d", strtotime($sku_dtl[0]['first_manufacturing_date']));
        } else {
            $op['data']['status'] = FALSE;
        }
        $this->set_response($op, REST_Controller::HTTP_OK);
        return TRUE;
    }

    
    public function Vindetails_post() {        
        
        $serviceable = "serviceable";
        $vin_no =$this->post('vin_no'); 
        $plant =$this->post('plant');
        $sku_code =$this->post('sku_code');
        $component = '';
        $description = '';
        $month_year =$this->post('month_year'); 

        $dates = (!empty($month_year)) ? $month_year : "";  // month date, year
        $dates_to_from = explode("-",$dates); 
        
        if(!empty($vin_no)){
            /*take bom header creation date*/
           $this->db->select('*');
        $this->db->from("(SELECT 
            bh.created_date,
            `bh`.`plant`,
            `sk`.`sku_code`,
            bh.valid_from

        FROM
            `gm_bomheader` AS `bh`
                JOIN
            `gm_skudetails_custom` AS `sk` ON `bh`.`sku_code` = `sk`.`sku_code`
        WHERE
            `bh`.`plant` = '".$plant."'
                AND bh.sku_code = '".$sku_code."' order by created_date DESC limit 1 ) AS bh1 ");
                $query = $this->db->get();
                $sku_dtl =  ($query->num_rows() > 0)? $query->result_array():FALSE;
                
                if($sku_dtl){
                    $dates_to_from[0]= date_format(date_create($sku_dtl[0]['valid_from']),"F d, Y");
		/*chek for to date is empty*/
                    if($dates_to_from[1] == " "){
                        $dates_to_from[1] = date("F d, Y");
                    }
                   
                }
        }
        

        /*make database Connection  and assign result to $data_set */

        $query0 = $this->vindetails_db($serviceable ,$plant,$sku_code,$component,$description, $dates_to_from,null,null);
        $data_set =  ($query0->num_rows() > 0)? $query0->result_array():FALSE;
        $catlog_url = $this->config->item('catlog');
       if($data_set){ $op['status'] = TRUE;
        foreach ($data_set as $key => $value) {
	    $op['data'][$key]['bom_plate_part_id'] = !empty($value['bom_plate_part_id']) ? $value['bom_plate_part_id']: "";
            $op['data'][$key]['part_number'] = $value['part_number'];
            $op['data'][$key]['part_description'] = $value['material_description'];
            $op['data'][$key]['quantity'] = $value['quantity'];
            $op['data'][$key]['validity_from'] = $value['valid_from'];
            $op['data'][$key]['validity_to'] = $value['valid_to'] ;
//            $op['data'][$key]['current_service_tag'] = $value['new_tag'];
            $op['data'][$key]['status'] = $value['status'];
            //$url = !empty($value['plate_approve_id']) ? $catlog_url['url']."/plates/" . $value['plate_approve_id'] . "/sbom" : "#" ;
            $op['data'][$key]['plate'] = !empty($value['plate_txt']) ? $value['plate_txt'] : "--";
        
        }
        
        } else { $op['status'] = FALSE; $op['message'] = "Sorry No Data.";}
        $this->set_response($op, REST_Controller::HTTP_OK);
    }
    
    public function plant_sku_details_get() {
        $this->db->select('allp.plant_id, aa.sku_code, aa.sku_description');
        $this->db->from(' gm_plant_details AS allp');
        $this->db->join('
    (SELECT 
        bh.plant, sk.sku_code, sk.sku_description
    FROM
        gm_skudetails_custom AS sk
    JOIN gm_bomheader AS bh ON bh.sku_code = sk.sku_code
    GROUP BY bh.plant , sk.sku_code) AS aa','aa.plant = allp.plant_id','left');
        $query = $this->db->get();
        $sku_plant_dtl =  ($query->num_rows() > 0)? $query->result_array():FALSE;
        
        
       $data= $final_data = array();
        foreach ($sku_plant_dtl as $key_plant => $value_plant) {
            $data[$value_plant['plant_id']]['plant_code'] = $value_plant['plant_id'];
            $data[$value_plant['plant_id']]['sku_details'][$key_plant]['sku_code'] = $value_plant['sku_code'];                                        
            $data[$value_plant['plant_id']]['sku_details'][$key_plant]['sku_description'] = $value_plant['sku_description'];
        }
        $i=0;
        foreach ($data as $key => $value) {
            $j=0;
            foreach ($value['sku_details'] as $key_inner => $value_inn) {
                $final_data[$i]['plant'] = $value['plant_code'];
                if(!empty($value_inn['sku_code'])){
                $final_data[$i]['sku_dtl'][$j]['sku_code'] = $value_inn['sku_code'];
                $final_data[$i]['sku_dtl'][$j]['sku_description'] = $value_inn['sku_description'];
                } else {
                    $final_data[$i]['sku_dtl']= array();
                }
                $j++;
            }
            $i++;
        }
       $this->set_response(array('data'=>$final_data,'status'=>TRUE), REST_Controller::HTTP_OK);
    }
    
    private function vindetails_db($serviceable,$plant,$sku_code,$component,$description,$dates,$offset,$perpage ) {
        $ver = 0; //STR_TO_DATE('".$dates[0]."', '%M %d, %Y')
        if(!empty($sku_code)){
            $this->db->select('id As version');
            $this->db->from('gm_bomheader AS bh');
            $this->db->where('sku_code',$sku_code);
            (!empty($plant) and $plant != 'all') ?  $this->db->where('bh.plant',$plant) : "";
            $this->db->order_by('created_date','DESC');
            $this->db->limit(1);
            $query = $this->db->get();
            $data_set =  ($query->num_rows() > 0)? $query->result_array():FALSE;
            if($data_set == FALSE){
		$op['status'] =  FALSE;
                $op['message'] =  "No BOM  With SKU ".$sku_code." AND Plant ".$plant;
		echo json_encode($op);
                die; return false;
	}
            $ver = $data_set[0]['version'];
        } else { 
		 $data['status']= FALSE;
            $data['message']= "NO DATA Found";
			echo json_encode($data);
            die; return false;
        }
        
       if(empty($plant)){
		 $data['status']= FALSE;
            $data['message']= "Please add plate";
		echo json_encode($data);
            die; return false;
        }
        if($plant == 'all'){
            $plant = '';
        }
    $this->db->select(" DISTINCT '".$dates[0]."-".$dates[1]."' AS manufacturing_date");
    $this->db->select("bh.sku_code");
    $this->db->select("skd.sku_description");
    $this->db->select("bi.item_id AS node_id");
    $this->db->select("bi.part_number");
    $this->db->select("bi.material_description");
    $this->db->select("FORMAT(FLOOR(bi.quantity),0) AS quantity");
    $this->db->select("bh.plant");
    $this->db->select("DATE_FORMAT(bi.valid_from, '%d-%m-%Y') AS valid_from");
    $this->db->select("DATE_FORMAT(bi.valid_to, '%d-%m-%Y') AS valid_to");
    $this->db->select("IFNULL(h.new_tag, '--') AS new_tag");
    $this->db->select("case  when bi.status is null  then 'INITIAL' else bi.status END AS status");
    $this->db->select("plat_approv.plate_code"); 
    $this->db->select("plat_approv.plate_txt"); 
    $this->db->select("plat_approv.plate_approve_id"); 
    $this->db->select("plat_approv.bom_plate_part_id");
    $this->db->from("gm_bomitem AS bi");
    
    !empty($sku_code) && !empty($plant) ? $this->db->join('gm_bomheader AS bh ',' bi.bom_id = bh.id AND bh.id = '.$ver,'left') : $this->db->join('gm_bomheader AS bh ',' bi.bom_id = bh.id','left');
    
    $this->db->join('gm_skudetails_custom AS skd','skd.sku_code = bh.sku_code','left');
    $this->db->join('
        (SELECT 
            *
        FROM
            (SELECT 
            *
        FROM
            gm_serviceability_mtr_history
        ORDER BY change_date DESC , change_time DESC) AS h1
        GROUP BY h1.material_number) AS h','h.material_number = bi.part_number','left');
    $this->db->join('gm_locator_desc AS locater ',' bi.serial_number = locater.locator_codes','left');
    $this->db->join(" (SELECT 
    bh.sku_code,
    bh.plant,
    bp.part_number AS material_code,
    plate.plate_id AS plate_code,
    plap.id AS plate_approve_id,
    plate.plate_txt,
    bpp.id AS bom_plate_part_id
        FROM
    gm_bomheader AS bh
        LEFT JOIN
    gm_bomplatepart AS bpp ON bpp.bom_id = bh.id 
        LEFT JOIN
    gm_bompart AS bp ON bpp.part_id = bp.id
        LEFT JOIN
    gm_bomplate AS plate ON bpp.plate_id = plate.id
        LEFT JOIN
    gm_epc_plateimages AS plap ON plap.plate_id = plate.id AND plap.`status` = 'Approved' group by material_code  ) AS plat_approv","bi.part_number=plat_approv.material_code AND bh.plant= plat_approv.plant AND bh.sku_code = plat_approv.sku_code ","left");
    
    
    
        $this->db->where('h.new_tag','S');
        !empty($dates) ?  $this->db->where("(bi.valid_from >= STR_TO_DATE('".$dates[0]."', '%M %d, %Y') AND ( bi.valid_to = '9999-12-31' OR STR_TO_DATE('".$dates[1]."', '%M %d, %Y') <= bi.valid_to)) ") : "";
        !empty($sku_code) ?  $this->db->where('bh.sku_code',$sku_code) : "";
        !empty($component) ?  $this->db->where('bi.part_number',$component) : "";
        !empty($description) ?  $this->db->like('bi.material_description',$description) : "";
        !empty($plant) ?  $this->db->where('bh.plant',$plant) : "";
        !empty($serviceable) AND ($serviceable == "serviceable") ?  $this->db->where('(h.old_tag is not null or h.new_tag is not null)') : "";
        (!empty($offset) || $offset == 0) ? $this->db->limit($perpage,$offset) : "";
        $query0 = $this->db->get();
        
        return $query0;
    }
}


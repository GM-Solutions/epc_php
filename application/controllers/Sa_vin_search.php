<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Description of Epc_reports
 *
 * @author pavaningalkar
 */
class Sa_vin_search extends CI_Controller {
    //put your code here
    public function __construct() {
        parent::__construct();
        $this->load->library("session");
        $this->load->helper('url');
        //$this->load->library('breadcrumbs'); /*for breadcrumbs*/
        $this->load->database();
        //$this->load->library('S3');/* for AWS S3 bucket Transactions */

    }
    public function Vindetails() {
        $data =  array();
        $catlog_url = $this->config->item('catlog');
        $data['siteurl']= $catlog_url['url'];
        
        $data['select_type'] = $this->input->get('select_type');
//        echo $data['select_type']; 
        /*get sku details */
         $this->db->select('sk.sku_code,sk.sku_description');
            $this->db->from('gm_productbrands AS pb');
            $this->db->join('gm_skudetails AS sk', 'sk.brand_id = pb.id', 'left');
            $this->db->join('gm_bomheader AS bh','bh.sku_code = sk.sku_code');
            $this->db->group_by('sk.sku_code');
            $this->db->where('pb.brand_vertical_id','1');
            $query = $this->db->get();
            $sku_dtl =  ($query->num_rows() > 0)? $query->result_array():FALSE;
            $data['sku_codes'] = $sku_dtl;
		
	    $this->db->select('sg.sub_brand_name,sg.id');
//            $this->db->from('gm_brand_subgroup');
            $this->db->from('gm_productbrands AS pb');
            $this->db->join('gm_skudetails AS sk', 'sk.brand_id = pb.id', 'left');
            $this->db->join('gm_brand_subgroup  sg','sg.id= sk.sub_brand_id','left');
            $this->db->group_by('sg.id');
            $this->db->where('pb.brand_vertical_id','1');
            $query = $this->db->get();
            $sku_dtl =  ($query->num_rows() > 0)? $query->result_array():FALSE;
            $data['sub_brand'] = $sku_dtl;
            
         $this->db->select('*');
            $this->db->from('gm_plant_details');
            $query = $this->db->get();
            $vin_codes =  ($query->num_rows() > 0)? $query->result_array():FALSE;
            $data['vin_codes'] = $vin_codes;
            
        $this->load->view('sa/vin_search',$data);
//        $this->load->view('sa/test',$data);
    }
    
    public function get_manufacturing_details() {
       $vin_no =  $this->input->post('vin_no');       
       /*get details from manufacturing data*/
       if(empty($vin_no)){
           $op['data']['status'] =  FALSE;
           echo json_encode($op);
           return true;
       }
//       $this->db->select('md.material_number,CONCAT(SUBSTRING(md.material_number,1, CHAR_LENGTH(md.material_number) - 2),"ZZ") AS sku_code,
//                md.plant,
//                md.vehicle_off_line_date,
//                bh.valid_from AS first_manufacturing_date');
//       $this->db->from('gm_manufacturingdata AS md');
//       $this->db->join("gm_bomheader AS bh","bh.sku_code = CONCAT(SUBSTRING(md.material_number, 1,CHAR_LENGTH(md.material_number) - 2),'ZZ') AND md.plant = bh.plant","left");
//       $this->db->join("gm_skudetails_custom as sk","bh.sku_code = sk.sku_code","left");
       
       
        $this->db->select('md.material_number');
        $this->db->select('CONCAT(SUBSTRING(md.material_number,1, CHAR_LENGTH(md.material_number) - 2),"ZZ") AS sku_code');
        $this->db->select('md.plant');
        $this->db->select('md.vehicle_off_line_date');
        $this->db->from('gm_manufacturingdata AS md');
       !empty($vin_no) ? $this->db->where('md.product_id',$vin_no) : "";       
       $query = $this->db->get();
       $manufacturing_data =  ($query->num_rows() > 0)? $query->result_array():FALSE;
       if($manufacturing_data){
           /*get data from  SKU Details Need to change function */
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
    `bh`.`plant` = '".$manufacturing_data[0]['plant']."'
        AND bh.sku_code = '".$manufacturing_data[0]['sku_code']."' order by created_date DESC limit 1 ) AS bh1");
            
            $query = $this->db->get();
            $sku_dtl =  ($query->num_rows() > 0)? $query->result_array():FALSE;
           /*get data from  SKU Details*/
           
           $op['data']['status'] =  TRUE;
           $op['data']['sku_code'] = array(array('id'=>$manufacturing_data[0]['sku_code'],'text'=>$sku_dtl ? $sku_dtl[0]['sku_description']." (".$manufacturing_data[0]['sku_code'].")":$manufacturing_data[0]['sku_code'])); 
           //$op['data']['sku_code_details'] = $sku_dtl ? $sku_dtl[0]['sku_description']:""; 
           $op['data']['plant'] = $manufacturing_data[0]['plant'];
           $op['data']['vehicle_off_line_date'] = date("m/d/Y", strtotime($manufacturing_data[0]['vehicle_off_line_date']) );
           $op['data']['first_manufacturing_date'] = date("m/d/Y", strtotime($sku_dtl[0]['first_manufacturing_date']) );
       }else{
           $op['data']['status'] =  FALSE;
       }
       echo json_encode($op);
       return TRUE;
    }
    public function Vindetails_ajax() {        
         $applications = $this->input->post();
        $applications = $applications['applications'];
        $serviceable =$applications['serviceable'];
        $vin_no =$applications['vin_no'];
        $plant =$applications['plant'];
        $sku_code =$applications['sku_code'];
        $component =$applications['component'];
        $description =$applications['description'];
        $month_year =$applications['month_year'];
        
        $dates = (!empty($month_year)) ? $month_year : "";
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
                   
                }
        }
        

        /*make database Connection  and assign result to $data_set */

        $query0 = $this->vindetails_db($serviceable ,$plant,$sku_code,$component,$description, $dates_to_from,null,null);
        $data_set =  ($query0->num_rows() > 0)? $query0->result_array():FALSE;
        $catlog_url = $this->config->item('catlog');
       if($data_set){
        foreach ($data_set as $key => $value) {
            $op['data'][$key]['part_number'] = $value['part_number'];
            $op['data'][$key]['part_description'] = $value['material_description'];
            $op['data'][$key]['quantity'] = $value['quantity'];
            $op['data'][$key]['validity_from'] = $value['valid_from'];
            $op['data'][$key]['validity_to'] = $value['valid_to'] ;
            $op['data'][$key]['locater_code'] = $value['serial_number'];
            $op['data'][$key]['locater_description'] = !empty($value['locators_description']) ? $value['locators_description'] : $value['serial_number'];
            $op['data'][$key]['service_tag'] = $value['old_tag'];
            $op['data'][$key]['current_service_tag'] = $value['new_tag'];
            $op['data'][$key]['change_date'] = $value['change_date'];
            $op['data'][$key]['status'] = $value['status'];
            $url = !empty($value['plate_approve_id']) ? $catlog_url['url']."/plates/" . $value['plate_approve_id'] . "/sbom" : "#" ;
            $multi = !empty($value['part_number']) ? $catlog_url['url']."/multiplates/" . $sku_code ."/". $value['part_number'] : "#" ;
            $op['data'][$key]['plate'] = array('pl_id'=> $url,
                'desc'=>
                !empty($value['plate_txt']) ? $value['plate_txt'] : "--",
                'multiplate' => $multi
                    );
        
        }
        
        } else {  $op['data'] = "";}
        echo json_encode($op);
    }
    public function download_vindetails() {
        $serviceable =$this->input->get('serviceable');
        $vin_no =$this->input->get('vin_no');
        $sku_code =$this->input->get('sku_code');
        $plant =$this->input->get('plant');
        
        $component =$this->input->get('component');
        $description =$this->input->get('description');
        
        $dates = (!empty($this->input->get('month_year'))) ? $this->input->get('month_year') : "";
        $dates_to_from = explode("-",$dates);
//        $dates_to_from = (!empty($this->input->get('month_year'))) ? $this->input->get('month_year') : "";
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
                   
                }
        }
       
        $query = $this->vindetails_db($serviceable,$plant, $sku_code,$component,$description, $dates_to_from,NULL,null);
        
        $target_dtl =  ($query->num_rows() > 0)? $query->result_array():FALSE;
        /* start spreadsheet here*/
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('bajaj')
                ->setLastModifiedBy('gladminds')
                ->setTitle('Bajaj EPC')
                ->setSubject('Show vehical applicable parts')
                ->setDescription('Part catlog');


        $styleArray = array(
            'font' => array('bold' => true,),
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,),
            'borders' => array('top' => array(
            'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,),),
            'fill' => array(
                'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                'rotation' => 90,
                'startcolor' => array('argb' => 'FFA0A0A0',), 'endcolor' =>
                array('argb' => 'FFFFFFFF',),));
        $spreadsheet->getActiveSheet()->getStyle('A1:O4')->applyFromArray($styleArray);

        foreach (range('A', 'O') as $columnID) {
            $spreadsheet->getActiveSheet()->getColumnDimension($columnID)
                    ->setAutoSize(true);
        }
        if(!empty($dates_to_from)){
            $spreadsheet->setActiveSheetIndex(0)                
                        ->setCellValue("A1", 'Manufacturing Date: From:'.$dates_to_from[0].' To: '.$dates_to_from[1]);
            $spreadsheet->getActiveSheet(0)->mergeCells('A1:C1');
        }
        if(!empty($vin_no)){
        $spreadsheet->setActiveSheetIndex(0)                
                    ->setCellValue("A2", 'VIN Number: '.$vin_no);        
        $spreadsheet->getActiveSheet(0)->mergeCells('A2:C2');
        }
                
        $spreadsheet->getActiveSheet(0)->getStyle('A2')
        ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        
        $spreadsheet->setActiveSheetIndex(0)                
                ->setCellValue("A4", 'SKU Code')
                ->setCellValue("B4", 'SKU Code  Description')
                ->setCellValue("C4", 'Part Number')
                ->setCellValue("D4", 'Part Description')
                ->setCellValue("E4", 'Quantity')
                ->setCellValue("F4", 'Plant')
                ->setCellValue("G4", 'Valid From')
                ->setCellValue("H4", 'Valid To')
                ->setCellValue("I4", 'Locator Code')
                ->setCellValue("J4", 'Locator Description')
                ->setCellValue("K4", 'Service Tag')
                ->setCellValue("L4", 'Current Service Tag')
                ->setCellValue("M4", 'Change Date')
                ->setCellValue("N4", 'Status')
                ->setCellValue("O4", 'Plate')
                ;
        
        /* add data */
        
        $x = 5;
        foreach ($target_dtl as $sub) {
            $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue("A$x", $sub['sku_code'])
                    ->setCellValue("B$x", $sub['sku_description'])
                    ->setCellValue("C$x", $sub['part_number'])
                    ->setCellValue("D$x", $sub['material_description'])
                    ->setCellValue("E$x", $sub['quantity'])
                    ->setCellValue("F$x", $sub['plant'])
                    ->setCellValue("G$x", $sub['valid_from'])
                    ->setCellValue("H$x", $sub['valid_to'])
                    ->setCellValue("I$x", $sub['serial_number'])
                    ->setCellValue("J$x", $sub['locators_description'])
                    ->setCellValue("K$x", $sub['old_tag'])
                    ->setCellValue("L$x", $sub['new_tag'])
                    ->setCellValue("M$x", $sub['change_date'])
                    ->setCellValue("N$x", $sub['status'])
                    ->setCellValue("O$x", $sub['plate_txt'])
                    ;
            $x++;
        }


        $spreadsheet->getActiveSheet()->setTitle('BAJAJ PART CATLOGUE');
        $spreadsheet->setActiveSheetIndex(0);



        $writer = new Xlsx($spreadsheet);

        $filename = 'Vehicle_parts_details_'.date('Ymd');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output'); // download file 
        
    }
    private function vindetails_db($serviceable,$plant,$sku_code,$component,$description,$dates,$offset,$perpage ) {
        $ver = array(0); //STR_TO_DATE('".$dates[0]."', '%M %d, %Y')
        $skcd = "'".str_replace(",","','",$sku_code)."'";
        
        if(!empty($sku_code)){ 
            /*previous date before 2019-01-23*/
            
            $plant_q = (!empty($plant))  ? " AND plant ='".$plant."'" : ""; 
            $this->db->select('a.id As version,  `a`.`sku_code`');
            $this->db->from(" 
                (SELECT 
                *
            FROM
                gm_bomheader
            WHERE
                sku_code IN (".$skcd.")
            AND created_date <= '2019-01-23' ".$plant_q."
            ORDER BY created_date DESC) AS a ");
            $this->db->group_by('a.sku_code');
            
            $query = $this->db->get();
            $data_set =  ($query->num_rows() > 0)? $query->result_array():FALSE;
            if($data_set == FALSE){
                /*after date */
            $plant_q = (!empty($plant) and $plant != 'all') ?  "AND plant = '".$plant."'": "";
            $this->db->select('a.id As version, a.sku_code');
            $this->db->from("(SELECT 
                *
            FROM
                gm_bomheader
            WHERE
                sku_code IN (".$skcd.") ".$plant_q."
            ORDER BY created_date DESC) AS a");
            $this->db->group_by('a.sku_code');
            $query = $this->db->get();
            $data_set =  ($query->num_rows() > 0)? $query->result_array():FALSE;
                if($data_set == FALSE){
                    echo "No BOM  With SKU ".$sku_code." AND Plant ".$plant; die; return false;
                }
            }
            foreach ($data_set as $key => $value) {
                $ver[] = $value['version'];
            }             
        } else { 
            //echo "NO DATA Found"; 
            return false;
        }
        
        if(empty($plant)){
            //echo "Please add plate"; die; 
            return false;
        }
        if($plant == 'all'){
            $plant = '';
        }
      //  $this->db->select("md.product_id,DATE_FORMAT(md.vehicle_off_line_date, '%d-%m-%Y') AS vehicle_off_line_date,md.plant ,skd.sku_description,CONCAT(SUBSTRING(material_number, 1, CHAR_LENGTH(md.material_number) - 2),'ZZ') AS sku_code ");
    $this->db->select(" DISTINCT '".$dates[0]."-".$dates[1]."' AS manufacturing_date");
    $this->db->select("bh.sku_code");
    $this->db->select("skd.sku_description");
    $this->db->select("bi.item_id AS node_id");
    $this->db->select("bi.part_number");
    $this->db->select("bi.material_description");
    $this->db->select("FORMAT(FLOOR(max(bi.quantity)),0) AS quantity");
    $this->db->select("bh.plant");
    $this->db->select("DATE_FORMAT(MIN(bi.valid_from), '%d-%m-%Y') AS valid_from");
    $this->db->select("DATE_FORMAT(bi.valid_to, '%d-%m-%Y') AS valid_to");
    $this->db->select("bi.serial_number");
    $this->db->select("CONCAT_WS('-',locater.main_group,locater.sub_group) AS locators_description");
    $this->db->select("IFNULL(h.old_tag, '--') AS old_tag");
    $this->db->select("IFNULL(h.new_tag, '--') AS new_tag");
    $this->db->select("IFNULL(h.change_date, '--') AS change_date"); 
    $this->db->select("case  when bi.status is null  then 'INITIAL' else bi.status END AS status");
    $this->db->select("plat_approv.plate_code"); 
    $this->db->select("plat_approv.plate_txt"); 
    $this->db->select("plat_approv.plate_approve_id"); 
    $this->db->select("GROUP_CONCAT(bh.sku_code) AS sku_code"); 
    $this->db->from("gm_bomitem AS bi");
//    $this->db->join("gm_bomheader AS bh","bi.bom_id = bh.id","left");
    $version_ids = "'".str_replace(",","','", implode(',',$ver))."'";
     $this->db->join('gm_bomheader AS bh ',' bi.bom_id = bh.id AND bh.id in ('.$version_ids.')','left',FALSE) ;
    if($plant == 'all'){
            $plant = '';
    }
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
    $append = !empty($sku_code) && !empty($plant) ? " ,bh.sku_code,bh.plant ":",bh.sku_code,bh.plant";
    $this->db->join(" (SELECT 
    bh.sku_code,
    bh.plant,
    bp.part_number AS material_code,
    plate.plate_id AS plate_code,
    plap.id AS plate_approve_id,
    plate.plate_txt
FROM
    gm_bomheader AS bh
        LEFT JOIN
    gm_bomplatepart AS bpp ON bpp.bom_id = bh.id 
        LEFT JOIN
    gm_bompart AS bp ON bpp.part_id = bp.id
        LEFT JOIN
    gm_bomplate AS plate ON bpp.plate_id = plate.id
        LEFT JOIN
    gm_epc_plateimages AS plap ON plap.plate_id = plate.id AND plap.`status` = 'Approved' group by material_code ".$append." ) AS plat_approv","bi.part_number=plat_approv.material_code AND bh.plant= plat_approv.plant AND bh.sku_code = plat_approv.sku_code ","left");
    
    
    
//        !empty($dates) ?  $this->db->where("(bi.valid_from <= '".$dates."' AND ( bi.valid_to = '9999-12-31' OR '".$dates."' <= bi.valid_to)) ") : "";
        !empty($dates) ?  $this->db->where("(bi.valid_from >= STR_TO_DATE('".$dates[0]."', '%M %d, %Y') AND ( bi.valid_to = '9999-12-31' OR STR_TO_DATE('".$dates[1]."', '%M %d, %Y') <= bi.valid_to)) ") : "";
        !empty($sku_code) ?  $this->db->where_in('bh.sku_code', explode(",", $sku_code)) : "";
        !empty($component) ?  $this->db->where('bi.part_number',$component) : "";
        !empty($description) ?  $this->db->like('bi.material_description',$description) : "";
        !empty($plant) ?  $this->db->where('bh.plant',$plant) : "";
        !empty($serviceable) AND ($serviceable == "serviceable") ?  $this->db->where('(h.old_tag is not null or h.new_tag is not null)') : "";
        $this->db->group_by('bi.part_number,bi.valid_to');
//        $this->db->order_by('bi.part_number ASC');
        (!empty($offset) || $offset == 0) ? $this->db->limit($perpage,$offset) : "";
     //  $this->db->limit(10) ;
        $query0 = $this->db->get();
        
        return $query0;
    }
    public function plates_sku() {
       $plant = $this->input->get('plant');
        $data=$op =array();
        
        $this->db->select('sk.sku_code,sk.sku_description');
        $this->db->from('gm_skudetails_custom  AS sk');
        $this->db->join('gm_bomheader AS bh','bh.sku_code = sk.sku_code');
        $this->db->group_by('bh.plant,sk.sku_code');
        (!empty($plant ) AND $plant != 'null' AND $plant != 'all') ? $this->db->where('bh.plant',$plant) : "";
        $query = $this->db->get();
        $sku_dtl =  ($query->num_rows() > 0)? $query->result_array():FALSE;
//        echo $this->db->last_query(); die;
        if($sku_dtl){
            $i = 0;
            $data[$i]['id']="";
                $data[$i]['text']="";
                $i=1;
            foreach ($sku_dtl as $key => $value) {
                $data[$i]['id']=$value['sku_code'];
                $data[$i]['text']=$value['sku_description']."(".$value['sku_code'].")";
                $i++;
            }
            $op['status']= TRUE;
            $op['sku']= $data;
        }else{
            $op['status']= false;
        }
        echo json_encode($op);
    }
    public function sku_manufacturing_date() {
       $sku_code = $this->input->get('sku_code');
       $plant = $this->input->get('plant');
        $data=$op =array();
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
            
            $op['status']= TRUE;
            $op['sku_manufacturing_date']= $sku_dtl[0]['valid_from'];
        }else{
            $op['status']= false;
        }
        echo json_encode($op);
    }
}

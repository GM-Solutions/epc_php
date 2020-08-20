<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Epc_reports
 *
 * @author pavaningalkar
 */
class Epc_reports extends CI_Controller {
    //put your code here
    public function __construct() {
        parent::__construct();
        $this->load->library("session");
        $this->load->helper('url');
        $this->load->library('breadcrumbs'); /*for breadcrumbs*/
        $this->load->database();
        $this->load->library('S3');/* for AWS S3 bucket Transactions */

    }
    public function Vindetails() {
        $data =  array();
        $catlog_url = $this->config->item('catlog');
        $data['siteurl']= $catlog_url['url'];
        $this->load->view('vin_detils',$data);
    }
    public function Vindetails_ajax() {
        $vin_no =$this->input->post('vin_no');
        $plant =$this->input->post('plant');
        $sku_code =$this->input->post('sku_code');
        
        $date_filter =$this->input->post('date_filter');
        
        
        
        $dates = (!empty($this->input->post('month_year'))) ? $this->input->post('month_year') : "";
        $dates_to_from = explode("-",$dates);
        
        $this->load->library('Ajax_pagination');
        $this->perPage = 20;
        $page = $this->input->post('page');
        $offset = !empty($page) ? $page : 0;
        /*make database Connection  and assign result to $data_set */

        $query0 = $this->vindetails_db($date_filter,$vin_no, $plant,$sku_code, $dates_to_from,null,null);
        $data_set =  ($query0->num_rows() > 0)? $query0->result_array():FALSE;
        $totalRec = $query0->num_rows();

        //pagination configuration
        $config['target']      = '#documentlist';
        $config['base_url']    = base_url().'Info/pagination';
        $config['total_rows']  = $totalRec;
        $config['per_page']    =  $this->perPage;
        
        $config['link_func']   = 'searchFilter';
        $this->ajax_pagination->initialize($config);
        
        //$data['data_set_dtl'] = ($data_set) ? array_slice($data_set,$offset,$this->perPage) : FALSE;
        $query = $this->vindetails_db($date_filter,$vin_no,$plant, $sku_code, $dates_to_from,$offset,$this->perPage);
        $data['data_set_dtl'] =  ($query->num_rows() > 0)? $query->result_array():FALSE;
        $this->load->view('pagination/pagination',$data);
    }
    public function download_vindetails() {
        $vin_no =$this->input->get('vin_no');
        $sku_code =$this->input->get('sku_code');
        $plant =$this->input->get('plant');
        $date_filter =$this->input->get('date_filter');
        
        $dates = (!empty($this->input->get('month_year'))) ? $this->input->get('month_year') : "";
        $dates_to_from = explode("-",$dates);
        $filename = 'vindtl_'.date('Ymd').'.csv'; 
        header("Content-Description: File Transfer"); 
        header("Content-Disposition: attachment; filename=$filename"); 
        header("Content-Type: application/csv; ");   
        $query = $this->vindetails_db($date_filter,$vin_no,$plant, $sku_code, $dates_to_from,NULL,null);
        
        $target_dtl =  ($query->num_rows() > 0)? $query->result_array():FALSE;
        $file = fopen('php://output', 'w');

            $header = array("VIN No","Manufacture date","Production plant","SKU Code Description","SKU Code","SBOMB Exist","Data Import Date"); 
            fputcsv($file, $header);
            if($target_dtl){
            foreach ($target_dtl as $key=>$line){ 
              fputcsv($file,$line); 
            }
            }
            fclose($file); 
            exit; 
        
    }
    private function vindetails_db($date_filter,$vin_no,$plant,$sku_code,$dates_to_from,$offset,$perpage ) {
        
        $this->db->select("md.product_id,DATE_FORMAT(md.vehicle_off_line_date, '%d-%m-%Y') AS vehicle_off_line_date,md.plant ,skd.sku_description,CONCAT(SUBSTRING(material_number, 1, CHAR_LENGTH(md.material_number) - 2),'ZZ') AS sku_code ");
        
        $this->db->select("case
        WHEN md.bomheader_id is null then 'NO'
        else 'YES'
        END AS sbomb_exists"); 
        $this->db->select("DATE_FORMAT(md.created_date, '%d-%m-%Y') AS data_import_date");
        $this->db->from('gm_manufacturingdata AS md');
        $this->db->join('gm_bomheader AS bh','md.bomheader_id = bh.id','left');
        $this->db->join('gm_skudetails AS skd','skd.sku_code = bh.sku_code','left');
        if($date_filter === "Manufacturing"){
        !empty($dates_to_from) ? $this->db->where("md.vehicle_off_line_date  between STR_TO_DATE('".$dates_to_from[0]."', '%M %d, %Y') AND  STR_TO_DATE('".$dates_to_from[1]."', '%M %d, %Y') + INTERVAL 1 DAY ") : "";
        }
        if($date_filter === "Import"){
        !empty($dates_to_from) ? $this->db->where("md.created_date  between STR_TO_DATE('".$dates_to_from[0]."', '%M %d, %Y') AND  STR_TO_DATE('".$dates_to_from[1]."', '%M %d, %Y') + INTERVAL 1 DAY ") : "";
        }
        !empty($vin_no) ? $this->db->where('md.product_id',$vin_no) : "";
        !empty($sku_code) ?  $this->db->having('sku_code',$sku_code) : "";
        !empty($plant) ?  $this->db->where('bh.plant',$plant) : "";
        (!empty($offset) || $offset == 0) ? $this->db->limit($perpage,$offset) : "";
        $query0 = $this->db->get();
        return $query0;
    }
}

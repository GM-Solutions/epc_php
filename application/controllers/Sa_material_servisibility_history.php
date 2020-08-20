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
class Sa_material_servisibility_history extends CI_Controller {
    //put your code here
    public function __construct() {
        parent::__construct();
        $this->load->library("session");
        $this->load->helper('url');
        $this->load->library('breadcrumbs'); /*for breadcrumbs*/
        $this->load->database();
        $this->load->library('S3');/* for AWS S3 bucket Transactions */

    }
    public function index() {
        $catlog_url = $this->config->item('catlog');
        $data['siteurl']= $catlog_url['url'];
        $this->load->view('sa/material_servisibility_history',$data);
    }
    public function servisibility_ajax() {        
        $material_code =$this->input->post('material_code');           
         $dates = (!empty($this->input->post('month_year'))) ? $this->input->post('month_year') : "";
        $dates_to_from = explode("-",$dates);        
        
        $this->load->library('Ajax_pagination');
        $this->perPage = 20;
        $page = $this->input->post('page');
        $offset = !empty($page) ? $page : 0;
        /*make database Connection  and assign result to $data_set */

        $query0 = $this->sbombdtl_db($dates_to_from,$material_code, null,null,"NO");
        $data_set =  ($query0->num_rows() > 0)? $query0->result_array():FALSE;
        $totalRec = $query0->num_rows();

        //pagination configuration
        $config['target']      = '#documentlist';
        $config['base_url']    = base_url().'Sa_material_servisibility_history/servisibility_ajax';
        $config['total_rows']  = $totalRec;
        $config['per_page']    =  $this->perPage;
        
        $config['link_func']   = 'searchFilter';
        $this->ajax_pagination->initialize($config);
        
        //$data['data_set_dtl'] = ($data_set) ? array_slice($data_set,$offset,$this->perPage) : FALSE;
        $query = $this->sbombdtl_db($dates_to_from,$material_code,$offset,$this->perPage,"NO");
        $data['data_set_dtl'] =  ($query->num_rows() > 0)? $query->result_array():FALSE;
 
        $this->load->view('sa/pagination/material_servisibility_history_pagination',$data);
    }
    public function download_sbometails() {
        $dates = (!empty($this->input->get('month_year'))) ? $this->input->get('month_year') : "";
        $dates_to_from = explode("-",$dates);
        $material_code =$this->input->get('material_code');        
        $filename = 'material_servisibility_history_'.date('Ymd').'.csv'; 
        header("Content-Description: File Transfer"); 
        header("Content-Disposition: attachment; filename=$filename"); 
        header("Content-Type: application/csv; ");   
        $query = $this->sbombdtl_db($dates_to_from,$material_code,NULL,null,"YES");
        $target_dtl =  ($query->num_rows() > 0)? $query->result_array():FALSE;
        
        $file = fopen('php://output', 'w');
        $header = array("Material Number","Material Description","Old Tag","New Tag","Update On"); 
        fputcsv($file, $header);
        if($target_dtl){
            foreach ($target_dtl as $key=>$line){ 
              fputcsv($file,$line); 
            }
        }
        fclose($file); 
        exit; 
        
    }
    private function sbombdtl_db($dates_to_from,$material_code,$offset,$perpage,$downloadable) {
        
        /*get the latest version of sku code */
       
        
        $this->db->select('h.material_number');
        $this->db->select('bi.material_description');
        $this->db->select("IFNULL(h.old_tag, '--') AS old_tag");
        $this->db->select("IFNULL(h.new_tag, '--') AS new_tag");
        $this->db->select("IFNULL(h.change_date, '--') AS change_date"); 
//        $this->db->select("GROUP_CONCAT(CONCAT_WS('+',
//                IFNULL(h.old_tag, 'N/A'),
//                IFNULL(h.new_tag, 'N/A'),
//                h.change_date)) AS as_tags");
        
        /*------------------------*/
        $this->db->from('gm_serviceability_mtr_history AS h');
        $this->db->join('gm_bomitem AS bi','h.material_number = bi.part_number','');
        $this->db->group_by('h.material_number, change_date');
        
        
        
        !empty($material_code) ?  $this->db->where("h.material_number like '%".$material_code."%' OR bi.material_description like '%".$material_code."%' ") : "";
        !empty($dates_to_from) ? $this->db->where("h.change_date  between STR_TO_DATE('".$dates_to_from[0]."', '%M %d, %Y') AND  STR_TO_DATE('".$dates_to_from[1]."', '%M %d, %Y') + INTERVAL 1 DAY ") : "";
//        $this->db->where('h.change_date is not null');
        $this->db->order_by('h.material_number');
        
        (!empty($offset) || $offset == 0) ? $this->db->limit($perpage,$offset) : "";
        $query0 = $this->db->get();
        return $query0;
    }
}

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
class Epc_sbomb extends CI_Controller {
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
        
        $this->load->view('sbomb_detils');
    }
    public function sbomb_ajax() {        
        $sku_code =$this->input->post('sku_code');           
        $mm_code_description =$this->input->post('mm_code_description');           
               
        
        $this->load->library('Ajax_pagination');
        $this->perPage = 20;
        $page = $this->input->post('page');
        $offset = !empty($page) ? $page : 0;
        /*make database Connection  and assign result to $data_set */

        $query0 = $this->sbombdtl_db($sku_code,$mm_code_description, null,null,"NO");
        $data_set =  ($query0->num_rows() > 0)? $query0->result_array():FALSE;
        $totalRec = $query0->num_rows();

        //pagination configuration
        $config['target']      = '#documentlist';
        $config['base_url']    = base_url().'epc_sbomb/sbomb_ajax';
        $config['total_rows']  = $totalRec;
        $config['per_page']    =  $this->perPage;
        
        $config['link_func']   = 'searchFilter';
        $this->ajax_pagination->initialize($config);
        
        //$data['data_set_dtl'] = ($data_set) ? array_slice($data_set,$offset,$this->perPage) : FALSE;
        $query = $this->sbombdtl_db( $sku_code,$mm_code_description,$offset,$this->perPage,"NO");
        $data['data_set_dtl'] =  ($query->num_rows() > 0)? $query->result_array():FALSE;
 
        $this->load->view('pagination/sbomb_pagination',$data);
    }
    public function download_sbometails() {
        $sku_code =$this->input->get('sku_code');    
        $mm_code_description =$this->input->get('mm_code_description'); 
        $filename = 'sbom_report_'.date('Ymd').'.csv'; 
        header("Content-Description: File Transfer"); 
        header("Content-Disposition: attachment; filename=$filename"); 
        header("Content-Type: application/csv; ");   
        $query = $this->sbombdtl_db($sku_code,$mm_code_description,NULL,null,"YES");
        $target_dtl =  ($query->num_rows() > 0)? $query->result_array():FALSE;
        
        $file = fopen('php://output', 'w');
        $header = array("SKU Code","Plant","Material Code","Material Description","SKU Create On","Valid From","Valid To","Current Validity"); 
        fputcsv($file, $header);
        if($target_dtl){
            foreach ($target_dtl as $key=>$line){ 
              fputcsv($file,$line); 
            }
        }
        fclose($file); 
        exit; 
        
    }
    private function sbombdtl_db($sku_code,$mm_code_description,$offset,$perpage,$downloadable) {
        
        /*get the latest version of sku code */
        $ver = 0;
        if(!empty($sku_code)){
            $this->db->select('max(bh.`version`) As version');
            $this->db->from('gm_bomheader AS bh');
            $this->db->where('sku_code',$sku_code);
            $query = $this->db->get();
            $data_set =  ($query->num_rows() > 0)? $query->result_array():FALSE;
            $ver = $data_set[0]['version'];
        }
        
        /* gm_bompart -> part_number (Material code) */
        $this->db->select('bh.sku_code');
        $this->db->select('bh.plant');
        $this->db->select('bp.part_number AS material_code');
        $this->db->select('bp.description AS material_description');
        $this->db->select("DATE_FORMAT(bh.created_on, '%d-%m-%Y') AS sku_created_on");
        $this->db->select("DATE_FORMAT(bpp.valid_from, '%d-%m-%Y') AS valid_from");
        $this->db->select("DATE_FORMAT(bpp.valid_to, '%d-%m-%Y') AS valid_to");
        
        if($downloadable === "YES"){
        $this->db->select("case
        WHEN bp.valid_to != '9999-12-31' then 'NO'
        else 'YES'
        END AS validity"); } else {
//        $this->db->select('bpp.change_number');
//        $this->db->select('bpp.change_number_to');
        $this->db->select('plate.plate_id AS plate_code');
        $this->db->select('plap.id AS plate_approve_id');
        }
        
        
        
        $this->db->from('gm_bomheader AS bh');
        $this->db->join('gm_skudetails AS skd','skd.sku_code = bh.sku_code');
        $this->db->join('gm_bomplatepart AS bpp', 'bpp.bom_id = bh.id', 'left');
        $this->db->join('gm_bompart AS bp', 'bpp.part_id = bp.id', 'left');
        
        $this->db->join('gm_bomplate AS plate', 'bpp.plate_id = plate.id', 'left');
        $this->db->join('gm_epc_plateimages AS plap', 'plap.plate_id = plate.id', 'left');
        
        $this->db->where('plap.status', 'Approved');
        !empty($ver) ?  $this->db->where('bh.version',$ver) : "";
        !empty($sku_code) ?  $this->db->where('bh.sku_code',$sku_code) : "";
        
        !empty($mm_code_description) ?  $this->db->where(" ( bp.description like '%".$mm_code_description."%' OR bp.part_number like '%".$mm_code_description."%' )") : "";
        (!empty($offset) || $offset == 0) ? $this->db->limit($perpage,$offset) : "";
        $query0 = $this->db->get();
        return $query0;
    }
}

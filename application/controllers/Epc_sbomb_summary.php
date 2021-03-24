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
class Epc_sbomb_summary extends CI_Controller {
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
        
        $this->load->view('sbomb_summary_detils');
    }
    public function sbomb_ajax() {        
        $sku_code =$this->input->post('sku_code');           
        
        $this->load->library('Ajax_pagination');
        $this->perPage = 20;
        $page = $this->input->post('page');
        $offset = !empty($page) ? $page : 0;
        /*make database Connection  and assign result to $data_set */

        $query0 = $this->sbombdtl_db($sku_code, null,null,"NO");
        $data_set =  ($query0->num_rows() > 0)? $query0->result_array():FALSE;
        $totalRec = $query0->num_rows();

        //pagination configuration
        $config['target']      = '#documentlist';
        $config['base_url']    = base_url().'epc_sbomb_summary/sbomb_ajax';
        $config['total_rows']  = $totalRec;
        $config['per_page']    =  $this->perPage;
        
        $config['link_func']   = 'searchFilter';
        $this->ajax_pagination->initialize($config);
        
        //$data['data_set_dtl'] = ($data_set) ? array_slice($data_set,$offset,$this->perPage) : FALSE;
        $query = $this->sbombdtl_db( $sku_code,$offset,$this->perPage,"NO");
        $data['data_set_dtl'] =  ($query->num_rows() > 0)? $query->result_array():FALSE;
 
        $this->load->view('pagination/sbomb_summary_pagination',$data);
    }
    public function download_sbometails() {
        $sku_code =$this->input->get('sku_code');        
        $filename = 'sbom_summary_report_'.date('Ymd').'.csv'; 
        header("Content-Description: File Transfer"); 
        header("Content-Disposition: attachment; filename=$filename"); 
        header("Content-Type: application/csv; ");   
        $query = $this->sbombdtl_db( $sku_code,NULL,null,"YES");
        $target_dtl =  ($query->num_rows() > 0)? $query->result_array():FALSE;
        
        $file = fopen('php://output', 'w');
        $header = array("SKU Code","SKU Description","Plant","Count of Serviceable parts","Count of non-serviceable parts"); 
        fputcsv($file, $header);
        if($target_dtl){
            foreach ($target_dtl as $key=>$line){ 
              fputcsv($file,$line); 
            }
        }
        fclose($file); 
        exit; 
        
    }
    private function sbombdtl_db($sku_code,$offset,$perpage,$downloadable) {
        
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
        
        
        $this->db->select('bh.sku_code');
        $this->db->select('skd.sku_description');
        $this->db->select('bh.plant');
        $this->db->select("SUM(CASE
        WHEN bp.valid_to = '9999-12-31' THEN 1
        ELSE 0
    END) AS valid_count");
        $this->db->select("SUM(CASE
        WHEN bp.valid_to != '9999-12-31' THEN 1
        ELSE 0
    END) AS not_valid_count");
        
        $this->db->from('gm_bomheader AS bh');
        $this->db->join('gm_skudetails AS skd','skd.sku_code = bh.sku_code');
        $this->db->join('gm_bomplatepart AS bpp', 'bpp.bom_id = bh.id', 'left');
        $this->db->join('gm_bompart AS bp', 'bpp.part_id = bp.id', 'left');
        
        
        !empty($ver) ?  $this->db->where('bh.version',$ver) : "";
        !empty($sku_code) ?  $this->db->where('bh.sku_code',$sku_code) : "";
        $this->db->group_by('skd.sku_code');
        $this->db->group_by('bh.plant');
        (!empty($offset) || $offset == 0) ? $this->db->limit($perpage,$offset) : "";
        $query0 = $this->db->get();
        return $query0;
    }
}

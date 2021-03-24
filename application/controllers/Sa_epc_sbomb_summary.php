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
class Sa_epc_sbomb_summary extends CI_Controller {
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
        $data =  array();
        $this->db->select('sk.sku_code,sk.sku_description');
            $this->db->from('gm_skudetails_custom  AS sk');
            $this->db->join('gm_bomheader AS bh','bh.sku_code = sk.sku_code');
            $this->db->group_by('sk.sku_code');
            $query = $this->db->get();
            $sku_dtl =  ($query->num_rows() > 0)? $query->result_array():FALSE;
            $data['sku_codes'] = $sku_dtl;
        $this->load->view('sa/sbomb_summary_detils',$data);
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
        $config['base_url']    = base_url().'sa_epc_sbomb_summary/sbomb_ajax';
        $config['total_rows']  = $totalRec;
        $config['per_page']    =  $this->perPage;
        
        $config['link_func']   = 'searchFilter';
        $this->ajax_pagination->initialize($config);
        
        //$data['data_set_dtl'] = ($data_set) ? array_slice($data_set,$offset,$this->perPage) : FALSE;
        $query = $this->sbombdtl_db( $sku_code,$offset,$this->perPage,"NO");
        $data['data_set_dtl'] =  ($query->num_rows() > 0)? $query->result_array():FALSE;
 
        $this->load->view('sa/pagination/sbomb_summary_pagination',$data);
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
        $header = array("SKU Code","SKU Description","Plant","Count Unique Parts"); 
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
       
        
        $this->db->select('header.sku_code');
        $this->db->select('skd.sku_description');
        $this->db->select('header.plant');
        
        $this->db->select("COUNT(DISTINCT (bi.item_id)) AS part_count");       
        
        /*------------------------*/
        $this->db->from('gm_bomitem AS bi');
        $this->db->join('gm_bomheader AS header ',' bi.bom_id = header.id','left');
        $this->db->join('gm_skudetails_custom AS skd','skd.sku_code = header.sku_code','left');
        
        $this->db->where('header.id is not null');
        !empty($sku_code) ?  $this->db->where('header.sku_code',$sku_code) : "";
        $this->db->group_by('header.sku_code');
        $this->db->group_by('header.plant');
        (!empty($offset) || $offset == 0) ? $this->db->limit($perpage,$offset) : "";
        $query0 = $this->db->get();
        return $query0;
    }
}

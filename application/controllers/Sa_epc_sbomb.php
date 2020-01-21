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
class Sa_epc_sbomb extends CI_Controller {
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
        /*get sku details */
        $this->db->select('sk.sku_code,sk.sku_description');
            $this->db->from('gm_skudetails_custom  AS sk');
            $this->db->join('gm_bomheader AS bh','bh.sku_code = sk.sku_code');
            $this->db->group_by('sk.sku_code');
            $query = $this->db->get();
            $sku_dtl =  ($query->num_rows() > 0)? $query->result_array():FALSE;
            $data['sku_codes'] = $sku_dtl;
         $catlog_url = $this->config->item('catlog');
        $data['siteurl']= $catlog_url['url'];
        $this->load->view('sa/sbomb_detils',$data);
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
        $config['base_url']    = base_url().'sa_epc_sbomb/sbomb_ajax';
        $config['total_rows']  = $totalRec;
        $config['per_page']    =  $this->perPage;
        
        $config['link_func']   = 'searchFilter';
        $this->ajax_pagination->initialize($config);
        
        //$data['data_set_dtl'] = ($data_set) ? array_slice($data_set,$offset,$this->perPage) : FALSE;
        $query = $this->sbombdtl_db( $sku_code,$mm_code_description,$offset,$this->perPage,"NO");
        $data['data_set_dtl'] =  ($query->num_rows() > 0)? $query->result_array():FALSE;
        
        /*plat linking*/
        
 
        $this->load->view('sa/pagination/sbomb_pagination',$data);
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
        $header =  array();
        $header[]= "SKU Code";
        $header[]= "Plant Code";
        $header[]= "Material Code";
        $header[]= "Material Description";
        $header[]= "Node ID";
        $header[]= "Quantity";
        $header[]= "Locator code";
        //$header[]= "Locator Description";
        $header[]= "Status";
        $header[]= "Valid from date";
        $header[]= "Valid to date";
        $header[]= "Currently valid";
        
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
            $this->db->select('id As version');
            $this->db->from('gm_bomheader AS bh');
            $this->db->where('sku_code',$sku_code);
            $this->db->order_by('created_date','DESC');
            $this->db->limit(1);
            $query = $this->db->get();
            $data_set =  ($query->num_rows() > 0)? $query->result_array():FALSE;
            if($data_set == FALSE){echo "No Bom With SKU ".$sku_code." "; die; return false;}
            $ver = $data_set[0]['version'];
        } else {
            echo "NO DATA Found"; die; return false;
        }
        
        
        /* gm_bompart -> part_number (Material code) */
        $this->db->select('header.sku_code');
        $this->db->select('header.plant');
        $this->db->select('bi.part_number AS material_code');
        $this->db->select('bi.material_description AS material_description');
        $this->db->select("bi.item_id AS node_id");
        $this->db->select("TRUNCATE(bi.quantity,0) AS quantity");
        $this->db->select("bi.serial_number AS locators");
        $this->db->select("CONCAT_WS('-',locater.main_group,locater.sub_group) AS locators_description");
        $this->db->select("case  when bi.status is null  then 'INITIAL' else bi.status END AS status");

        $this->db->select("DATE_FORMAT(bi.valid_from, '%d-%m-%Y') AS valid_from");
        $this->db->select("DATE_FORMAT(bi.valid_to, '%d-%m-%Y') AS valid_to");
        
        if($downloadable === "YES"){
        $this->db->select("case
        WHEN bi.valid_to != '9999-12-31' then 'NO'
        else 'YES'
        END AS validity"); } 
        
        
        
        $this->db->from('gm_bomitem AS bi');
        
        !empty($sku_code) ? $this->db->join('gm_bomheader AS header ',' bi.bom_id = header.id AND header.id = '.$ver,'left') : $this->db->join('gm_bomheader AS header ',' bi.bom_id = header.id','left');/*new*/
        
        //!empty($sku_code) ? $this->db->join('gm_bomheader AS header ',' bi.bom_id = header.id AND header.version = '.$ver,'left') : $this->db->join('gm_bomheader AS header ',' bi.bom_id = header.id','left');
        $this->db->join('gm_locator_desc AS locater ',' bi.serial_number = locater.locator_codes','left');
        
       
        !empty($sku_code) ?  $this->db->where('header.sku_code',$sku_code) : $this->db->where('header.sku_code is not null');
        
        !empty($mm_code_description) ?  $this->db->where(" ( bi.part_number like '%".$mm_code_description."%' OR bi.material_description like '%".$mm_code_description."%' )") : "";
        (!empty($offset) || $offset == 0) ? $this->db->limit($perpage,$offset) : "";
        $query0 = $this->db->get();
        return $query0;
    }
}

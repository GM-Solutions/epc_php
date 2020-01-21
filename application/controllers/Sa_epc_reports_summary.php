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
class Sa_epc_reports_summary extends CI_Controller {

    //put your code here
    public function __construct() {
        parent::__construct();
        $this->load->library("session");
        $this->load->helper('url');
        $this->load->library('breadcrumbs'); /* for breadcrumbs */
        $this->load->database();
        $this->load->library('S3'); /* for AWS S3 bucket Transactions */
    }

    public function Vindetails() {
        $data =  array();
         $this->db->select('sk.sku_code,sk.sku_description');
            $this->db->from('gm_skudetails_custom  AS sk');
            $this->db->join('gm_bomheader AS bh','bh.sku_code = sk.sku_code');
            $this->db->group_by('sk.sku_code');
            $query = $this->db->get();
            $sku_dtl =  ($query->num_rows() > 0)? $query->result_array():FALSE;
            $data['sku_codes'] = $sku_dtl;
            
         $this->db->select('*');
            $this->db->from('gm_plant_details');
            $query = $this->db->get();
            $vin_codes =  ($query->num_rows() > 0)? $query->result_array():FALSE;
            $data['vin_codes'] = $vin_codes;
            $catlog_url = $this->config->item('catlog');
        $data['siteurl']= $catlog_url['url'];
        $this->load->view('sa/vin_detils_summary',$data);
    }

    public function Vindetails_ajax() {

        $sbomb_exists = $this->input->post('sbomb_exists');
        $plant = $this->input->post('plant');
        $sku_code = $this->input->post('sku_code');
        $date = $this->input->post('date');
        $sku_desc = $this->input->post('sku_desc');
        $select_date_date = $this->input->post('select_date_date');

        $dates = (!empty($this->input->post('month_year'))) ? $this->input->post('month_year') : "";
        $dates_to_from = explode("-", $dates);

        $this->load->library('Ajax_pagination');
        $this->perPage = 20;
        $page = $this->input->post('page');
        $offset = !empty($page) ? $page : 0;
        /* make database Connection  and assign result to $data_set */

        $query0 = $this->vindetails_db($plant, $sku_code, $sku_desc, $dates_to_from, $sbomb_exists, null, null);
        $data_set = ($query0->num_rows() > 0) ? $query0->result_array() : FALSE;
        $totalRec = $query0->num_rows();

        //pagination configuration
        $config['target'] = '#documentlist';
        $config['base_url'] = base_url() . 'Info/pagination';
        $config['total_rows'] = $totalRec;
        $config['per_page'] = $this->perPage;

        $config['link_func'] = 'searchFilter';
        $this->ajax_pagination->initialize($config);

        //$data['data_set_dtl'] = ($data_set) ? array_slice($data_set,$offset,$this->perPage) : FALSE;
        $query = $this->vindetails_db($plant, $sku_code, $sku_desc, $dates_to_from, $sbomb_exists, $offset, $this->perPage);
        $data['data_set_dtl'] = ($query->num_rows() > 0) ? $query->result_array() : FALSE;

        $this->load->view('sa/pagination/vin_details_summary', $data);
    }

    public function vin_count_summary() {
        $plant = $this->input->get('plant');
        $sku_code = $this->input->get('sku_code');
        //echo $import_month = $this->input->get('month_year');
        $sbom_exist = $this->input->get('sbom_exists');
        $dates = (!empty($this->input->get('month_year'))) ? $this->input->get('month_year') : "";
        $dates_to_from = explode("-", $dates);
        //print_r($dates_to_from);
        $query = $this->vin_count_db($plant, $sku_code, $dates_to_from, $sbom_exist);
        $data['data_set_dtl'] =  ($query->num_rows() > 0)? $query->result_array():FALSE;
        //print_r($data);
        
        $this->load->view('sa/vin_count_summary',$data);
    }

    public function vin_count_db($plant, $sku_code, $dates_to_from, $sbom_exist) {
        $this->db->select('`skd`.`sku_description`');
//        $this->db->select('`skd`.`created_date`');
        $this->db->select('`md`.`product_id`');
        $this->db->select('`md`.`vehicle_off_line_date`');
        $this->db->select('`bh`.`sku_code`');
        $this->db->select('`md`.`plant`');
        if (!empty($sbomb_exist)) {
            $this->db->select("case
        WHEN md.bomheader_id is null then 'NO'
        else 'YES'
        END AS sbomb_exists");
        } else {
            $this->db->select('"YES/NO" AS sbomb_exists');
        }
        $this->db->select("DATE_FORMAT(md.vehicle_off_line_date, '%d-%m-%Y %l:%i %p') AS data_manufacture_month_year");
        $this->db->from('gm_manufacturingdata AS md');
        $this->db->join('gm_bomheader AS bh', 'md.bomheader_id = bh.id', 'left');
        $this->db->join('gm_skudetails AS skd', 'skd.sku_code = bh.sku_code','left');
        !empty($dates_to_from) ? $this->db->where("md.vehicle_off_line_date  between STR_TO_DATE('" . $dates_to_from[0] . "', '%M %d, %Y') AND  STR_TO_DATE('" . $dates_to_from[1] . "', '%M %d, %Y') + INTERVAL 1 DAY ") : "";

        if (!empty($sbomb_exists)) {
            if ($sbomb_exists == "YES") {
                $this->db->where('md.bomheader_id is not null');
            } elseif ($sbomb_exists == "NO") {
                $this->db->where('md.bomheader_id is null');
            }
        }
        if(!empty($sku_code) && !empty($plant)){
            $this->db->where("(bh.sku_code like '%" . $sku_code . "%' and `md`.`plant` like '%" . $plant . "%') ");
        }elseif(empty($sku_code) && !empty($plant)){
             $this->db->where("(bh.sku_code is null AND `md`.`plant` like '%" . $plant . "%') ");
        }elseif(!empty($sku_code) && empty($plant)){
             $this->db->where("(bh.sku_code like '%" . $sku_code . "%' )");
        }

        //!empty($plant) ? $this->db->where('bh.plant', $plant) : "";
        //!empty($sbomb_exists) ? $this->db->group_by('sbomb_exists') : "";
        //(!empty($offset) || $offset == 0) ? $this->db->limit($perpage, $offset) : "";
        $query0 = $this->db->get();
        //print_r($query0);
//        echo $this->db->last_query();
        return $query0;
    }

    public function download_vindetails() {
        $sku_desc = $this->input->post('sku_desc');
        $sbomb_exists = $this->input->get('sbomb_exists');
        $date = $this->input->get('date');
        $sku_code = $this->input->get('sku_code');
        $plant = $this->input->get('plant');

        $dates = (!empty($this->input->get('month_year'))) ? $this->input->get('month_year') : "";
        $dates_to_from = explode("-", $dates);
        $filename = 'vindtlsummary_' . date('Ymd') . '.csv';
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/csv; ");
        $query = $this->vindetails_db($plant, $sku_code, $sku_desc,$dates_to_from, $sbomb_exists, NULL, null);
        $target_dtl = ($query->num_rows() > 0) ? $query->result_array() : FALSE;
        $file = fopen('php://output', 'w');

        $header = array("SKU Description", "Sku Code", "Production plant", "Vin Count", "SBOM Exist", "Manufacturing Date");
        fputcsv($file, $header);
        if ($target_dtl) {
            foreach ($target_dtl as $key => $line) {
                fputcsv($file, $line);
            }
        }
        fclose($file);
        exit;
    }

    private function vindetails_db($plant, $sku_code, $sku_desc, $dates_to_from, $sbomb_exists, $offset, $perpage) {


        $this->db->select('`skd`.`sku_description`');
//        $this->db->select("CONCAT(SUBSTRING(material_number, 1, CHAR_LENGTH(md.material_number) - 2),'ZZ') AS sku_code ");
        $this->db->select('`skd`.`sku_code`');
        $this->db->select('`md`.`plant`');
        $this->db->select('COUNT(`md`.`product_id`) AS Vin_count');
        if (!empty($sbomb_exists)) {
            $this->db->select("CASE
        WHEN bh.id IS NULL THEN 'NO'
        ELSE 'YES'
    END AS sbomb_exists");
        } else {
            //$this->db->select('"YES/NO" AS sbomb_exists');
            $this->db->select("CASE
        WHEN bh.id IS NULL THEN 'NO'
        ELSE 'YES'
    END AS sbomb_exists");
        }
        $this->db->select("DATE_FORMAT(md.vehicle_off_line_date, '%d-%m-%Y') AS data_manufacture_month_year");
        $this->db->from('gm_manufacturingdata AS md');
        $this->db->join("`gm_skudetails_custom` AS `skd`","`skd`.`sku_code` = CONCAT(SUBSTRING(md.material_number,
                1,
                CHAR_LENGTH(md.material_number) - 2),
            'ZZ')","left");
        $this->db->join("gm_bomheader AS bh","bh.sku_code = `skd`.`sku_code` AND bh.plant = md.plant","LEFT");

        //!empty($dates_to_from) ? $this->db->where("md.vehicle_off_line_date  between STR_TO_DATE('" . $dates_to_from[0] . "', '%M %d, %Y') AND  STR_TO_DATE('" . $dates_to_from[1] . "', '%M %d, %Y')") : "";
        !empty($dates_to_from) ? $this->db->where("md.vehicle_off_line_date  between STR_TO_DATE('" . $dates_to_from[0] . "', '%M %d, %Y') AND  STR_TO_DATE('" . $dates_to_from[1] . "', '%M %d, %Y') + INTERVAL 1 DAY ") : "";

        if (!empty($sbomb_exists)) {
            if ($sbomb_exists == "YES") {
                $this->db->where('bh.id is not null');
            } elseif ($sbomb_exists == "NO") {
                $this->db->where('bh.id is null');
            }
        }
        !empty($sku_code) ? $this->db->where("(bh.sku_code = '" . $sku_code . "') ") : "";
        !empty($plant) ? $this->db->where('bh.plant', $plant) : "";
        //!empty($sku_desc) ? $this->db->like('skd.sku_description', $sku_desc) : "";
        $this->db->group_by('data_manufacture_month_year, sku_code ,sbomb_exists, `md`.`plant`');
        !empty($sbomb_exists) ? $this->db->group_by('sbomb_exists') : "";
        (!empty($offset) || $offset == 0) ? $this->db->limit($perpage, $offset) : "";
        $query0 = $this->db->get();
        //echo $this->db->last_query();
        return $query0;
    }

}
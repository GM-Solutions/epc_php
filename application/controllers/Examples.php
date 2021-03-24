<?php

/* http://preview.codecanyon.net/item/installation-wizard-codeigniter/full_screen_preview/19992822?_ga=2.88811027.982990717.1542707703-2095600627.1537615285 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Examples extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library("session");
        $this->load->helper('url');
        $this->load->library('breadcrumbs'); /*for breadcrumbs*/
        
        $this->load->library('S3');/* for AWS S3 bucket Transactions */

    }
    
    
    
    public function breadcrumb() {
        // add breadcrumbs
        $this->breadcrumbs->push('Section', '/section',FALSE);
        $this->breadcrumbs->push('Page', '/section/page',FALSE);

        // unshift crumb
        $this->breadcrumbs->unshift('Home', '/',TRUE);

        // output
        echo  $this->breadcrumbs->show();
    }
    public function s3buckets() {
        
        $aws = $this->config->item('aws'); /*load S3 configuration */
        $bucketName = $aws['bucket_name'];
        $s3 = new S3($aws['awsAccessKey'], $aws['awsSecretKey']);
        if(!empty($_FILES) && $_FILES['name_of_input_file_controler']['error']== 0){
                
                $array = explode('.', $_FILES['name_of_input_file_controler']['name']);
                $extension = end($array);
                
                $uniquesavename=  str_replace(" ", "_",$array[0]);
                $doc_name = $uniquesavename."_".rand(100,9999).'.'.$extension;  
                
                $doc_url = "";
                
                $file_url = 'file_path/'.$doc_name;
                $type = S3::$extenstion_type;
            	if ($s3->putObjectFile($_FILES['name_of_input_file_controler']['tmp_name'], $bucketName, $file_url, S3::ACL_PUBLIC_READ,array(),(string)$type[$extension])) {
                    $doc_url = $bucketName."/".$file_url;
                    /*perform action*/                   
                }                               
            
            }
    }

}
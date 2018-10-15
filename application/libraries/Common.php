<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of REST_Common
 *
 * @author pavaningalkar
 */
class Common {
    public function __construct()
    {
//    parent::__construct();
        
    }
    public static function sendSMS($sms_dtl =  array()) {
        /*get country*/
        $CI = & get_instance();
        $country = !empty($CI->post('country')) ? $CI->post('country') : "india";        
        $all_group = $CI->config->item('db_group');
        $country = (array_key_exists($country, $all_group)) ? $country : "india";

        $sms_setting = $CI->config->item('sms');
        
        switch ($country) {
            case "india":
                $parameters = "aid=".$sms_setting[$country]['aid']."&pin=".$sms_setting[$country]['pin']."&signature=".$sms_setting[$country]['signature']."&mnumber=".$sms_dtl['mobile_no']."&message=".$sms_dtl['message'];
		$apiurl = $sms_setting[$country]['message_url'];
		$ch = curl_init($apiurl);		

		curl_setopt($ch, CURLOPT_POST,0);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$parameters);		

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_HEADER,0);
		// DO NOT RETURN HTTP HEADERS 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
		// RETURN THE CONTENTS OF THE CALL
		$return_val = curl_exec($ch);
                
                break;
            
            case "uganda":


                break;
        }
        
    }
    public static function get_country(){
        $CI = & get_instance();
        $country = !empty($CI->post('country')) ? $CI->post('country') : "india";        
        $all_group = $CI->config->item('db_group');
        $country = (array_key_exists($country, $all_group)) ? $country : "india";
        return $country;
    }
    public static function django_pwd_verify($dbString,$password) {
        
        $pieces = explode("$", $dbString);
        if(count($pieces) == 0){
            return FALSE;
        }
        $iterations = $pieces[1];
        $salt = $pieces[2];
        $old_hash = $pieces[3];

        $hash = hash_pbkdf2("SHA256", $password, $salt, $iterations, 0, true);
        $hash = base64_encode($hash);
        if ($hash == $old_hash) {
           // login ok.
           return true;
        }
        else {
           //login fail       
           return false; 
        }
    }
    public static function django_pwd_generate($raw_password,$salt) {
        $iterations = 12000;
        $salt =  $salt;
        $hash = hash_pbkdf2("SHA256", $raw_password, $salt, $iterations, 0, true);
        $hash = base64_encode($hash);
        return "pbkdf2_sha256$".$iterations."$".$salt."$".$hash;
//        $password_hash = shell_exec('python /var/www/qa.gladminds.local/public_html/epc/django_password_hash.py ' . $raw_password . ' ' . $salt);
    }
    public static function send_email($email_dtl){
        
        $CI = & get_instance();
        $configration = $CI->config->item('email1');        
        $CI->load->library('email');
        $config['charset'] = 'iso-8859-1';
        $config['wordwrap'] = TRUE;
        $config['protocol'] = "smtp";
        $config['smtp_host'] = $configration['smtp_host'];
        $config['smtp_port'] = 465;
        $config['strictSSL'] = TRUE;
        $config['smtp_user'] = $configration['smtp_user'];
        $config['smtp_pass'] = "7709266996p#";
        $config['charset'] = "utf-8";
        $config['mailtype'] = "html";
        $config['newline'] = "\r\n";
        $CI->email->initialize($config);
        $CI->email->from($configration['smtp_user'], $configration['smtp_user']); 
        $log_email = array();
        $i=$j=0;
        
        foreach ($email_dtl as $key => $value) {
                   
            $CI->email->to($value['to']);
            $CI->email->subject($value['subject']);
            $CI->email->message($value['message']);
            $CI->email->set_newline("\r\n");
            
            if ($CI->email->send()) {
                $log_email[$i]['to']=$value['to'];
                $log_email[$i]['from']=$configration['email_from'];
                $log_email[$i]['message']=$value['message'];
                $log_email[$i]['subject']=$value['subject'];
                $log_email[$i]['status']='sent';
                $i++;
            }else{
                $log_email[$j]['to']=$value['to'];
                $log_email[$j]['from']=$configration['email_from'];
                $log_email[$j]['message']=$value['message'];
                $log_email[$j]['subject']=$value['subject'];
                $log_email[$j]['status']='nosent';
                $j++;
            }
        }
        return $log_email;
    }
    public static function generate_booking_no($index,$digit=6,$country_code = 'IN'){
//            $country_code = 'IN';
            $date = date("Y-m-d");
//            $booking_number = date('Ymd', strtotime($date)) . str_pad($index, $digit, 0, STR_PAD_LEFT);
		$booking_number = date('Ymd', strtotime($date)) . $index;
            return $country_code . $booking_number ;          
    }
}

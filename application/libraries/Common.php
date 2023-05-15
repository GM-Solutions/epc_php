<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require BASEPATH . '../application/libraries/mailer/vendor/autoload.php';
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
class Common
{
    public function __construct()
    {
        //    parent::__construct();

    }
    public static function sendSMS($sms_dtl =  array())
    {
        /*get country*/
        $CI = &get_instance();
        $country = !empty($CI->post('country')) ? $CI->post('country') : "india";
        $all_group = $CI->config->item('db_group');
        $country = (array_key_exists($country, $all_group)) ? $country : "india";

        $sms_setting = $CI->config->item('sms');

        switch ($country) {
            case "india":
                $parameters = "aid=" . $sms_setting[$country]['aid'] . "&pin=" . $sms_setting[$country]['pin'] . "&signature=" . $sms_setting[$country]['signature'] . "&mnumber=" . $sms_dtl['mobile_no'] . "&message=" . $sms_dtl['message'];
                $apiurl = $sms_setting[$country]['message_url'];
                $ch = curl_init($apiurl);

                curl_setopt($ch, CURLOPT_POST, 0);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);

                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                // DO NOT RETURN HTTP HEADERS 
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                // RETURN THE CONTENTS OF THE CALL
                $return_val = curl_exec($ch);

                break;

            case "uganda":


                break;
        }
    }
    public static function get_country()
    {
        $CI = &get_instance();
        $country = !empty($CI->post('country')) ? $CI->post('country') : "india";
        $all_group = $CI->config->item('db_group');
        $country = (array_key_exists($country, $all_group)) ? $country : "india";
        return $country;
    }
    public static function django_pwd_verify($dbString, $password)
    {

        $pieces = explode("$", $dbString);
        if (count($pieces) == 0) {
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
        } else {
            //login fail       
            return false;
        }
    }

    public static function token_pwd_verify($dbString, $password)
    {
        return true;
    }

    public static function django_pwd_generate($raw_password, $salt)
    {
        $iterations = 12000;
        $salt =  $salt;
        $hash = hash_pbkdf2("SHA256", $raw_password, $salt, $iterations, 0, true);
        $hash = base64_encode($hash);
        return "pbkdf2_sha256$" . $iterations . "$" . $salt . "$" . $hash;
        //        $password_hash = shell_exec('python /var/www/qa.gladminds.local/public_html/epc/django_password_hash.py ' . $raw_password . ' ' . $salt);
    }
    public static function send_email($email_dtl)
    {

        $log_email =  array();
        $i = $j = 0;
        $CI = &get_instance();
        $configration = $CI->config->item('email');
        $mail = new PHPMailer(true);
        try {

            foreach ($email_dtl as $key => $value) {
                // Specify the SMTP settings.
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->setFrom($configration['email_from'], $configration['email_from_name']);
                $mail->Username = $configration['smtp_user'];
                $mail->Password = $configration['smtp_pass'];
                $mail->Host = $configration['smtp_host'];
                $mail->Port = 587;
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = 'tls';
                //               $mail->addCustomHeader('X-SES-CONFIGURATION-SET', $configurationSet);
                // Specify the message recipients.
                $mail->addAddress($value['to']);
                $mail->addBCC('chaitanya@gladminds.co');
                // You can also add CC, BCC, and additional To recipients here.
                // Specify the content of the message.
                $mail->isHTML(true);
                $mail->Subject = $value['subject'];
                $mail->Body = $value['message'];
                // $mail->AltBody = $bodyText;

                if ($mail->Send()) {
                    $log_email[$i]['to'] = $value['to'];
                    $log_email[$i]['from'] = $configration['email_from'];
                    $log_email[$i]['message'] = $value['message'];
                    $log_email[$i]['subject'] = $value['subject'];
                    $log_email[$i]['status'] = 'sent';
                    $i++;
                } else {
                    $log_email[$j]['to'] = $value['to'];
                    $log_email[$j]['from'] = $configration['email_from'];
                    $log_email[$j]['message'] = $value['message'];
                    $log_email[$j]['subject'] = $value['subject'];
                    $log_email[$j]['status'] = 'nosent';
                    $j++;
                }
            }

            //            echo "Email sent!", PHP_EOL;
        } catch (phpmailerException $e) {
            // echo "An error occurred. {$e->errorMessage()}", PHP_EOL; //Catch errors from PHPMailer.
        } catch (Exception $e) {
            // echo "Email not sent. {$mail->ErrorInfo}", PHP_EOL; //Catch errors from Amazon SES.
        }
        return $log_email;
    }
    public static function transactionSMS($sms_dtl =  array())
    {
        //print_r($sms_dtl);
        /*get country*/
        $CI = &get_instance();
        $country = "india";
        $sms_setting = $CI->config->item('transactionsms');
        switch ($country) {
            case "india":
                $sms_dtl['mobile_no'] = strlen($sms_dtl['mobile_no']) == 10 ? "91" . $sms_dtl['mobile_no'] : $sms_dtl['mobile_no'];
                $parameters = "key=" . $sms_setting[$country]['key'] . "&encrpt=0&dest=" . $sms_dtl['mobile_no'] . "&send=" . $sms_setting[$country]['send'] . "&text=" . urlencode($sms_dtl['message']);
                $apiurl = $sms_setting[$country]['message_url'] . $parameters;
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, $apiurl);
                curl_setopt($ch, CURLOPT_POST, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $return = curl_exec($ch);
                curl_close($ch);
                break;

            case "uganda":


                break;
        }
    }
    public static function generate_booking_no($index, $digit = 6)
    {
        $date = date("Y-m-d");
        $country_code = 'IN';
        //            $booking_number = date('Ymd', strtotime($date)) . str_pad($index, $digit, 0, STR_PAD_LEFT);
        $booking_number = date('Ymd', strtotime($date)) . $index;
        return $country_code . $booking_number;
    }
}

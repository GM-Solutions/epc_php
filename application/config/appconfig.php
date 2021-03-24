<?php

/* Site Configuration 
 */
$setting_type = "production"; //  $setting_type=  local  /dev / qa/ production 
// maintenance mode
$config['maintenance_mode'] = FALSE;
$config['maintenance_allowed_ip'] = '127.0.0.1';

/*download DB setting */
$config['download_db'] =  TRUE;

if ($setting_type == "production") {
    $config['catlog']['url'] = 'http://epc.gladminds.co';
} else if ($setting_type == "qa") {
    $config['catlog']['url'] = 'http://qaepc.gladminds.co';
}

/*AWS Configuration*/

if ($setting_type == "production") {
    $config['aws']['host'] = 'http://gladminds-connect.s3.amazonaws.com';
    $config['aws']['awsAccessKey'] = 'AKIAJB3GCNQLLPCPYV6A';
    $config['aws']['awsSecretKey'] = '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';
    $config['aws']['bucket_agreement'] = 'gladminds-connect';
    $config['aws']['dir'] = 'prod/epc/';
} else if ($setting_type == "local") {
    $config['aws']['host'] = 'http://gladminds-connect.s3.amazonaws.com';
    $config['aws']['awsAccessKey'] = 'AKIAJB3GCNQLLPCPYV6A';
    $config['aws']['awsSecretKey'] = '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';
    $config['aws']['dir'] = 'prod/epc/';
    $config['aws']['bucket_agreement'] = 'gladminds-connect';
} else {
    $config['aws']['host'] = 'http://gladminds-connect.s3.amazonaws.com';
    $config['aws']['awsAccessKey'] = 'AKIAJB3GCNQLLPCPYV6A';
    $config['aws']['awsSecretKey'] = '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';
    $config['aws']['dir'] = 'qa/epc/';
    $config['aws']['bucket_agreement'] = 'gladminds-connect';
}
/*email Configuration*/
$config['email']['smtp_user']='';
$config['email']['smtp_pass']='';
$config['email']['email_from']='';
$config['email']['email_from_name']='';
$config['email']['smtp_host']='';
$config['email']['smtp_port']='';

$config['email1']['smtp_user']='info-bajaj@gladminds.co';
$config['email1']['smtp_pass']='bajaj@epc';
$config['email1']['email_from']='info-bajaj@gladminds.co';
$config['email1']['email_from_name']='info-bajaj@gladminds.co';
$config['email1']['smtp_host']='ssl://smtp.googlemail.com';
//$config['email']['smtp_host']='smtpout.secureserver.net';
$config['email1']['smtp_port']="587";

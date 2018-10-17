<?php

$setting_type = "qa"; //  $setting_type=  local  /dev / qa/ production 

$config['wsdlconf']['ordersend'] = "ALL"; // SFA / CDMS / ALL
$config['wsdlconf']['username'] = "epc"; 
$config['wsdlconf']['password'] = "epc"; 

$config['wsdlconf']['log_path'] = $_SERVER['DOCUMENT_ROOT'].'/application/logs/wsdl/'; 


$config['wsdl_cdms']['UserName'] = "balcrtest.espl"; 
$config['wsdl_cdms']['Password'] = "123"; 


/*KTM WSDL COnf*/
$config['ktmwsdlconf']['username'] = "bajaj"; 
$config['ktmwsdlconf']['password'] = "gladminds@321"; 


/* countries list */
$config['countries'] = array(
    'india' => array('key' => 'india', 'lable' => 'India (+91)', 'mobile_validation' => 10, 'flag' => 'india.png', 'base_url' => 'india', 'code' => '91'),
    'uganda' => array('key' => 'uganda', 'lable' => 'Uganda (+256)', 'mobile_validation' => 9, 'flag' => 'uganda.png', 'base_url' => 'uganda', 'code' => '256'),
    'kenya' => array('key' => 'kenya', 'lable' => 'Kenya (+254)', 'mobile_validation' => 9, 'flag' => 'kenya.png', 'base_url' => 'kenya', 'code' => '254'),
);

/* list of group available */
$config['db_group']['india'] = 'default';
$config['db_group']['uganda'] = 'bajajib';
$config['db_group']['kenya'] = 'kenya';

if($setting_type == "qa"){
$config['db_group']['bajaj_db'] = "bajaj_qa";
}else if($setting_type == "production"){
$config['db_group']['bajaj_db'] = "bajaj_prod";
}else{
$config['db_group']['bajaj_db'] = "bajaj_qa";
}

/*country role menu config*/
$config['role_menu']['india'][0] = array('key'=>'asc','value'=>'Authorise Service Center','username'=>TRUE);
$config['role_menu']['india'][1] = array('key'=>'dealer','value'=>'Dealers','username'=>TRUE);
$config['role_menu']['india'][2] = array('key'=>'service_advisor','value'=>'Service Advisors','username'=>FALSE);

$config['role_menu']['uganda'][0] = array('key'=>'main_country_dealer','value'=>'Main Country Dealer','username'=>TRUE);
$config['role_menu']['uganda'][1] = array('key'=>'dealer','value'=>'Dealers','username'=>TRUE);
$config['role_menu']['uganda'][2] = array('key'=>'service_advisor','value'=>'Service Advisors','username'=>FALSE);
$config['role_menu']['uganda'][3] = array('key'=>'sales_executive','value'=>'Sales Executive','username'=>FALSE);

$config['role_menu']['kenya'][0] = array('key'=>'main_country_dealer','value'=>'Main Country Dealer','username'=>TRUE);
$config['role_menu']['kenya'][1] = array('key'=>'dealer','value'=>'Dealers','username'=>TRUE);
$config['role_menu']['kenya'][2] = array('key'=>'service_advisor','value'=>'Service Advisors','username'=>FALSE);
$config['role_menu']['kenya'][3] = array('key'=>'sales_executive','value'=>'Sales Executive','username'=>FALSE);


/* sms configuration */
$config['sms']['india']['aid'] = '640811';
$config['sms']['india']['pin'] = 'ba124';
$config['sms']['india']['message_url'] = 'http://httpapi.zone:7501/failsafe/HttpLink';
$config['sms']['india']['signature'] = 'BJAJFS';



/***********************/
$config['email1']['smtp_user']='';
$config['email1']['smtp_pass']='';
$config['email1']['email_from']='';
$config['email1']['email_from_name']='';
$config['email1']['smtp_host']='ssl://smtp.googlemail.com';
//$config['email']['smtp_host']='smtpout.secureserver.net';
$config['email1']['smtp_port']="587";

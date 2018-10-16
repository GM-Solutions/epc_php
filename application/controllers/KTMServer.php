<?php

/* https://gist.github.com/helieting/2880574 */
require 'KTMSoapLogic.php';
/**
 * Description of KTM Soap  Server
 *
 * @author pavaningalkar
 */
//put your code here
class KTMServer extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library("nuSoap_lib"); //load the library here  
        
        ob_end_clean();
    }

    public function index() {
        $op = array();
        $this->nusoap_server = new soap_server();
        $this->nusoap_server->soap_defencoding = 'UTF-8';
        $this->nusoap_server->decode_utf8 = FALSE;
        $this->nusoap_server->encode_utf8 = TRUE;

        $this->nusoap_server->configureWSDL("BajajInterface", base_url() . "KTMServer?wsdl", base_url() . "KTMServer");
        //WSDL Schema
        $this->nusoap_server->wsdl->addComplexType(
                'OrderArray', 'complexType', 'struct', 'all', '', array(
                'CUST_MOBILE' => array('name' => 'CUST_MOBILE', 'type' => 'xsd:string','minOccurs' => '0'),
                'VEH_SL_DLR' => array('name' => 'VEH_SL_DLR', 'type' => 'xsd:string','minOccurs' => '0'),
                'ENGINE' => array('name' => 'ENGINE', 'type' => 'xsd:string','minOccurs' => '0'),
                'CITY' => array('name' => 'CITY', 'type' => 'xsd:string','minOccurs' => '0'),
                'PIN_CODE' => array('name' => 'PIN_CODE', 'type' => 'xsd:string','minOccurs' => '0'),
                'TIMESTAMP' => array('name' => 'TIMESTAMP', 'type' => 'xsd:string','minOccurs' => '0'),
                'VEH_REG_NO' => array('name' => 'VEH_REG_NO', 'type' => 'xsd:string','minOccurs' => '0'),
                'STATE' => array('name' => 'STATE', 'type' => 'xsd:string','minOccurs' => '0'),
                'CHASSIS' => array('name' => 'CHASSIS', 'type' => 'xsd:string','minOccurs' => '0'),
                'VEH_SL_DT' => array('name' => 'VEH_SL_DT', 'type' => 'xsd:string','minOccurs' => '0'),
                'CUSTOMER_ID' => array('name' => 'CUSTOMER_ID', 'type' => 'xsd:string','minOccurs' => '0'),
                'CUSTOMER_NAME' => array('name' => 'CUSTOMER_NAME', 'type' => 'xsd:string','minOccurs' => '0')
                    )
        );

        
        $this->nusoap_server->wsdl->addComplexType('Credentials', 'complexType', 'struct', 'all', '', array(
            'username' => array('name' => 'username', 'type' => 'xsd:string'),
            'password' => array('name' => 'password', 'type' => 'xsd:string')
        ));
        
        /* return parameters */
        $this->nusoap_server->wsdl->addComplexType('ResponseDetails', 'complexType', 'struct', 'all', '', array(
            'error' => array('name' => 'error', 'type' => 'xsd:string'),
            'message' => array('name' => 'message', 'type' => 'xsd:string'),
            'ticket_no' => array('name' => 'ticket_no', 'type' => 'xsd:string'),
            'status' => array('name' => 'status', 'type' => 'xsd:boolean')
        ));

        $this->nusoap_server->wsdl->addComplexType('PurchaseOrder', 'complexType', 'array', 'sequence', '', array('OrderDetail' => array('name' => 'OrderDetail', 'type' => 'tns:OrderArray', 'minOccurs' => '0', 'maxOccurs' => 'unbounded')));
        /* register precess */
        $this->nusoap_server->register(
                'postProductPurchase',
                array('Auth'=>'tns:Credentials','PurchaseOrderList' => 'tns:PurchaseOrder'),
                array('return' => 'tns:ResponseDetails'),
                'urn:' . base_url() . 'KTMServer',
                'urn:' . base_url() . 'KTMServer#postProductPurchase', 'rpc', 
                'encoded', 'Purchase feed from cdms to gladminds');

        function postProductPurchase($Credentials,$PurchaseOrder) {
            $logic = new KTMSoapLogic();
           return $logic->postProductPurchase($Credentials,$PurchaseOrder);
        }

        $this->nusoap_server->service(file_get_contents("php://input")); //shows the standard info about service
    }

}

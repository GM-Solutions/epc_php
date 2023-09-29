<?php

use PHPMailer\PHPMailer\PHPMailer;

require APPPATH . 'libraries/REST_Controller.php';
//defined('BASEPATH') or exit('No direct script access allowed');

class User extends REST_Controller
{

    //put your code here
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model("Common_model");
        $this->load->database();
        $this->load->helper('rand_helper');
        $this->load->library('S3');
    }

    public function login_post()
    {

        $email = $this->post('email');
        $password = $this->post('password');
        if (empty($email) || empty($password)) {
            $dtl['status'] = FALSE;
            $dtl['message'] = "Please fill all details properly";
            $this->response($dtl, REST_Controller::HTTP_OK);
        }

        $dtl = array();
        $this->db->select('au.id AS user_id,
            au.first_name,
            au.last_name,
            au.password,
            au.username,
            role.name AS role_name,
            role.id AS role_id,
            bv.name AS vertical_name,
            bv.id AS vertical_id');
        $this->db->from('auth_user AS au');
        $this->db->join('gm_epcuserprofileroles AS up', 'up.userprofile_id = au.id', 'left');
        $this->db->join('gm_epcroles AS role', 'role.id = up.role_id', 'left');
        $this->db->join('gm_brandvertical AS bv', 'bv.id = role.vertical_id', 'left');

        $this->db->where('au.username', $email);
        $this->db->or_where('au.email', $email);
        $query = $this->db->get();
        $user_info = ($query->num_rows() > 0) ? $query->result_array() : FALSE;

        if ($query->num_rows() > 1) { /* more than one record */
            $dtl['status'] = FALSE;
            $dtl['message'] = "Sorry, Can not process your request please contact help desk.";
            $this->response($dtl, REST_Controller::HTTP_OK);
        }

        if ($user_info) {
            if (Common::django_pwd_verify($user_info[0]['password'], $password)) {
                $dtl['status'] = TRUE;
                foreach ($user_info as $key => $value) {
                    $dtl['user_id'] = $value['user_id'];
                    $dtl['username'] = $value['username'];
                    $dtl['first_name'] = $value['first_name'];
                    $dtl['last_name'] = $value['last_name'];
                    $dtl['role'][$key]['role_name'] = $value['role_name'];
                    $dtl['role'][$key]['role_id'] = $value['role_id'];
                    $dtl['role'][$key]['vertical_name'] = $value['vertical_name'];
                    $dtl['role'][$key]['vertical_id'] = $value['vertical_id'];

                    if ($value['vertical_id'] == MOTERCYCLE && $value['role_name'] == "Distributor") {
                        $dtl1 = $this->Common_model->select_info('gm_sfa_mc_distributor', array('user_id' => $value['user_id']));
                        $dtl['role'][$key]['distributor_id'] = $dtl1[0]['id'];
                    }
                    if ($value['vertical_id'] == MOTERCYCLE && $value['role_name'] == "Dealer") {
                        $dtl1 = $this->Common_model->select_info('gm_mc_dealer', array('user_id' => $value['user_id']));
                        $dtl['role'][$key]['dealer_id'] = $dtl1[0]['id'];
                    }

                    if ($value['role_name'] == "user") {
                        $order_count = $this->Common_model->select_info('gm_part_order_cart', array('user_id' => $value['user_id'], 'active' => 1));
                        $dtl['cart_count'] = ($order_count == FALSE) ? 0 : count($order_count);
                    }
                }
                $this->response($dtl, REST_Controller::HTTP_OK);
            } else {
                $dtl['status'] = FALSE;
                $dtl['message'] = "Password dose not match";
                $this->response($dtl, REST_Controller::HTTP_OK);
            }
        } else {
            $dtl['status'] = FALSE;
            $dtl['message'] = "User details not found, Please check  your details";
            $this->response($dtl, REST_Controller::HTTP_OK);
        }
    }

    public function forget_password_check_post()
    {
        /* get email id */
        $email = $this->post('email');
        $type = $this->post('type'); /* check for sent verification ,  verify  for verify otp and set password */
        if (empty($email) || empty($type)) {
            $dtl['status'] = FALSE;
            $dtl['message'] = "Please fill all details properly";
            $this->response($dtl, REST_Controller::HTTP_OK);
        }


        $this->db->select('au.id AS user_id, au.email,group.name AS group_name,group.id AS group_id,au.first_name,au.last_name,au.password,au.username');
        $this->db->from('auth_user AS au');
        $this->db->join('auth_user_groups AS g', 'au.id=g.user_id', 'left');
        $this->db->join('auth_group AS group', 'group.id=g.group_id', 'left');
        $this->db->where('au.username', $email);
        $this->db->or_where('au.email', $email);
        $query = $this->db->get();

        $user_info = ($query->num_rows() > 0) ? $query->result_array() : FALSE;
        //$user_info[0]['email'] = "";

        if ($query->num_rows() > 1) { /* more than one record */
            $dtl['status'] = FALSE;
            $dtl['message'] = "Sorry, Can not process your request please contact help desk.";
            $this->response($dtl, REST_Controller::HTTP_OK);
        }

        if ($user_info) {
            if ($type == "set_pwd") {
                /* check for code authenticity */

                $dtl = $this->set_password($user_info);
                $this->response($dtl, REST_Controller::HTTP_OK);
                die;
            }
            /* send email */
            $code = generateRandomString(6);
            $template_message = "Dear User<br/> You have request for Password Change.<br/> Please Use Code " . $code . " to reset password<br/>";
            $email_dtl[0]['to'] = $user_info[0]['email']; //"pvningalkar@gmail.com";
            $email_dtl[0]['message'] = $template_message;
            $email_dtl[0]['subject'] = "Verification Code for password reset";
            $log_responce = Common::send_email($email_dtl);
            if ($log_responce[0]['status'] == "sent") {
                $dtl['status'] = TRUE;
                $dtl['message'] = "We have sent you verification code.";
                $dtl['code'] = $code;
                /* insert code in  gm_user_password_reset */
                $now = date('Y-m-d H:i:s');
                $this->Common_model->insert_info('gm_user_password_reset', array('user_id' => $user_info[0]['user_id'], 'uuid' => $code, 'created_date' => $now, 'requested_date' => $now));
            } else {
                $dtl['status'] = FALSE;
                $dtl['message'] = "Something went wrong";
            }
        } else {
            $dtl['status'] = FALSE;
            $dtl['message'] = "No User Found";
        }
        $this->response($dtl, REST_Controller::HTTP_OK);
    }

    private function set_password($user_info)
    {
        $randomString = generateRandomString(12);
        $new_password = $this->post('new_password');
        if (empty($new_password)) {
            $dtl['status'] = FALSE;
            $dtl['message'] = "Please give new password";
            return $dtl;
        }
        $salt = $randomString;
        $new_hash_pwd = Common::django_pwd_generate($new_password, $salt);

        /* update password */

        $up_dtl['password'] = $new_hash_pwd;
        $up_cond['id'] = $user_info[0]['user_id'];
        if ($this->Common_model->update_info("auth_user", $up_dtl, $up_cond)) {
            $dtl['status'] = TRUE;
            $dtl['message'] = "Password update sucessfully";
            return $dtl;
        }
    }

    public function add_shop_post()
    {

        $mytoken = $this->Common_model->validate_token();
        if ($mytoken['code'] !== 200) {
            http_response_code($mytoken['code']);
            echo json_encode($mytoken);
            die();
        }

        $user_id = $this->post('user_id');
        $api_type = $this->post('api_type');
        $role_name = $this->post('role');
        $role_id = $this->post('role_id'); /* 15 for Distributor */
        $default_address = $this->post('default_address');

        if (!empty($role_name) && ($role_name == "Distributor" || $role_name == "Dealer")) {
            /* continue */
        } else {
            $op['status'] = FALSE;
            $op['message'] = "Sorry No details available";
            $this->response($op, REST_Controller::HTTP_OK);
            return TRUE;
        }

        $add_address = $select_cond = array();

        if (!empty($this->post('user_id')))
            $select_cond['user_id'] = $add_address['user_id'] = $this->post('user_id');

        if (!empty($this->post('address')))
            $add_address['address'] = $this->post('address');

        if (!empty($this->post('city')))
            $add_address['city'] = $this->post('city');

        if (!empty($this->post('state')))
            $add_address['state'] = $this->post('state');


        if (!empty($this->post('pin_code')))
            $add_address['pin_code'] = $this->post('pin_code');

        if (!empty($this->post('latitude')))
            $add_address['latitude'] = $this->post('latitude');

        if (!empty($this->post('longitude')))
            $add_address['longitude'] = $this->post('longitude');

        if (!empty($this->post('active')))
            $add_address['active'] = $this->post('active');


        if ($api_type == "addnew") {

            /* update all address as not default */
            $add_address['active'] = 1;
            $this->Common_model->insert_info('gm_epc_shop_details', $add_address);
        } elseif ($api_type == "update") {
            $up_cond['id'] = $this->post('address_id');
            $this->Common_model->update_info('gm_epc_shop_details', $add_address, $up_cond);
        }

        /* send address details */
        $op = $op1 = array();
        $select_cond['active'] = 1;
        $address_dtl = $this->Common_model->select_info('gm_epc_shop_details', $select_cond);
        if ($address_dtl) {
            $op['status'] = TRUE;
            foreach ($address_dtl as $key => $val) {

                $op1[$key]['address_id'] = $val['id'];
                $op1[$key]['address'] = $val['address'];
                $op1[$key]['city'] = $val['city'];
                $op1[$key]['state'] = $val['state'];
                $op1[$key]['pin_code'] = $val['pin_code'];
                $op1[$key]['latitude'] = $val['latitude'];
                $op1[$key]['longitude'] = $val['longitude'];
                $op1[$key]['user_id'] = $val['user_id'];
            }
            $op['address'] = $op1;
        } else {
            $op['status'] = FALSE;
            $op['message'] = "Sorry No Address available";
        }

        $this->response($op, REST_Controller::HTTP_OK);
    }

    public function user_registration_post()
    {
        $user_type = $this->post('user_type'); /* Dealer Distributor Member Customer */
        $vertical = $this->post('vertical'); /* Motorcycle ,Commercial Vehicle, Probiking, International Business */
        $otp = $this->post('otp');

        $first_name = $this->post('first_name');
        $last_name = $this->post('last_name');
        $email = $this->post('email');
        $password = $this->post('password');
        $phone_number = $this->post('phone_number');
        $op = array();
        if (empty($user_type) || empty($email) || empty($phone_number)) {
            $op['status'] = false;
            $op['message'] = "Please send manditory details";
            $this->response($op, REST_Controller::HTTP_OK);
            return TRUE;
        }
        /* check  for already registered User or not */
        $this->db->select('*');
        $this->db->from('auth_user AS au');
        $this->db->join('gm_userprofile AS up', 'au.id=up.user_id');
        $this->db->where('au.username', $email);
        $this->db->where('au.email', $email);
        $this->db->or_where('up.phone_number', $phone_number);

        $query = $this->db->get();
        $user_info = ($query->num_rows() > 0) ? $query->result_array() : FALSE;

        if ($user_info) {
            $msg = "";
            if ($user_info[0]['email'] == $email)
                $msg = "Email";
            if ($user_info[0]['username'] == $email)
                $msg = "Email";
            if ($user_info[0]['phone_number'] == $phone_number)
                $msg .= (!empty($msg) ? "," : "") . " Mobile number";
            $op['status'] = false;
            $op['message'] = trim($msg . " is already used");
            $this->response($op, REST_Controller::HTTP_OK);
            return TRUE;
        } else {
            $this->db->trans_start();
            if ($vertical == "Motorcycle" && ($user_type == "Dealer" || $user_type == "Distributor")) { /* Make Registration for Motorcycle */
                try {
                    /* for dealer registration */
                    $allow_global_users_type = array('Members', 'Users');
                    if (in_array($user_type, $allow_global_users_type))
                        throw new Exception('Invalid User type');
                    /* check for dealer code already register or not */
                    $dealer_data = ($user_type == "Dealer") ? $this->db->select('id,dealer_code,email,mobile1')->from('gm_mc_dealer')->like("dealer_code", trim($this->post('dealer_id')))->get()->row() : $this->db->select('id,sfa_mc_distributor_id AS dealer_code,email_bajaj AS email,mobile1,mobile2')->from('gm_sfa_mc_distributor')->like("sfa_mc_distributor_id", trim($this->post('dealer_id')))->get()->row();
                    if (!empty($dealer_data)) {
                        throw new Exception((int) $dealer_data->dealer_code . " And Mobile number: " . $dealer_data->mobile1);
                    } elseif (empty($otp)) {
                        /* Generate & send OTP on mobile number */
                        $this->generate_send_otp($phone_number, $email, trim($first_name . " " . $last_name));
                        /* Transaction completed */
                        $this->db->trans_complete();
                        $op['status'] = TRUE;
                        $op['message'] = "Please verify your mobile OTP";
                        $this->response($op, REST_Controller::HTTP_OK);
                        return TRUE;
                    }

                    /* verify OTP and allow access */
                    if (!empty($otp)) {
                        $otp_dtl = $this->db->select('token')->from('gm_otptoken')->where("phone_number", trim($phone_number))->order_by('created_date', 'desc')->get()->row();

                        if (empty($otp_dtl))
                            throw new Exception('No OTP found for ' . $phone_number);

                        if ($otp_dtl->token == $otp) {
                            /* allow registration */
                            $registration_fields = $this->registration_detaiils($user_type, $vertical);

                            /* add data in auth */
                            $new_user_id = $this->Common_model->insert_info('auth_user', $registration_fields['auth_user']);
                            /* set user id */
                            $registration_fields['role_data']['userprofile_id'] = $new_user_id;
                            $registration_fields['userprofile']['user_id'] = $new_user_id;
                            $registration_fields['gladminds_master']['user_id'] = $new_user_id;
                            /* Add userprofile data */
                            $this->Common_model->insert_info('gm_userprofile', $registration_fields['userprofile']);
                            /* Add gm_epc_shop_details data */
                            $registration_fields['shop_dtl']['user_id'] = $new_user_id;
                            $this->Common_model->insert_info('gm_epc_shop_details', $registration_fields['shop_dtl']);
                            /* Create MC dealer profile */
                            if ($user_type == "Dealer")
                                $this->Common_model->insert_info('gm_mc_dealer', $registration_fields['gladminds_master']);
                            /* Create MC Distributor profile */
                            if ($user_type == "Distributor")
                                $this->Common_model->insert_info('gm_sfa_mc_distributor', $registration_fields['gladminds_master']);
                            /* create dealer role */
                            $this->Common_model->insert_info('gm_epcuserprofileroles', $registration_fields['role_data']);

                            $op['status'] = TRUE;
                            $op['message'] = "User register Successfully";
                        } else {
                            $op['status'] = FALSE;
                            $op['message'] = "OTP Not Match";
                        }
                    }
                } catch (Exception $ex) {
                    $op['status'] = FALSE;
                    $op['message'] = $ex->getMessage();
                }
                /* } elseif ($vertical == "Commercial Vehicle") {
                
            } elseif ($vertical == "Probiking") {
                
            } elseif ($vertical == "International Business") {*/
            } else { /* User Members */
                try {
                    $allow_global_users_type = array('Members', 'Users');
                    if (!in_array($user_type, $allow_global_users_type))
                        throw new Exception('Invalid User type');
                    if (empty($otp)) {
                        /* Generate & send OTP on mobile number */
                        $this->generate_send_otp($phone_number, $email, trim($first_name . " " . $last_name));
                        /* Transaction completed */
                        $op['status'] = TRUE;
                        $op['message'] = "Please verify your mobile OTP";
                    } else {
                        $otp_dtl = $this->db->select('token')->from('gm_otptoken')->where("phone_number", trim($phone_number))->order_by('created_date', 'desc')->get()->row();
                        if (empty($otp_dtl))
                            throw new Exception('No OTP found for ' . $phone_number);

                        if ($otp_dtl->token == $otp) {
                            /* allow registration */
                            $registration_fields = $this->registration_detaiils($user_type, $vertical);
                            /* add data in auth */
                            $new_user_id = $this->Common_model->insert_info('auth_user', $registration_fields['auth_user']);
                            /* set user id */
                            $registration_fields['role_data']['userprofile_id'] = $new_user_id;
                            $registration_fields['userprofile']['user_id'] = $new_user_id;
                            /* Add userprofile data */
                            $this->Common_model->insert_info('gm_userprofile', $registration_fields['userprofile']);
                            /* create role */
                            $this->Common_model->insert_info('gm_epcuserprofileroles', $registration_fields['role_data']);
                            $op['status'] = TRUE;
                            $op['message'] = "User register Successfully";
                        } else {
                            $op['status'] = FALSE;
                            $op['message'] = "OTP Not Match";
                        }
                    }
                } catch (Exception $ex) {
                    $op['status'] = False;
                    $op['message'] = $ex->getMessage();
                }
            }
            $this->db->trans_complete();
        }
        $this->response($op, REST_Controller::HTTP_OK);
    }

    private function generate_send_otp($phone_number, $email, $user_name)
    {
        $otp_no = rand(100000, 999999);

        $otp_data['token'] = $otp_no;
        $otp_data['phone_number'] = $phone_number;
        $otp_data['email'] = $email;
        $otp_data['created_date'] = date('Y-m-d H:i:s');
        $otp_data['modified_date'] = date('Y-m-d H:i:s');
        $otp_data['request_date'] = date('Y-m-d H:i:s');

        $msg = "Dear Mr {user_name} <br/>Use OTP: {otp} to complete your registration process.<br/>Regards<br/><br/>Bajaj Auto<br/><br/><br/><br/>Powered By Gladminds";
        $email_dtl[0]['subject'] = "Verify your account";
        $email_dtl[0]['message'] = str_replace(array("{user_name}", "{otp}"), array($user_name, $otp_no), $msg);
        $email_dtl[0]['to'] = $email;
        $sms_msg = str_replace(array("{otp}"), array($otp_no), "Dear EPC User, your OTP is {otp} , For any Support please email us on epcsupport@gladminds.co -Bajaj Auto Limited");
        /* send Email */
        Common::send_email($email_dtl);
        /*send SMS */
        Common::transactionSMS(array('mobile_no' => $otp_data['phone_number'], 'message' => $sms_msg));
        $this->Common_model->insert_info('gm_otptoken', $otp_data);
    }

    private function registration_detaiils($type, $vertical = NULL)
    {
        $methode_name = $this->router->fetch_method();
        $data = array();
        if ($vertical == "Motorcycle" && ($type == "Distributor" || $type == "Dealer")) {
            if ($type == 'Dealer' || $type == 'Distributor') {
                if (empty($this->post('password')) && $methode_name != "update_profile")
                    throw new Exception('Password is empty ');
                if (!empty($this->post('address')))
                    $data['gladminds_master']['shop_address'] = $this->post('address');
                if (!empty($this->post('city')))
                    $data['gladminds_master']['city'] = $this->post('city');
                if (!empty($this->post('state')))
                    $data['gladminds_master']['state'] = $this->post('state');
                if (!empty($this->post('pincode')))
                    $data['gladminds_master']['pin_code'] = $this->post('pincode');
                if (!empty($this->post('latitude')))
                    $data['gladminds_master']['latitude'] = $this->post('latitude');
                if (!empty($this->post('longitude')))
                    $data['gladminds_master']['longitude'] = $this->post('longitude');

                /*gm_epc_shop_details*/
                if (!empty($this->post('address')))
                    $data['shop_dtl']['shop_address'] = $this->post('address');
                if (!empty($this->post('city')))
                    $data['shop_dtl']['city'] = $this->post('city');
                if (!empty($this->post('state')))
                    $data['shop_dtl']['state'] = $this->post('state');
                if (!empty($this->post('pincode')))
                    $data['shop_dtl']['pin_code'] = $this->post('pincode');
                if (!empty($this->post('latitude')))
                    $data['shop_dtl']['latitude'] = $this->post('latitude');
                if (!empty($this->post('longitude')))
                    $data['shop_dtl']['longitude'] = $this->post('longitude');
            }
            if ($type == 'Dealer') {
                if (!empty($this->post('dealer_id')))
                    $data['gladminds_master']['dealer_code'] = $this->post('dealer_id');
                if (!empty($this->post('email')))
                    $data['gladminds_master']['email'] = $this->post('email');
                if (!empty($this->post('first_name')))
                    $data['gladminds_master']['dealer_name'] = trim($this->post('first_name') . " " . $this->post('last_name'));
                if (!empty($this->post('phone_number')))
                    $data['gladminds_master']['mobile1'] = $this->post('phone_number');
                if (!empty($this->post('land_line')))
                    $data['gladminds_master']['mobile2'] = $this->post('land_line');
            }
            if ($type == 'Distributor') {
                if (!empty($this->post('dealer_id')))
                    $data['gladminds_master']['sfa_mc_distributor_id'] = $this->post('dealer_id');
                if (!empty($this->post('email')))
                    $data['gladminds_master']['email_bajaj'] = $this->post('email');
                if (!empty($this->post('first_name')))
                    $data['gladminds_master']['name'] = trim($this->post('first_name') . " " . $this->post('last_name'));
                if (!empty($this->post('phone_number')))
                    $data['gladminds_master']['mobile1'] = $this->post('phone_number');
                if (!empty($this->post('land_line')))
                    $data['gladminds_master']['phone_number'] = $this->post('land_line');
            }
        }
        /* auth data */
        if (!empty($this->post('email')))
            $data['auth_user']['username'] = $this->post('email');
        if (!empty($this->post('email')))
            $data['auth_user']['email'] = $this->post('email');
        if (!empty($this->post('first_name')))
            $data['auth_user']['first_name'] = $this->post('first_name');
        if (!empty($this->post('last_name')))
            $data['auth_user']['last_name'] = $this->post('last_name');

        $data['auth_user']['is_staff'] = 0;
        $data['auth_user']['is_active'] = 1;
        $data['auth_user']['date_joined'] = date('Y-m-d H:i:s');
        !empty($this->post('password')) ? $data['auth_user']['password'] = common::django_pwd_generate(trim($this->post('password')), mt_rand(10000, 99999) . time()) : "";
        /* user profile */
        $data['userprofile']['user_id'] = "";
        if (!empty($this->post('phone_number')))
            $data['userprofile']['phone_number'] = $this->post('phone_number');
        if (!empty($this->post('gender')))
            $data['userprofile']['gender'] = $this->post('gender');
        if (!empty($this->post('address')))
            $data['userprofile']['address'] = $this->post('address');
        if (!empty($this->post('state')))
            $data['userprofile']['state'] = $this->post('state');
        if (!empty($this->post('pincode')))
            $data['userprofile']['pincode'] = $this->post('pincode');
        if (!empty($this->post('city')))
            $data['userprofile']['city'] = $this->post('city');

        $data['userprofile']['created_date'] = date('Y-m-d H:i:s');
        $doc_url = "";
        $configration = $this->config->item('aws');
        /*upload media file */
        if (!empty($_FILES['photo']['name']) && $_FILES["photo"]['error'] == 0) {
            $array = explode('.', $_FILES['photo']['name']);
            $extension = end($array);
            $uniquesavename = str_replace(" ", "_", $array[0]);
            $doc_name = $uniquesavename . "_" . rand(10, 100) . '.' . $extension;
            $doc_url = $configration['dir'] . 'user/' . $doc_name;
            $type_ex = S3::$extenstion_type;
            $s3 = new S3($configration['awsAccessKey'], $configration['awsSecretKey']);
            if ($s3->putObjectFile($_FILES['photo']['tmp_name'], (string)$configration['bucket_agreement'], (string)$doc_url, S3::ACL_PUBLIC_READ, array(), (string)$type_ex[$extension])) {
                $doc_url =   $doc_url;
            }
        }
        if (!empty($doc_url))
            $data['userprofile']['image_url'] =  $doc_url;
        /* get user profile id */
        $this->db->select('role.id AS role_id');
        $this->db->from('gm_epcroles AS role');
        !empty($vertical) ? $this->db->join('gm_brandvertical AS bv', 'role.vertical_id = bv.id') : "";
        !empty($vertical) ? $this->db->where('bv.name', $vertical) : "";
        $this->db->where('role.name', $type);
        $query = $this->db->get();
        $user_info = ($query->num_rows() > 0) ? $query->row()->role_id : FALSE;
        $data['role_id'] = $user_info;

        if (!$user_info)
            throw new Exception('No Role Found with: ' . $type);

        $data['role_data']['modified_date'] = date('Y-m-d H:i:s');
        $data['role_data']['created_date'] = date('Y-m-d H:i:s');
        $data['role_data']['role_id'] = $user_info;
        $data['role_data']['userprofile_id'] = "";

        $data['status'] = TRUE;

        return $data;
    }

    public function select_sku_model_list_post()
    {

        $mytoken = $this->Common_model->validate_token();
        if ($mytoken['code'] !== 200) {
            http_response_code($mytoken['code']);
            echo json_encode($mytoken);
            die();
        }

        $vertical_id = $this->post('vertical_id');
        $sku_text = $this->post('sku_text');

        $data = $op = array();
        if (empty($vertical_id)) {
            $op['status'] = FALSE;
            $op['message'] = "Please send vertical Id";
            $this->response($op, REST_Controller::HTTP_OK);
            return TRUE;
        }
        $this->db->select('*,skd.id AS skudetails_id');
        $this->db->from('gm_skudetails AS skd');
        $this->db->join('gm_productbrands AS pb', 'skd.brand_id=pb.id');
        $this->db->where('pb.brand_vertical_id', $vertical_id);
        $this->db->like('skd.sku_description', $sku_text);
        $this->db->limit(10);
        $query = $this->db->get();
        $sku_dtl = ($query->num_rows() > 0) ? $query->result_array() : FALSE;

        if ($sku_dtl) {
            foreach ($sku_dtl as $key => $value) {
                $data[$key]['skudetails_id'] = $value['skudetails_id'];
                $data[$key]['sku_description'] = $value['sku_description'];
            }
            $op['status'] = TRUE;
            $op['skudetails'] = $data;
        } else {
            $op['status'] = FALSE;
        }
        $this->response($op, REST_Controller::HTTP_OK);
    }

    public function add_user_vehical_post()
    {
        $mytoken = $this->Common_model->validate_token();
        if ($mytoken['code'] !== 200) {
            http_response_code($mytoken['code']);
            echo json_encode($mytoken);
            die();
        }


        $api_type = $this->post('api_type'); /* 1=> selection of vin, 2=> Add information manually */
        $user_id = $this->post('user_id');
        $product_id = $this->post('product_id');

        $manufacturing_month = $this->post('manufacturing_month');
        $manufacturing_year = $this->post('manufacturing_year');
        $vertical_id = $this->post('vertical_id');
        $skudetails_id = $this->post('skudetails_id');

        $data_add = $op = array();
        /* vehical add */
        if ($api_type == "vin") {
            /* get information of product_id in  gm_manufacturingdata */

            $this->db->select('md.id AS manufacturing_id,sku.id AS sku_id');
            $this->db->from('gm_manufacturingdata AS md');
            $this->db->join('gm_bomheader AS bh', 'bh.id=md.bomheader_id', 'left');
            $this->db->join('gm_skudetails AS sku', 'sku.sku_code=bh.sku_code', 'left');
            $this->db->where('product_id', $product_id);
            $query = $this->db->get();
            $manufacturing_info = ($query->num_rows() > 0) ? $query->result_array() : FALSE;
            if ($manufacturing_info) {

                $data_add['manufacturingdata_id'] = $manufacturing_info[0]['manufacturing_id'];
                $data_add['user_id'] = $user_id;
                $data_add['product_id'] = $product_id;
                $data_add['vertical_id'] = $vertical_id;
                $data_add['skudetails_id'] = $manufacturing_info[0]['sku_id'];
                $data_add['active'] = 1;
            } else {
                $op['status'] = FALSE;
                $op['message'] = "Sorry No Vin Found";
                $this->response($op, REST_Controller::HTTP_OK);
                return TRUE;
            }
        } else if ($api_type == "no_vin") {
            $data_add['user_id'] = $user_id;
            $data_add['skudetails_id'] = $skudetails_id;
            $data_add['manufacturing_month'] = $manufacturing_month;
            $data_add['manufacturing_year'] = $manufacturing_year;
            $data_add['vertical_id'] = $vertical_id;
            if (empty($skudetails_id)) {
                $op['status'] = FALSE;
                $op['message'] = "Please select Models from  list";
                $this->response($op, REST_Controller::HTTP_OK);
                return TRUE;
            }
        }
        $this->db->trans_start();
        $data_add['create_date'] = date('Y-m-d H:i:s');
        $data_add['added_from'] = 1; /* FROM  APPLICATION */
        $this->Common_model->insert_info('gm_epc_user_vehical', $data_add);
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            $op['status'] = FALSE;
            $op['message'] = "something went wrong";
        } else {
            $op['status'] = TRUE;
        }
        $this->response($op, REST_Controller::HTTP_OK);
    }

    public function view_vehical_list_post()
    {

        $mytoken = $this->Common_model->validate_token();
        if ($mytoken['code'] !== 200) {
            http_response_code($mytoken['code']);
            echo json_encode($mytoken);
            die();
        }

        $img_base = "http://gladminds-connect.s3.amazonaws.com/";
        $user_id = $this->post('user_id');
        /* get the list off  all  register vehical address */
        $this->db->select('*,uv.id AS vehical_id');
        $this->db->from('gm_epc_user_vehical AS uv');
        $this->db->join('gm_skudetails AS sku', 'uv.skudetails_id = sku.id', 'left');
        $this->db->where('uv.user_id', $user_id);
        $query = $this->db->get();
        $vehical_list = ($query->num_rows() > 0) ? $query->result_array() : FALSE;
        $data = $op = array();
        if ($vehical_list) {
            foreach ($vehical_list as $key => $value) {
                $data[$key]['vehical_id'] = $value['vehical_id'];
                $data[$key]['product_id'] = $value['product_id'];
                $data[$key]['skudetails_id'] = $value['skudetails_id'];
                $data[$key]['model_name'] = $value['sku_description'];
                $data[$key]['image_url'] = $img_base . $value['image_url'];
                $data[$key]['manufacturing_month'] = $value['manufacturing_month'];
                $data[$key]['manufacturing_year'] = $value['manufacturing_year'];
                $data[$key]['manual_url'] = !empty($value['manual_url']) ? $value['manual_url'] : "https://www.globalbajaj.com/media/21022/dominar-400-om-mar17.pdf";
            }
            $op['data'] = $data;
            $op['status'] = TRUE;
        } else {
            $op['status'] = FALSE;
            $op['message'] = "No vehical vaailable";
        }
        $this->response($op, REST_Controller::HTTP_OK);
    }

    public function headquater_details_post()
    {

        $mytoken = $this->Common_model->validate_token();
        if ($mytoken['code'] !== 200) {
            http_response_code($mytoken['code']);
            echo json_encode($mytoken);
            die();
        }

        $user_id = $this->post('user_id');
        $role_name = $this->post('role');
        $op = array();
        if ($role_name == "Dealer" || $role_name == "Distributor") {
            $this->db->select('*');
            $this->db->from('gm_epc_shop_details');
            $this->db->where('active', 1);
            $this->db->where('address_type', 'hq');
            $query = $this->db->get();
            $shopdetails = ($query->num_rows() > 0) ? $query->result_array() : FALSE;
            if ($shopdetails) {
                $op['hq_shop_address_id'] = $shopdetails[0]['id'];
                $op['hq_name'] = "Head Quarter Pune";
                $op['hq_email'] = "customerservice@bajajauto.co.in";
                $op['hq_phone'] = "7219821111";
                $op['status'] = TRUE;
            } else {
                $op['status'] = FALSE;
                $op['status'] = "Sorry No HQ Availabble";
            }
        } else {
            $op['status'] = FALSE;
            $op['status'] = "Sorry No HQ Availabble";
        }
        $this->response($op, REST_Controller::HTTP_OK);
    }

    public function master_data_register_get()
    {
        /* Vertical list and roles */
        $this->db->select('*');
        $this->db->from('gm_brandvertical');
        $this->db->where_in('name', array('Motorcycle', 'Commercial Vehicle', 'Probiking', 'International Business'));
        $query = $this->db->get();
        $vertical_data = ($query->num_rows() > 0) ? $query->result_array() : FALSE;

        $dtl_data = $state_city_dtl = $state_city_raw = array();
        if ($vertical_data) {
            foreach ($vertical_data as $key => $value) {
                $dtl_data[$key]['id'] = $value['id'];
                $dtl_data[$key]['vertical_name'] = $value['name'];
            }
        }

        /* get city and state json */
        $this->db->select('city.id AS city_id,city.city,state.id AS  state_id,state.state_name');
        $this->db->from('gm_city AS city');

        $this->db->join('gm_state AS state', 'city.state_id =  state.id', 'left');
        $query01 = $this->db->get();
        $state_city = ($query01->num_rows() > 0) ? $query01->result_array() : FALSE;

        if ($state_city) {
            foreach ($state_city as $key => $value) {
                $state_city_raw[$value['state_id']]['state_id'] = $value['state_id'];
                $state_city_raw[$value['state_id']]['state_name'] = $value['state_name'];
                $state_city_raw[$value['state_id']]['city'][$value['city_id']]['city_id'] = $value['city_id'];
                $state_city_raw[$value['state_id']]['city'][$value['city_id']]['city_name'] = $value['city'];
            }
            $i = 0;
            foreach ($state_city_raw as $key => $value) {
                $state_city_dtl['state'][$i]['state_id'] = $value['state_id'];
                $state_city_dtl['state'][$i]['state_name'] = $value['state_name'];
                $j = 0;
                foreach ($value['city'] as $key_city => $value_city) {
                    $state_city_dtl['state'][$i]['city'][$j]['city_id'] = $value_city['city_id'];
                    $state_city_dtl['state'][$i]['city'][$j]['city_name'] = $value_city['city_name'];
                    $j++;
                }
                $i++;
            }
        }
        $op['vertical_dtl'] = $dtl_data;
        $op['city_dtl'] = $state_city_dtl;
        $op['status'] = TRUE;
        $this->response($op, REST_Controller::HTTP_OK);
    }

    public function update_profile_post()
    {

        $mytoken = $this->Common_model->validate_token();
        if ($mytoken['code'] !== 200) {
            http_response_code($mytoken['code']);
            echo json_encode($mytoken);
            die();
        }

        $op = array();
        $api_type = $this->post('api_type'); /* Dealer Distributor Member Customer */
        $user_type = $this->post('user_type'); /* Dealer Distributor Member Customer */
        $vertical = $this->post('vertical'); /* Motorcycle ,Commercial Vehicle, Probiking, International Business */

        $user_id = $this->post('user_id');

        $first_name = $this->post('first_name');
        $last_name = $this->post('last_name');
        $email = $this->post('email');

        $phone_number = $this->post('phone_number');
        try {
            if (empty($user_id))
                throw new Exception('Sorry you are not authorise user');
            /* check  for duplicate phone number */
            if ($api_type == "update") {
                $user_dtl = $this->db->select('phone_number')->from('gm_userprofile')->where('user_id !=', $user_id, FALSE)->like("phone_number", trim($phone_number))->get()->row();
                //            echo $this->db->last_query();
                if (!empty($user_dtl)) {

                    throw new Exception('Mobile Number is already register please, use diffrent number');
                } else {
                    $phone_number = "";
                    $_POST['phone_number'] = "";
                }
                $registration_fields = $this->registration_detaiils($user_type, $vertical);
                /* list of unset data */
                unset($registration_fields['userprofile']['user_id']);
                unset($registration_fields['userprofile']['created_date']);

                unset($registration_fields['auth_user']['username']);
                unset($registration_fields['auth_user']['email']);
                unset($registration_fields['auth_user']['is_active']);
                unset($registration_fields['auth_user']['date_joined']);
                unset($registration_fields['auth_user']['is_staff']);

                unset($registration_fields['gladminds_master']['dealer_code']);
                unset($registration_fields['gladminds_master']['email']);

                unset($registration_fields['role_data']);
                unset($registration_fields['role_id']);
            }
            $this->db->trans_start();
            if ($vertical == "Motorcycle" && ($type == "Distributor" || $type == "Dealer")) { /* Make Registration for Motorcycle */
                /* for dealer data updates */
                if ($api_type == "update") {
                    $allow_global_users_type = array('Members', 'Users');
                    if (in_array($user_type, $allow_global_users_type))
                        throw new Exception('Invalid User type');
                    /* update data in auth */
                    if (count($registration_fields['auth_user']))
                        $this->Common_model->update_info('auth_user', $registration_fields['auth_user'], array('id' => $user_id));

                    /* update userprofile data */
                    if (count($registration_fields['userprofile']))
                        $this->Common_model->update_info('gm_userprofile', $registration_fields['userprofile'], array('user_id' => $user_id));
                    /* Update gm_epc_shop_details data */
                    $this->Common_model->update_info('gm_epc_shop_details', $registration_fields['shop_dtl'], array('user_id' => $user_id));
                    /* update MC dealer profile */
                    if ($user_type == "Dealer" && count($registration_fields['gladminds_master']))
                        $this->Common_model->update_info('gm_mc_dealer', $registration_fields['gladminds_master'], array('user_id' => $user_id));
                    /* update MC Distributor profile */
                    if ($user_type == "Distributor" && count($registration_fields['gladminds_master']))
                        $this->Common_model->update_info('gm_sfa_mc_distributor', $registration_fields['gladminds_master'], array('user_id' => $user_id));
                }
                /*get data from tables*/
                $this->db->select('au.id AS user_id,
                    au.first_name,
                    au.last_name,
                    au.email,
                    profile.phone_number,
                    profile.image_url,
                    au.last_name,
                    au.password,
                    au.username,
                    role.name AS role_name,
                    role.id AS role_id,
                    bv.name AS vertical_name,
                    bv.id AS vertical_id');
                $this->db->from('auth_user AS au');
                $this->db->join('gm_userprofile  AS profile', 'profile.user_id=au.id', 'left');
                $this->db->join('gm_epcuserprofileroles AS up', 'up.userprofile_id = au.id', 'left');
                $this->db->join('gm_epcroles AS role', 'role.id = up.role_id', 'left');
                $this->db->join('gm_brandvertical AS bv', 'bv.id = role.vertical_id', 'left');

                $this->db->where('au.id', $user_id);
                $query = $this->db->get();
                $user_info = ($query->num_rows() > 0) ? $query->row() : FALSE;
                if ($user_info) {
                    $configration = $this->config->item('aws');
                    $user_data['email'] = $user_info->email;

                    $user_data['first_name'] = $user_info->first_name;
                    $user_data['last_name'] = $user_info->last_name;
                    $user_data['photo'] = !empty($user_info->image_url) ? $configration['host'] . "/" . $user_info->image_url : "";
                    $op['profile'] =  $user_data;
                }
                if ($user_type == "Dealer") {
                    $dealer_dtl = $this->db->select('*')->from('gm_mc_dealer')->where('user_id', $user_id)->get()->row();
                    $user_data['dealer_id'] = $dealer_dtl->dealer_code;
                    $user_data['phone_number'] = $dealer_dtl->mobile1;
                    $user_data['land_line'] = $dealer_dtl->mobile2;
                    $user_data['address'] = $dealer_dtl->shop_address;
                    $user_data['city'] = $dealer_dtl->city;
                    $user_data['state'] = $dealer_dtl->state;
                    $user_data['pincode'] = $dealer_dtl->pin_code;
                    $user_data['latitude'] = $dealer_dtl->latitude;
                    $user_data['longitude'] = $dealer_dtl->longitude;
                    $op['profile'] =  $user_data;
                }
                if ($user_type == "Distributor") {
                    $dealer_dtl = $this->db->select('*')->from('gm_sfa_mc_distributor')->where('user_id', $user_id)->get()->row();
                    $user_data['dealer_id'] = $dealer_dtl->dealer_code;
                    $user_data['phone_number'] = $dealer_dtl->mobile1;
                    $user_data['land_line'] = $dealer_dtl->mobile2;
                    $user_data['address'] = $dealer_dtl->shop_address;
                    $user_data['city'] = $dealer_dtl->city;
                    $user_data['state'] = $dealer_dtl->state;
                    $user_data['pincode'] = $dealer_dtl->pin_code;
                    $user_data['latitude'] = $dealer_dtl->latitude;
                    $user_data['longitude'] = $dealer_dtl->longitude;
                    $op['profile'] =  $user_data;
                }

                /*} elseif ($vertical == "Commercial Vehicle") {
                
            } elseif ($vertical == "Probiking") {
                
            } elseif ($vertical == "International Business") {
              */
            } else { /* User Members */
                if ($api_type == "update") {

                    $allow_global_users_type = array('Members', 'Users');
                    if (!in_array($user_type, $allow_global_users_type))
                        throw new Exception('Invalid User type');
                    /* update data in auth */

                    if (count($registration_fields['auth_user']))
                        $this->Common_model->update_info('auth_user', $registration_fields['auth_user'], array('id' => $user_id));

                    /* update userprofile data */
                    if (count($registration_fields['userprofile']))
                        $this->Common_model->update_info('gm_userprofile', $registration_fields['userprofile'], array('user_id' => $user_id));
                    //                echo $this->db->last_query();
                }
                /*get data from tables*/
                $this->db->select('au.id AS user_id,
                    au.first_name,
                    au.last_name,
                    au.email,
                    profile.phone_number,
                    profile.address,
                    profile.gender,
                    profile.city,
                    profile.state,
                    profile.pincode,
                    profile.image_url,
                    au.last_name,
                    au.password,
                    au.username,
                    role.name AS role_name,
                    role.id AS role_id,
                    bv.name AS vertical_name,
                    bv.id AS vertical_id');
                $this->db->from('auth_user AS au');
                $this->db->join('gm_userprofile  AS profile', 'profile.user_id=au.id', 'left');
                $this->db->join('gm_epcuserprofileroles AS up', 'up.userprofile_id = au.id', 'left');
                $this->db->join('gm_epcroles AS role', 'role.id = up.role_id', 'left');
                $this->db->join('gm_brandvertical AS bv', 'bv.id = role.vertical_id', 'left');

                $this->db->where('au.id', $user_id);
                $query = $this->db->get();
                $user_info = ($query->num_rows() > 0) ? $query->row() : FALSE;
                if ($user_info) {
                    $configration = $this->config->item('aws');
                    $user_data['email'] = $user_info->email;
                    $user_data['phone_number'] = $user_info->phone_number;
                    $user_data['first_name'] = $user_info->first_name;
                    $user_data['last_name'] = $user_info->last_name;
                    $user_data['address'] = $user_info->address;
                    $user_data['city'] = $user_info->city;
                    $user_data['state'] = $user_info->state;
                    $user_data['pincode'] = $user_info->pincode;
                    $user_data['gender'] = $user_info->gender;
                    $user_data['photo'] = !empty($user_info->image_url) ? $configration['host'] . "/" . $user_info->image_url : "";

                    $op['profile'] =  $user_data;
                }
            }
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE) {
                # Something went wrong.
                $this->db->trans_rollback();
                $op['status'] = False;
                $op['message'] = "Profile not updated";
            } else {
                # Everything is Perfect. 
                # Committing data to the database.
                $this->db->trans_commit();
                $op['status'] = True;
                $op['message'] = "Profile updated successfully";
            }
        } catch (Exception $ex) {
            $op['status'] = False;
            $op['message'] = $ex->getMessage();
        }
        $this->response($op, REST_Controller::HTTP_OK);
    }
    public function user_profile_get()
    {

        $mytoken = $this->Common_model->validate_token();
        if ($mytoken['code'] !== 200) {
            http_response_code($mytoken['code']);
            echo json_encode($mytoken);
            die();
        }

        $user_id = $this->get('user_id');
        $configration = $this->config->item('aws');
        $op =  array();
        try {
            if (empty($user_id))
                throw new Exception('Empty User ID');

            $user_info = $this->db->select('*')->from('gm_userprofile')->where('user_id', $user_id)->get()->row();
            if (empty($user_info))
                throw new Exception('Invalid User Detaiils');

            $data['photo'] = !empty($user_info->image_url) ? $configration['host'] . "/" . $user_info->image_url : "";
            $op['data'] = $data;
            $op['status'] = TRUE;
        } catch (Exception $exc) {
            $op['status'] = False;
            $op['message'] = $exc->getMessage();
        }
        $this->response($op, REST_Controller::HTTP_OK);
    }
    public function test_email_post()
    {
        $phone_number = 9835708476;
        $email = "kapil.mathur10@gmail.com";
        $first_name = "Kapil";
        $last_name = "Mathur";
        $p = $this->generate_send_otp($phone_number, $email, trim($first_name . " " . $last_name));
        print_r($p);
    }
}

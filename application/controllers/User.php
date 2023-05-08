<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class User extends CI_Controller
{

    function __construct()
    {
        parent::__construct();

        $this->load->helper('url');
        $this->load->model("User_model");
        $this->load->model("Common_model");
        $this->load->database();
        $this->load->library('session');
    }

    public function index()
    {
        try {

            $fullURL = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $url_components = parse_url($fullURL);
            parse_str($url_components['query'], $params);
            if (isset($params['cid'])) {
                $cid = $params['cid'];
            } else {
                $cid = "";
            }
            if (isset($params['login_token'])) {
                $userToken = $params['login_token'];
            } else {
                $userToken = "";
            }


            //$userToken = '6056c2ea799ec409ca5a6980c590e073191e9e57';
            //$this->session->set_userdata($dtl);
            // $useLoginDtl = array(
            //     'user_id' => $cid,
            //     'username'  => '',
            //     'email' => '',
            //     'logged_in' => FALSE,
            //     'userToken' => $userToken
            // );

            // print_r($cid);
            // exit();
            // Token Validation

            $tokenDtl = $this->db->query("select * from oauth2_accesstoken where token = '$userToken'");
            $tokenDtlArr = $tokenDtl->result_array();
            if (count($tokenDtlArr) < 1) {
                http_response_code(400);
                //$data['message'] = "Invalid Token";
                $data['message'] = '{"code":400, "msg":"Invalid Token"}';
                //$this->load->view('login', $data);
                echo $data['message'];
                return true;
            }
            $td = $tokenDtlArr[0];

            if ($td['user_id'] !== $cid) {
                // $data['message'] = "Invalid Token Owner";
                http_response_code(401);
                $data['message'] = '{"code":401, "msg":"Invalid Token Owner"}';
                //$this->load->view('login', $data);
                echo $data['message'];
                return true;
            }

            if (time() > strtotime($td['expires'])) {
                http_response_code(401);
                $data['message'] = '{"code":401, "msg":"Token Expired"}';
                //$data['message'] = "Token Expire";
                //$this->load->view('login', $data);
                echo $data['message'];
                return true;
            }

            //Finding username from auth_user table
            $usrIdFrmOthTb = $this->db->query("select * from auth_user where id = '$cid'");
            $usrIdFrmOthTbArr = $usrIdFrmOthTb->result_array();
            if (count($usrIdFrmOthTbArr) < 1) {
                http_response_code(401);
                $data['message'] = '{"code":401, "msg":"User Not Found"}';
                //$data['message'] = "User Not Found";
                //$this->load->view('login', $data);
                echo $data['message'];
                return true;
            }
            $username =  $usrIdFrmOthTbArr[0]['username'];
            // print_r($username);
            // // echo "Hello";
            // die();

            // $action = $this->input->post('submit');

            // $mytoken = $this->Common_model->validate_token();
            // if ($mytoken['code'] !== 200) {
            //     http_response_code($mytoken['code']);
            //     echo json_encode($mytoken);
            //     die();
            // }

            // $username =  $mytoken['username'];

            // echo $username;
            // die();


            $action = "token_validate";
            if ($action == "token_validate") {
                // $password = $this->input->post('password');
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
                $this->db->where('au.username', $username);
                $this->db->or_where('au.email', $username);
                $query = $this->db->get();
                $cm = $query->result_array();
                $user_info = ($query->num_rows() > 0) ? $query->result_array() : FALSE;

                if ($query->num_rows() > 1) { /* more than one record */
                    $data['message'] = "More than one account";
                    $this->load->view('login', $data);
                    return true;
                } else {
                    if (Common::token_pwd_verify("samplePass", "samplePass")) {
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
                        $dtl['logged_in'] = TRUE;
                        // print_r($dtl);
                        // die;
                        $this->session->set_userdata($dtl);
                        $rol = $this->session->userdata('role');
                        if ($rol[0]['role_name'] == "Distributor" || $rol[0]['role_name'] == "Dealer") {
                            redirect(base_url() . "Sa_vin_search_dealers/Vindetails?select_type=other");
                        } else {
                            redirect(base_url() . "Sa_vin_search/Vindetails");
                        }
                    } else {
                        $data['message'] = "Password Dosenot metch";
                        $this->load->view('login', $data);
                        return true;
                    }
                }
            }
            if ($action == "submit") {
                $username = $this->input->post('username');
                $password = $this->input->post('password');
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

                $this->db->where('au.username', $username);
                $this->db->or_where('au.email', $username);
                $query = $this->db->get();
                $user_info = ($query->num_rows() > 0) ? $query->result_array() : FALSE;

                if ($query->num_rows() > 1) { /* more than one record */
                    $data['message'] = "More than one account";
                    $this->load->view('login', $data);
                    return true;
                } else {
                    if (Common::django_pwd_verify($user_info[0]['password'], $password)) {
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
                        $dtl['logged_in'] = TRUE;
                        //                    print_r($dtl); die;
                        $this->session->set_userdata($dtl);
                        $rol = $this->session->userdata('role');
                        if ($rol[0]['role_name'] == "Distributor" || $rol[0]['role_name'] == "Dealer") {
                            redirect(base_url() . "Sa_vin_search_dealers/Vindetails?select_type=other");
                        } else {
                            redirect(base_url() . "Sa_vin_search/Vindetails");
                        }
                    } else {
                        $data['message'] = "Password Dosenot metch";
                        $this->load->view('login', $data);
                        return true;
                    }
                }
            }
            $data['message'] = "";
            $this->load->view('login', $data);
        } catch (Exception $e) {
            http_response_code(404);
            echo "Invalid Request";
        }
    }

    public function logout()
    {
        $newdata = array(
            'user_id' => '',
            'username'  => '',
            'email' => '',
            'logged_in' => FALSE,
        );

        $this->session->unset_userdata($newdata);
        $this->session->sess_destroy();

        redirect(base_url() . "User", 'refresh');
    }
}
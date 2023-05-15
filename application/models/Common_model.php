<?php
class Common_model extends CI_Model
{

	function validate_token()
	{
		$userToken = "";
		if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
			$authHeader = $_SERVER['HTTP_AUTHORIZATION'];
			$userToken = explode(' ', $authHeader);
			if (count($userToken) < 2) {
				$resData = array("code" => 400, "msg" => "Token Required");
				return $resData;
			}

			$cid = $userToken[0];
			$userToken =  $userToken[1];

			$tokenDtl = $this->db->query("select * from oauth2_accesstoken where token = '$userToken'");
			$tokenDtlArr = $tokenDtl->result_array();
			if (count($tokenDtlArr) < 1) {
				//http_response_code(400);
				$resData = array("code" => 400, "msg" => "Invalid Token");
				return $resData;
			}
			$td = $tokenDtlArr[0];

			if ($td['user_id'] !== $cid) {
				$resData = array("code" => 401, "msg" => "Invalid Token Owner");
				return $resData;
			}

			if (time() > strtotime($td['expires'])) {
				$resData = array("code" => 401, "msg" => "Token Expired");
				return $resData;
			}


			//Finding username from auth_user table
			$usrIdFrmOthTb = $this->db->query("select * from auth_user where id = '$cid'");
			$usrIdFrmOthTbArr = $usrIdFrmOthTb->result_array();
			if (count($usrIdFrmOthTbArr) < 1) {
				$resData = '{"code":401, "msg":"User Not Found"}';
				return $resData;
			}
			$username =  $usrIdFrmOthTbArr[0]['username'];

			$resData = array("code" => 200, "cid" => $cid, "username" => $username);
			return $resData;
		} else {
			$resData = array("code" => 401, "msg" => "Token Required");
			return $resData;
		}
	}

	function select_info($table_name, $cond = array())
	{
		$this->db->select('*');
		$this->db->from($table_name);
		if (!empty($cond)) {
			foreach ($cond as $key => $value) $this->db->where($key, $value);
		}
		$query = $this->db->get();
		return ($query->num_rows() > 0) ? $query->result_array() : FALSE;
	}
	function insert_info($table_name, $data)
	{
		$this->db->insert($table_name, $data);
		return $this->db->insert_id();
	}
	function update_info($tbl_name, $data_array, $cond)
	{
		if (!empty($cond)) {
			foreach ($cond as $key => $value) {
				$this->db->where($key, $value);
			}
		}
		$this->db->update($tbl_name, $data_array);
		return $this->db->affected_rows();
	}
	function insert_batch_record($table_name, $data)
	{
		$this->db->insert_batch($table_name, $data);
		return $this->db->insert_id();
	}

	function select_rows($selectstatement, $table_name, $cond = array(), $order_by = array())
	{
		$this->db->select($selectstatement);
		$this->db->from($table_name);
		if (!empty($cond)) {
			foreach ($cond as $key => $value) $this->db->where($key, $value);
		}
		if (!empty($order_by)) {
			foreach ($order_by as $key => $value) $this->db->order_by($key, $value);
		}
		$query = $this->db->get();
		return $query->row();
	}
	function select_info_like($table_name, $cond = array())
	{
		$this->db->select('*');
		$this->db->from($table_name);
		if (!empty($cond)) {
			foreach ($cond as $key => $value) $this->db->like($key, $value);
		}
		$query = $this->db->get();
		return ($query->num_rows() > 0) ? $query->result_array() : FALSE;
	}

	function increment_index($table_name = 'gm_orderpart')
	{
		$this->db->select('`AUTO_INCREMENT` AS nxt');
		$this->db->from('INFORMATION_SCHEMA.TABLES');
		$this->db->where('TABLE_SCHEMA', $this->db->database);
		$this->db->where('TABLE_NAME', $table_name);
		$query = $this->db->get();
		return $query->row();
	}
}

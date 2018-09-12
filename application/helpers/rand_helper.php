<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// ------------------------------------------------------------------------
/**
 * Random String Generator : Helper File for Codeigniter 
 * 
 * @author      Paras Nath Chaudhary
 * @link        https://gist.github.com/opnchaudhary/4995012
 * 
 */
// ------------------------------------------------------------------------
/*
 Documentation:
 =============
  1.
  $this->load->helper('rand_helper');
  $randomString=generateRandomString();
  echo $randomString;    
  2. 
  $this->load->helper('rand_helper');
  $randomString=generateRandomString(14);
  echo $randomString;    
*/

if ( ! function_exists('generateRandomString'))
{
    
  function generateRandomString($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, strlen($characters) - 1)];
	    }
	    return $randomString;
	}
}
/* End of file rand_helper.php */
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Maintenance {

    function __construct() {

        $this->CI = & get_instance();
    }

    public function maintenance() {

        $file_path = APPPATH . 'config/appconfig.php';
        $found = FALSE;
        if (file_exists($file_path)) {
            $found = TRUE;
            require($file_path);
        }

        if ($config['maintenance_mode'] && ($_SERVER['REMOTE_ADDR'] != $config['maintenance_allowed_ip'])) {
            $_error = & load_class('Exceptions', 'core');
            echo $_error->show_error("", "", 'error_maintenance', 200);
            exit;
        }
    }

}

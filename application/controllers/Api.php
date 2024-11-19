<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model("market"); 
    }

    public function price() {
        $data = $this->market->prices("BTCUSDT","500","D");
        
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode($data));
    }
}
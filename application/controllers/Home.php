<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model("lisa");
    }

    public function index() {
        $this->load->view("index");
    }

    public function test(){
        $data = $this->lisa->candles_vector();
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode($data));
    }
}
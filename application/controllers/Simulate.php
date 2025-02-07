<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Simulate extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model("simulation");
        $this->load->model("qdrant");
    }

    public function index() {
        $data = $this->simulation->vector_v2();
    
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode($data));

    }
    
    public function xrp($collection){
        $data = $this->lisa->xrp($collection);
    
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode($data));

    }

    public function test(){
        // $data = $this->lisa->candles_vector();
        // $payload = ["points" => $data]; 
        // $this->upsert($payload);
        
        // $data = $this->lisa->test();
        
        $data = $this->lisa->upsert_candle("candles_v2");

        header('Content-Type: application/json; charset=utf-8');
        die(json_encode($data));
    }
    
    public function search() {
        $data = $this->lisa->predict();
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode($data));
    }
    
    public function vector(){
        $data = $this->lisa->vector("candles_v2");
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode($data));
    }
    
    public function qdrant($col = ""){
        $data = $this->qdrant->collections($col);
        header('Content-Type: application/json; charset=utf-8');
        die($data);
    }

    public function delete(){
        $data = $this->qdrant->delete_collection("candle_240");
        header('Content-Type: application/json; charset=utf-8');
        die($data);
    }

    public function point($id){
        $data = $this->qdrant->get_point([$id], "candle_240");
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode($data));
    }

    public function add(){
        $data = $this->qdrant->create_collection("candles_v2",30);
        header('Content-Type: application/json; charset=utf-8');
        die($data);
    }

    public function simulate(){
        $data = $this->lisa->simulate("indicator_vector");
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode($data));
    }
    
    private function upsert($data){
        $data = $this->qdrant->add_point($data, "candle_240");
        header('Content-Type: application/json; charset=utf-8');
        die($data);
    }
}
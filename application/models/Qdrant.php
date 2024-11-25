<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Qdrant extends CI_Model {
    function __construct() {
        parent::__construct();
        $this->load->library('Vector_api');
    }

    public function collections($collection = ""){
        $pass = $collection == "" ? "collections" : "collections/". $collection ;
        $data = $this->vector_api->get($pass);
        return $data;
    }

    public function create_collection($name, $dimension){
        $uri = "collections/$name";
        $payload = ["vectors" => [
            "size"      => $dimension,  
            "distance"  => "Cosine"
        ]];

        $data = $this->vector_api->put($payload ,$uri);
        return $data;
    }

    public function delete_collection($collection = ""){
        $pass = $collection == "" ? "collections" : "collections/". $collection ;
        $data = $this->vector_api->del($pass);
        return $data;
    }

    public function add_point($payload, $name){
        $uri = "collections/$name/points";
        
        $data = $this->vector_api->put($payload ,$uri);
        return $data;
    }
    
    public function get_point($id, $name){
        $uri = "collections/$name/points";
        $payload = [
            "ids" => [$id],
            "with_payload" => true
        ];

        $data = $this->vector_api->post($payload ,$uri);
        return $data;
    }
    
    public function search($vector, $name){
        $uri = "collections/$name/points/search";
        $payload = [
            "vector" => $vector,
            "limit" => 5,
        ];

        $data = $this->vector_api->post($payload ,$uri);
        return $data;
    }
}
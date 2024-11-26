<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lisa extends CI_Model {
    function __construct() {
        parent::__construct();
        $this->load->model("qdrant");
        $this->load->library('bybit');

        $this->pair = "BTCUSDT";
        $this->candle_length = 10; // number of candle in vector
        $this->interval = "240";
    }

    public function price() {
        $prices = $this->bybit->next_prices($this->pair,"1000", $this->interval,1732291200000);
        return $prices;
    }

    public function candles_vector() {
        // create complete vector

        $prices = $this->bybit->prices($this->pair,"10000", $this->interval);
        $length = $this->candle_length;
        $limit = count($prices) - $length;
        $candle = [];
        for ($i=1; $i < $limit; $i++) { 
            $vector = [];
            $time = $prices[$i][0];
            for ($x=0; $x < $length; $x++) { 
                $num = $i+$x;
                $price = $prices[$num];

                // 0:timestamp 1: open, 2:high, 3:low, 4:close

                // calculate high
                $vector[] = ($price[2] - $price[1])/$price[1];
                
                // calculate low
                $vector[] = ($price[3] - $price[1])/$price[1];
                
                // calculate close
                $vector[] = ($price[4] - $price[1])/$price[1];
            }
            $candle[] = [
                "id" => generateUUID($time,$this->pair),
                "payload" => ["pair" => $this->pair, "time" => $time],
                "vector" => $vector
            ];
        }

        return $candle;
    }

    public function candle_vector() {
        $length = $this->candle_length;
        $prices = $this->bybit->prices($this->pair, $length,$this->interval);

        $vector = [];

        for ($i=0; $i < $length; $i++) { 
            $price = $prices[$i];
            // 1: open, 2:high, 3:low, 4:close
    
            // calculate high
            $vector[] = ($price[2] - $price[1])/$price[1];
            
            // calculate low
            $vector[] = ($price[3] - $price[1])/$price[1];
            
            // calculate close
            $vector[] = ($price[4] - $price[1])/$price[1];
        }

        return $vector;
    }

    public function vector(){
        $data = $this->candle_vector();

        $response = $this->qdrant->search($data,"candle_240");

        if($response["success"]){
            $result = $response["response"]["result"];
            $ids = [];
            $vector = [];

            foreach($result as $res){
                $ids[] = $res['id'];
                $vector[$res['id']] = $res['score'];
            }

            $neigh = $this->qdrant->get_point($ids, "candle_240");
            if($neigh["success"]){
                $return = $neigh["response"]["result"];
                for ($i=0; $i < count($return); $i++) { 
                    $return[$i]['score'] = $vector[$return[$i]['id']];

                    $prices = $this->next_price($return[$i]["payload"]["time"],$return[$i]["payload"]["pair"]);
                    $candle = [];
                    $x = 0;
                    $growth = 1;
                    foreach($prices as $price){
                        $candle[$x]["time"] = $price[0];

                        // calculate high
                        $candle[$x]["high"] = ($price[2] - $price[1])/$price[1];
            
                        // calculate low
                        $candle[$x]["low"] = ($price[3] - $price[1])/$price[1];
                        
                        // calculate close
                        $close = ($price[4] - $price[1])/$price[1];
                        $candle[$x]["close"] = $close;
                        $growth *= $close + 1;
                        $x++;
                    }

                    $return[$i]["growth"] = $growth;
                    $return[$i]["prices"] = $candle;

                }
            }
        }
        return $return;
    }

    public function next_price($time, $pair = "", $length = 20){
        $pair = $pair == "" ? $this->pair : $pair;
        $prices = $this->bybit->next_prices($pair,$length, $this->interval,$time);
        return $prices;
    }
}
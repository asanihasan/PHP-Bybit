<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lisa extends CI_Model {
    function __construct() {
        parent::__construct();
        $this->load->library('bybit');
        $this->candle_length = 10; // number of candle in vector
    }

    public function candles_vector() {
        // create complete vector
        $prices = $this->bybit->prices("BTCUSDT","2000","240");
        $length = $this->candle_length;
        $limit = count($prices) - $length;
        $candle = [];
        for ($i=0; $i < $limit; $i++) { 
            $vector = [];
            for ($x=0; $x < $length; $x++) { 
                $num = $i+$x;
                $price = $prices[$num];

                // 1: open, 2:high, 3:low, 4:close

                // calculate high
                $vector[] = ($price[2] - $price[1])/$price[1];
                
                // calculate low
                $vector[] = ($price[3] - $price[1])/$price[1];
                
                // calculate close
                $vector[] = ($price[4] - $price[1])/$price[1];
            }
            $candle[] = $vector;
        }
        return $candle;
    }

    private function candle_vector() {
        $length = $this->candle_length;
        $prices = $this->bybit->prices("BTCUSDT", $length,"240");

        $vector = [];
        foreach($prices as $price){
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
}
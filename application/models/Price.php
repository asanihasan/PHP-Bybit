<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Price extends CI_Model {
    public function addNewPair($pair, $length, $intval){
        $prices = [];
        $i = 0;
        while(count($prices) < $length){
            if(count($prices) == 0){
                $url = "https://api.bytick.com/v5/market/kline?category=spot&symbol=".$pair."&interval=".$intval."&limit=". $length;
            } else {
                $limit = $length - count($prices) + 2;
                $end = $prices[count($prices)-2][0];
                $url = "https://api.bytick.com/v5/market/kline?category=spot&symbol=".$pair."&interval=".$intval."&end=".$end."&limit=".$limit;
            }
            $get = $this->curl_get_file_contents($url);
            $price = json_decode($get)->result->list;
            sleep(1);
            if(count($prices) > 0){
                array_shift($price);
                array_shift($price);
            }

            if((count($price) == 0) || ($i > 30)){
                break;
            }
            $i ++;

            foreach($price as $pr){
                array_push($prices,$pr);
            }
        }

        return $prices;
    }
}
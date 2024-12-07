<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bybit {
    public function get($URL){
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        curl_close($c);
    
        if ($contents) return $contents;
        else return FALSE;
    }

    public function prices($pair, $length, $intval){
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
            $get = $this->get($url);
            $price = json_decode($get)->result->list;
            // sleep(1);
            if(count($prices) > 0){
                array_shift($price);
                array_shift($price);
            }

            if((count($price) == 0) || ($i > 30)) break;
            
            $i ++;

            foreach($price as $pr){
                array_push($prices,$pr);
            }
        }

        return $prices;
    }

    public function next_prices($pair, $length, $intval, $start = 0){
        $url = "https://api.bytick.com/v5/market/kline?category=spot&symbol=".$pair."&interval=".$intval."&limit=". $length . "&start=" . $start;
        $get = $this->get($url);
        $price = json_decode($get)->result->list;

        return $price;
    }

    public function tickers(){
        $url = "https://api.bybit.com/v5/market/tickers?category=spot";
        $get = $this->get($url);
        $get = json_decode($get)->result;
        return $get;
    }

    public function order_book($pair, $balance){
        $url = "https://api.bybit.com/v5/market/orderbook?category=spot&symbol=$pair&limit=3";
        $get = $this->get($url);
        $get = json_decode($get)->result;
        
        $seller = $get->a;
        foreach($seller as $sell){
            $price = $sell[0];
            $size = $sell[1];
            $qyt = $balance/$price;
            if($size >= $qyt) break;
        }
        
        return $qyt;
    }

}
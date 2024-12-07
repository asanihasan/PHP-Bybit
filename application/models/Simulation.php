<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Simulation extends CI_Model {
    function __construct() {
        parent::__construct();
        $this->load->model("qdrant");
        $this->load->model("indicator");
        $this->load->library('bybit');

        $this->pair = "BTCUSDT";
        $this->candle_length = 10; // number of candle in vector
        $this->interval = "15";
    }

    public function wma($len1 = 120, $len2 = 110){
        $prices = $this->bybit->prices("BTCUSDT","2000", 60);
        $big = $len1 > $len2 ? $len1 : $len2;
        $small = $len1 > $len2 ? $len2 : $len1;
        $wma1 = $this->indicator->wma($prices, $small);
        $wma2 = $this->indicator->wma($prices, $big);
        $rsi = $this->indicator->rsi($prices, 14, 1);

        $data = [];
        $trade_state = 0; // 0 = standby; 1 = bull; 2 = bear;
        $start = false;
        $trade = [];
        $total_growth = 1;
        for ($i=count($prices) - 1; $i >= 0; $i--) { 
            if(!isset($wma1[$i-1]) || !isset($wma2[$i-1]) || !isset($rsi[$i-1])) continue;
            if(!isset($wma1[$i]) || !isset($wma2[$i]) || !isset($rsi[$i])) continue;
            $price = $prices[$i];
            $time = $price[0];

            $status      = $wma1[$i] - $wma2[$i] > 0 ? 1 : 2; // 1 =  bull ; 2 = bear
            $last_status = $wma1[$i-1] - $wma2[$i-1] > 0 ? 1 : 2; // 1 =  bull ; 2 = bear
            $do_trade = false;
            if($status != $last_status) {
                $start = true;
                $do_trade = true;
            }

            if($start){
                if($do_trade){
                    $buy_price = $price[4];

                    if(count($trade) > 0) { // selling here
                        $trade[count($trade) - 1]["sell"] = $buy_price;
                        $growth = $trade[count($trade) - 1]["direction"] == 1 ? 1 + ($buy_price - $trade[count($trade) - 1]["buy"])/$trade[count($trade) - 1]["buy"] : 1 - ($buy_price - $trade[count($trade) - 1]["buy"])/$trade[count($trade) - 1]["buy"];
                        $trade[count($trade) - 1]["growth"] = $growth;
                        $trade[count($trade) - 1]["sell_rsi"] = $rsi[$i];
                        if($trade[count($trade) - 1]["do_trade"]) $total_growth *= $growth;
                        $trade[count($trade) - 1]["total"] = $growth;
                    }

                    // buying here
                    if($status == 1)
                    $do = $status == 1 && $rsi[$i] < 50 ? true : false;
                    if($status == 2)
                    $do = $status == 2 && $rsi[$i] > 50 ? true : false;
                    $trade[] = [
                        "do_trade" => $do,
                        'buy' => $buy_price,
                        'direction' => $status,
                        'buy_rsi' => $rsi[$i]
                    ];
                }
                // $data[] = [
                //     "status" => $status, 
                //     "price" => $price,
                // ];
            }
        }

        $result = [
            "growth" => $total_growth,
            "trade" => $trade
        ];

        return $result;
    }

    public function candle($len1 = 120, $len2 = 110){
        $prices = $this->bybit->prices("XRPUSDT","5000", 5);
        $big = max($len1, $len2);
        $small = min($len1, $len2);
        // $wma1 = $this->indicator->wma($prices, $small);
        // $wma2 = $this->indicator->wma($prices, $big);
        $rsi = $this->indicator->rsi($prices, 14, 1);

        $engulfing = 0;
        $harami = 0;
        $piercing = 0;
        $next = 2;
        for ($i=count($prices) - 1; $i >= 0; $i--) { 
            if(!isset($prices[$i]) || !isset($prices[$i+1]) || !isset($rsi[$i]) || !isset($prices[$i-$next])) continue;

            // engulfing
            if 
            (
                ( // bull
                    $prices[$i][1] - $prices[$i][4] > 0 && // current candle is green
                    $prices[$i+1][1] - $prices[$i+1][4] < 0 && // previous candle is red
                    $prices[$i][4] > $prices[$i+1][2] // current close higer than prev high
                ) ||
                ( // bear
                    $prices[$i][1] - $prices[$i][4] < 0 && // current candle is red
                    $prices[$i+1][1] - $prices[$i+1][4] > 0 && // previous candle is green
                    $prices[$i][4] < $prices[$i+1][3] // current close lower than prev low
                )
            ) $engulfing ++;
            
            
            // harami 
            if 
            (
                ( // bull
                    $prices[$i][1] - $prices[$i][4] > 0 && // current candle is green
                    $prices[$i+1][1] - $prices[$i+1][4] < 0 && // previous candle is red
                    $prices[$i][4] < $prices[$i-$next][2] && // previous candle is red
                    $prices[$i][2] < $prices[$i+1][1] && // higher now lower than open prev
                    $rsi[$i] < 40
                ) ||
                ( // bear
                    $prices[$i][1] - $prices[$i][4] < 0 && // current candle is red
                    $prices[$i+1][1] - $prices[$i+1][4] > 0 && // previous candle is green
                    $prices[$i][4] > $prices[$i-$next][3] && // previous candle is green
                    $prices[$i][4] > $prices[$i+1][1] && //  lower now lower than open prev
                    $rsi[$i] > 60
                )
            ) $harami ++;
            
            
            // piercing / dark cloud

            if (abs($prices[$i][1] - $prices[$i][4]) > 0 )
                if 
                (
                    abs($prices[$i+1][1] - $prices[$i+1][4])/abs($prices[$i][1] - $prices[$i][4]) < 2 && 
                    (
                        ( // bull
                            $prices[$i][1] - $prices[$i][4] > 0 && // current candle is green
                            $prices[$i+1][1] - $prices[$i+1][4] < 0 && // previous candle is red
                            $prices[$i][3] < $prices[$i+1][3] // low now lower than low prev
                        ) ||
                        ( // bear
                            $prices[$i][1] - $prices[$i][4] < 0 && // current candle is red
                            $prices[$i+1][1] - $prices[$i+1][4] > 0 && // previous candle is green
                            $prices[$i][2] > $prices[$i+1][2] // high now higer than high prev
                        )
                    )
                ) $piercing ++;
            
            
            // tweezer
        }

        return $harami;
    }

    public function price() {
        $prices = $this->bybit->next_prices($this->pair,"1000", $this->interval,1732291200000);
        return $prices;
    }

    public function candles_vector() {
        // create complete vector

        $prices = $this->bybit->prices($this->pair,"50000", $this->interval);
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

    public function candles_simulation($pair, $lenght, $interval) {
        // create complete vector

        $prices = $this->bybit->prices($pair,$lenght, $interval);
        $length = 5;
        $limit = count($prices) - $length;
        $candle = [];
        for ($i=1; $i < $limit; $i++) { 
            $vector = [];
            $time = $prices[$i][0];
            $next_price = [];
            for ($y=1; $y <= 5; $y++) { 
                $num = $i-$y;
                if(!isset($prices[$num])) continue 2;
                $next_price[] = $prices[$num];
            }
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
                "id" => generateUUID($time,$pair),
                "payload" => ["pair" => $pair, "time" => $time, "next" => $next_price],
                "vector" => $vector
            ];
        }

        return $candle;
    }

    //this function to create/update candel vector
    public function upsert_candle($collection){
        ini_set('memory_limit', '256M');
        $data = $this->lisa->candles_vector();
        $payload = ["points" => $data]; 
        $data = $this->qdrant->add_point($payload, $collection);
        return null;
    }
    
    public function xrp($collection){
        ini_set('memory_limit', '256M');
        $pair = "XRPUSDT";
        $vector = $this->candles_simulation($pair, 20000, 15);
        $length = count($vector[0]["vector"]);

        //create collection
        $data = $this->qdrant->create_collection($collection,$length);

        //prepare payload and insert data to collection
        $payload = ["points" => $vector]; 
        $data = $this->qdrant->add_point($payload, $collection);

        array_reverse($vector);
        $gro = 1;
        $trade = [];
        $win = 0;
        $loss = 0;

        foreach($vector as $vec){
            $point = $vec["vector"];
            $next = $vec["payload"]["next"];
            $neighbour = $this->qdrant->search($point,$collection)["response"]["result"];

            $last_status = 0;

            if(isset($growth)){
                
                if($growth["status"] > 1 ) { //long 
                    $grth = 1 + $point[2];
                    $gro *= $grth;
                    $trade[] = [
                        "growth" => $grth,
                        "total_growth" => $gro,
                    ];
                    
                    if($grth > 1) {
                        $win ++;
                    } else {
                        $loss ++;
                    }
                }
                
                if($growth["status"] < -1 ) { //short 
                    $grth = $point[2];
                    $grth *= -1;
                    $grth += 1;
                    $gro  *= $grth;
                    $trade[] = [
                        "growth" => $grth,
                        "total_growth" => $gro,
                    ];

                    if($grth > 1) {
                        $win ++;
                    } else {
                        $loss ++;
                    }
                }
                
                $last_status = $growth["status"];
            }

            $high = 0;
            $low = 0;
            $close = 0;
            $count = 0;

            foreach($neighbour as $neigh){
                if($neigh["score"] > 0.98){
                // if($neigh["score"] < 0.98 && $neigh["score"] > 0.96 ){
                    $count++;
                    $next_price = $neigh["payload"]["next"][0];
                    $op = $next_price[1];
                    $high += 1 + ($next_price[2]-$next_price[1]) / $next_price[1];
                    $low  += 1 + ($next_price[3]-$next_price[1]) / $next_price[1];
                    $close  += 1 + ($next_price[4]-$next_price[1]) / $next_price[1];
                }
            }
            
            if($count > 2) {
                $high /= $count;
                $low /= $count;
                $close /= $count;

                $status = 100 * ($close - 1);

                

                $growth = [
                    "high" => [$high],
                    "low" => [$low],
                    "close" => [$close],
                    "status" => $status
                ];
            } else {
                unset($growth);
            }

        }

        $result = [
            "win" => $win,
            "loss" => $loss,
            "growth" => $gro,
            "trades" => $trade
        ];

        return $result;
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

    public function vector($collection){
        $data = $this->candle_vector();

        $response = $this->qdrant->search($data,$collection);

        if($response["success"]){
            $result = $response["response"]["result"];
            $ids = [];
            $vector = [];

            foreach($result as $res){
                $ids[] = $res['id'];
                $vector[$res['id']] = $res['score'];
            }

            $neigh = $this->qdrant->get_point($ids, $collection);
            if($neigh["success"]){
                $return = $neigh["response"]["result"];
                for ($i=0; $i < count($return); $i++) { 
                    $return[$i]['score'] = $vector[$return[$i]['id']];

                    $prices = $this->next_price($return[$i]["payload"]["time"],$return[$i]["payload"]["pair"],5);
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

                    $return[$i]["growth"] = (1 - $growth) * 100;
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

    public function test(){
        $data = $this->create_vector();
        return $data;
    }
    
    public function create_vector() {
        $complete_vector = $this->indicator_vector(1000);

        $payload = ["points" => $complete_vector];
        $return = $this->qdrant->add_point($payload, "indicator_vector");
        return $return;
    } 

    public function indicator_vector($length = 100) {
        $count_back = 10;
        $complete_vector = []; 
        //
        $prices = $this->bybit->prices($this->pair,"$length", $this->interval);
        if($length > 100 ) array_shift($prices);

        $atr = $this->indicator->atr($prices, 14);
        $rsi = $this->indicator->rsi($prices, 14, 5);
        $wma1 = $this->indicator->wma($prices, 35);
        $wma2 = $this->indicator->wma($prices, 21);
        $ema1 = $this->indicator->ema($prices, 35);
        $ema2 = $this->indicator->ema($prices, 21);

        $i = 0;
        foreach($prices as $price) {
            $time = $price[0];
            $vector = [];
            for ($x = 0; $x < $count_back; $x++) { 
                $num = $i + $x;
                if (!isset($atr[$num]) || !isset($rsi[$num]) || !isset($wma1[$num]) || !isset($wma2[$num]) || !isset($ema1[$num]) || !isset($ema2[$num])) break 2;
                $wma = ($wma2[$num] - $wma1[$num]) /$wma1[$num];
                $ema = ($ema2[$num] - $ema1[$num]) /$ema1[$num];
                $vector[] = $wma;
                $vector[] = $ema;
                $vector[] = $rsi[$num]/100;
                $vector[] = $atr[$num];
            }
            $point["id"] = generateUUID($time,$this->pair);
            $point["payload"] = [
                "pair" => $this->pair,
                "time" => $time,
                "interval" => $this->interval,
                // "price" => $price[4]
            ];
            $point["vector"] = $vector;
            $complete_vector[] = $point;
            $i++;
        }

        return $complete_vector;
    }

    public function search($collection){
        $data = $this->indicator_vector()[0]["vector"];
        $result = $this->qdrant->search($data,$collection);
        return $result;
    }

    public function simulate($collection){
        $data = $this->indicator_vector(1000);
        $result = [];
        $vectors = [];

        foreach($data as $dt){
            $vector = $dt["vector"];
            $vectors[] = $vector;
        }

        $neighbour = $this->qdrant->batch_search($vectors,$collection);

        // $ids = [];
        // foreach($neighbour['response']['result'] as $res){
        //     foreach($res as $rs) {
        //         $ids[] = $rs["id"];
        //     }
        // }

        // $points = $this->qdrant->get_point($ids, $collection)['response']['result'];
        
        return $neighbour;
    }

    public function predict(){
        $collection = "indicator_vector";
        $data = $this->search($collection);
        $ids = [];
        foreach($data['response']['result'] as $res){
            $ids[] = $res["id"];
        }
        $points = $this->qdrant->get_point($ids, $collection)['response']['result'];   
        $result = [];
        foreach($points as $point) {
            $pair = $point["payload"]["pair"];
            $time = $point["payload"]["time"];
            $interval = $point["payload"]["interval"];
            $price = $this->bybit->next_prices($pair,"40", $interval,$time);
            $open = $price[count($price) - 1][4];
            $high = 0;
            $low = 10000000000;
            $i = 0;
            foreach($price as $pr){
                if($i == count($price)) continue;
                if($pr[2] > $high) $high = $pr[2];
                if($pr[3] < $low) $low = $pr[3];
            }
            
            $high_ratio = 100 * ($high-$open)/$open;
            $low_ratio = 100 * ($low-$open)/$open;
            $result[] = [
                "time" => $time,
                "open" => $open,
                "high"  => $high,
                "low"  => $low,
                "high_ratio" => $high_ratio,
                "low_ratio" => $low_ratio,
                // "price" => $price
            ];
        }

        $avg_gain = 0;
        $avg_loss = 0;
        foreach($result as $rs){
            $avg_gain += $rs["high_ratio"];
            $avg_loss += $rs["low_ratio"];
        }
        
        $avg_gain /= count($result);
        $avg_loss /= count($result);

        $all_result = [
            "summaries" => [
                "gain" => $avg_gain,
                "loss" => $avg_loss
            ],
            "prices" => $result
        ];

        return $all_result;
    }
}
<?php 

class Trade extends CI_Model {
    function __construct() {
        parent::__construct();
    }
    
    public function create($side = "buy", $dir = "long", $pair = "ETHUSDT"){
        $endpoint="/v5/order/create";
        $method="POST";
        $orderLinkId=uniqid();
        $pairs = $this->lisa->where('pair', $pair)->get('pair')->result();
        if(!isset($pairs[0])){
            echo "pair not found";
            die;
        }
        $pairs = $pairs[0];
        
        $balance = $this->balance();
        if($side == "sell"){
            if($dir == "long"){
                $p = $pairs->lng . $pairs->currency;
                $qyt = floor($balance[$pairs->lng] * 10000)/10000;
            } else if($dir == "short"){
                $p = $pairs->shrt . $pairs->currency;
                $qyt = floor($balance[$pairs->shrt] * 10000)/10000;
            }
        } else if($side == "buy") {
            $active_pair = $this->lisa->where('active',1)->get('pair')->result();
            if(count($active_pair) == 0) die;
            $totalizer = 0;
            $growth = 0;
            
            foreach($active_pair as $pr){
                $totalizer += $pr->growth;
                if($pr->pair == $pair) $growth = $pr->growth;
            }
            
            if($growth == 0) die;
            if($totalizer == 0) die;
            
            
            $assets = $this->balance(true);
            $qyt = ($growth/$totalizer)*$assets;
            
            //check if balance is enough
            if($balance[$pairs->currency] > $qyt){
                $qyt = floor($qyt * 10000)/10000;
            } else if($balance[$pairs->currency] > 10){
                $qyt = floor($balance[$pairs->currency] * 10000)/10000;
            } else {
                die;
            }
            
            //set pair
            if($dir == "long"){
                $p = $pairs->lng . $pairs->currency;
            } else if($dir == "short"){
                $p = $pairs->shrt . $pairs->currency;;
            }
        } else {
            die;
        }
        
        $params='{"category":"spot","symbol": "'.$p.'","side": "'.$side.'","orderType": "market","qty": "'.$qyt.'","timeInForce": "GTC","orderLinkId": "' . $orderLinkId . '"}';
        $res = $this->http_req("$endpoint","$method","$params","Create Order");
        $res['detail'] = [
            "side" => $side,
            "pair" => $pair,
            "qyt" => $qyt
        ];
        return $res;
    }
    
    public function cancel(){
        $endpoint="/v5/order/cancel";
        $method="POST";
        $params='{"category":"linear","symbol": "BTCUSDT","orderLinkId": "' . $orderLinkId . '"}';
        $res = $this->http_req($endpoint,$method,$params,"Cancel Order");
        // var_dump($res);
    }
    
    public function order(){
        $endpoint="/v5/order/realtime";
        $method="GET";
        $params="category=spot&settleCoin=USDT";
        $res = $this->http_req($endpoint,$method,$params,"List Order");
        // var_dump($res);
    }
    
    public function balance($a = false){
        $endpoint="/v5/account/wallet-balance";
        $method="GET";
        $params="accountType=spot";
        $res = $this->http_req($endpoint,$method,$params,"List Order");
        if($res['retCode'] == 0) $res = $res['result']['list'][0]['coin'];
        // $host= gethostname();
        // $ip = gethostbyname($host);
        // var_dump($ip);
        foreach($res as $cn){
            $data["{$cn['coin']}"] = $cn['walletBalance'];
        }
        if($a){
            $assets = $data;
            $tickers = $this->tickers();
            $balance = 0;
            foreach($assets as $key => $val){
                if($key == "USDT"){
                    $balance += $val;
                } else {
                    foreach($tickers->list as $lst){
                        if($lst->symbol == $key."USDT"){
                            $balance += $lst->lastPrice * $val;
                        }
                    }
                }
            }
            if($balance === 0) die;
            return $balance;
        }else {
            return $data;
        }
    }
}
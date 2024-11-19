<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Request {
    public function get($URL){
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        curl_close($c);
    
        if ($contents) return $contents;
        else return FALSE;
    }

    public function http_req($endpoint,$method,$params){
        $api_key    = bybit_api_key();
        $secret_key = bybit_secret();
        $url        = bybit_url();

        $curl = curl_init();
        
        $timestamp = time() * 1000;
        $params_for_signature= $timestamp . $api_key . "5000" . $params;
        $signature = hash_hmac('sha256', $params_for_signature, $secret_key);
        if($method=="GET")
        {
            $endpoint=$endpoint . "?" . $params;
        }
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => array(
              "X-BAPI-API-KEY: $api_key",
              "X-BAPI-SIGN: $signature",
              "X-BAPI-SIGN-TYPE: 2",
              "X-BAPI-TIMESTAMP: $timestamp",
              "X-BAPI-RECV-WINDOW: 5000",
              "Content-Type: application/json"
            ),
          ));
        if($method=="GET")
        {
          curl_setopt($curl, CURLOPT_HTTPGET, true);
        }
        $response = curl_exec($curl);
        curl_close($curl);
    
        return json_decode($response,true);
    }
}
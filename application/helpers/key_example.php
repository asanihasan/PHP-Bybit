
<!-- 
    rename this file to key_helper.php 
    add your API-Key at api_key() function;
-->

<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

//Qdrant Config Here

function vector_api_key(){
    return "<vector api key>";
}
function vector_url($path = ""){
    return "<qdrant url>" . $path;
}


//Bybit Config Here

function bybit_api_key(){
    return "<bybit api key>";
}
function bybit_secret(){
    return "<bybit secret key>";
}
function bybit_url($path = ""){
    return "<bybit url>" . $path;
}
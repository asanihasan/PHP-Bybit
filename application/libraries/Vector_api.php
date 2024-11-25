<!-- 
    API belum lengkap
    function belum pas
-->
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vector_api {
    public  function get($collection = ""){
        $apiUrl = vector_url('collections/'.$collection);
        $apiKey = api_key();
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'api-key: ' . $apiKey
        ]);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        } else {
            echo 'Response:' . $response;
        }
        
        curl_close($ch);
    }

    public function updateCollection($data) {
        $apiUrl = vector_url('collections/'.$collection);
        $apiKey = api_key();
        $ch = curl_init();
    
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'api-key: ' . $apiKey,
            'Content-Type: application/json'
        ]);
    
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
    
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        } else {
            echo 'Response:' . $response;
        }
    
        curl_close($ch);
    }

    public function deleteCollection($collection) {
        $apiUrl = vector_url('collections/'.$collection);
        $apiKey = api_key();
        $ch = curl_init();
    
        // Set the URL and HTTP method to DELETE
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    
        // Set the API key in the header
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'api-key: ' . $apiKey
        ]);
    
        // Return the response as a string instead of outputting it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        // Execute the request
        $response = curl_exec($ch);
    
        // Check for errors
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        } else {
            echo 'Response:' . $response;
        }
    
        // Close the cURL session
        curl_close($ch);
    }

    
}
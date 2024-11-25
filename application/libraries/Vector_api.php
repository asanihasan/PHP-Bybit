<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vector_api {
    public  function get($uri = "collections"){
        $apiUrl = vector_url($uri);
        $apiKey = vector_api_key();
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'api-key: ' . $apiKey
        ]);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        
        $err = curl_errno($ch);
        curl_close($ch);

        if ($err) {
            return false;
        } else {
            return $response;
        }
    }

    public function put($data, $uri = "collections") {
        $apiUrl = vector_url($uri);
        $apiKey = vector_api_key();
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
        
        $err = curl_errno($ch);
        curl_close($ch);

        if ($err) {
            return false;
        } else {
            return $response;
        }
    }

    public function del($uri = "collections") {
        $apiUrl = vector_url($uri);
        $apiKey = vector_api_key();
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
        
        $err = curl_errno($ch);
        curl_close($ch);

        if ($err) {
            return false;
        } else {
            return $response;
        }
    }

    public function post($data, $uri = "collections") {
        $apiUrl = vector_url($uri);
        $apiKey = vector_api_key();
        $ch = curl_init();
    
        // Set up cURL options
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'api-key: ' . $apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // To capture response as a string
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Capture HTTP status code
        $err = curl_errno($ch);
        $errorMessage = curl_error($ch); // Capture cURL error message
        curl_close($ch);
    
        // Check for cURL errors
        if ($err) {
            return [
                'success' => false,
                'error' => $errorMessage,
            ];
        }
    
        // Check for HTTP errors
        if ($httpCode < 200 || $httpCode >= 300) {
            return [
                'success' => false,
                'http_code' => $httpCode,
                'response' => $response,
            ];
        }
    
        // Assume response is JSON and decode it
        $decodedResponse = json_decode($response, true);
    
        return [
            'success' => true,
            'http_code' => $httpCode,
            'response' => $decodedResponse,
        ];
    }
    
}
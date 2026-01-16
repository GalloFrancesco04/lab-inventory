<?php

// Send JSON responses for API requests
//Format: { "data": ... }
Class Response {

    public static function success($data = null, $statusCode = 200){

        http_response_code($statusCode);
        header('Content-Type: application/json');

        
       $response= [ 'data'=>$data ];

        echo json_encode($response);
        exit;
        
    }

    //Sends a JSON error response
    //Format: { "error": { "code": "...", "message": "...", "details": ... } }
    public static function error ( $message=null,$errorCode="GENERIC _ERROR", $statusCode=400, $details=null){

    http_response_code($statusCode);

    header('Content-Type: application/json');

    $errorBody = [
            'code' => $errorCode,
            'message' => $message
        ];

        if ($details) {
            $errorBody['details'] = $details;
        }

        $response = [
            'error' => $errorBody
        ];

        echo json_encode($response);
        exit;
       
        
    }
}

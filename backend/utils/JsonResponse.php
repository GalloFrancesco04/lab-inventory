<?php

Class JsonResponse {

    public static function success($data=null, $message=null, $statusCode=200){

        http_response_code($statusCode);
        header('Content-Type: application/json');

        $response=['status'=>'success'];   
        if($message)
            $response['message']=$message;
        if($data)
            $response['data']=$data;

        echo json_encode($response);
        exit;
        
    }
}

<?php

class integracionApi{
    //public $url;
    public $action = [
        0 => "select",
        1 => "insert",
        2 => "update",
        3 => "delete"
    ];
    
    static function llamarAPI($url, $data/*, $accion, $return*/) {
        //$ch = curl_init('http://jmvidareal.synology.me:8085/' . $url);
        $ch = curl_init('http://192.168.0.59:8085/' . $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($data))
            ));
        }
    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
        $responseData = json_decode($response, true);
        
        curl_close($ch);
    
        return $responseData;
    }

}

?>
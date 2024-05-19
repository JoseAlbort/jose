<?php
define("ENCRYPT_METHOD", "AES-256-CBC");
define("SECRET_KEY", "98765");
define("SECRET_IV", "23452");

class Crypt
{
    public static function generarSalt() {
        $saltBytes = random_bytes(16);
        return bin2hex($saltBytes);
    }

    public static function Encriptar($string)
    {
        $bytes = $string;
        $digest = hash('sha256', $bytes, true);
        return bin2hex($digest);
    }
    public static function Desencriptar($string)
    {
        $output = false;
    
        $key = hash("sha256", SECRET_KEY);    
        
        $iv  = substr(hash("sha256", SECRET_IV), 0, 16);
    
        $output = base64_decode($string);
        $output = openssl_decrypt($output, ENCRYPT_METHOD, $key, 0, $iv);
    
        return $output;        
    }
}
?>
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Encrypt {
    
    var $skey 	= "Qe6h1ptO69X1vWd616V3224964PPAPkx";

    public function safe_b64encode($string) {

        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }

    public function safe_b64decode($string) {
        $data = str_replace(array('-','_'),array('+','/'),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    public function encode($value){
        if(!$value){return false;}
        $text = $value;
        $iv_size = openssl_cipher_iv_length('AES-256-CBC');
        $iv = openssl_random_pseudo_bytes($iv_size);

        $crypttext = openssl_encrypt($text, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, $iv);
    
        return trim(safe_b64encode($iv . $crypttext));
    }

    public function decode($value){
        if(!$value){return false;}
        $crypttext = safe_b64decode($value);

        $iv_size = openssl_cipher_iv_length('AES-256-CBC');
        $iv = substr($crypttext, 0, $iv_size);

        $decrypttext = openssl_decrypt(substr($crypttext, $iv_size), 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, $iv);

        return trim($decrypttext);
    }
}
<?php

namespace App\Models;

use CodeIgniter\Model;

use Config\Encryption;
use Config\Services;

class Mod_Crypt extends Model{

	public function var_crypt($string_text, $string_state){
    	//$encrypter = \Config\Services::encrypter();
		$encrypter = service('encrypter');

		if ($string_state == "encrypt"){
			$text_input = base64_encode($encrypter->encrypt($string_text));
			$text_output = str_replace('=', '-', str_replace('/', '_', $text_input));
			return $text_input;
		}elseif ($string_state == "decrypt"){
			//$text_input = str_replace('-', '=', str_replace('_', '/',($string_text)));
			$text_output = $encrypter->decrypt($string_text);
			return $text_output;
		}
	}

    public function Enc_String( $value ) {
        $cipher_algo = "AES-128-CTR";

        $iv_length = openssl_cipher_iv_length($cipher_algo);

        $options = 0;

        $crypt_iv = '1693339625878204';

        $crypt_key = "�s��0F&�C�!uA�o���)Q{Ԇ\~`�ݲ)���<�M";


        $crypt_iv_1 = '3156720759321927';

        $crypt_key_1 = "�U+�!�u+AuF@Ւ=VЏ̉�wVrȡ)Q{Ԇ\~`�ݲ)<�";

        $enc_val = openssl_encrypt($value, $cipher_algo, $crypt_key, $options, $crypt_iv);
        return( $enc_val);
    }

    public function Dec_String( $value ) {
        $cipher_algo = "AES-128-CTR";

        $iv_length = openssl_cipher_iv_length($cipher_algo);

        $options = 0;

        $crypt_iv = '1693339625878204';

        $crypt_key = "�s��0F&�C�!uA�o���)Q{Ԇ\~`�ݲ)���<�M";


        $crypt_iv_1 = '3156720759321927';

        $crypt_key_1 = "�U+�!�u+AuF@Ւ=VЏ̉�wVrȡ)Q{Ԇ\~`�ݲ)<�";

        $dec_val=openssl_decrypt ($value,  $cipher_algo, $crypt_key, $options, $crypt_iv);
        return( $dec_val);
    }

    public function Dec_File( $value ) {

        $iv  = base64_encode(openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-128-cbc')));

        $cipher_algo = "AES-128-CBC";//AES-CBC-PKCS5Padding";

        $iv_length = openssl_cipher_iv_length($cipher_algo);

        $options = OPENSSL_RAW_DATA;

        $crypt_iv = '[M[@_w[F4a>yQsJW';

        $crypt_key = "a:r2yt>N3_\\Py,f=";

        $dec_val=openssl_decrypt ($value,  $cipher_algo, $crypt_key, $options, $crypt_iv);
        return( base64_decode($dec_val));
    }

    public function decode_content($value){

        $cipher_algo = "AES-128-CBC";

        $options = OPENSSL_RAW_DATA;

        $crypt_iv = '[M[@_w[F4a>yQsJW';

        $crypt_key = "a:r2yt>N3_\\Py,f=";

        $dec_val=openssl_decrypt ($value,  $cipher_algo, $crypt_key, $options, $crypt_iv);

        return (base64_decode($dec_val));
        //return $dec_val;
    }

    function base64url_encode($data){
        $b64 = base64_encode($data);

        // Make sure you get a valid result, otherwise, return FALSE, as the base64_encode() function do
        if ($b64 === false) {
            return false;
        }

        // Convert Base64 to Base64URL by replacing “+” with “-” and “/” with “_”
        $url = strtr($b64, '+/', '-_');

        // Remove padding character from the end of line and return the Base64URL result
        //return rtrim($url, '=');
        return urlencode($url);
    }

    function base64url_decode($data, $strict = false){
        // Convert Base64URL to Base64 by replacing “-” with “+” and “_” with “/”
        $b64 = strtr(urldecode($data), '-_', '+/');

        // Decode Base64 string and return the original data
        return base64_decode($b64, $strict);
    }
}

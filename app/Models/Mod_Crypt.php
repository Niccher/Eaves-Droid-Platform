<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Services;

class Mod_Crypt extends Model
{
    protected $encrypter;

    public function __construct()
    {
        $this->encrypter = Services::encrypter();
    }

    /**
     * Encrypts or decrypts a string using CI encrypter.
     *
     * @param string $string_text
     * @param string $string_state 'encrypt' or 'decrypt'
     * @return string|false
     */
    public function var_crypt(string $string_text, string $string_state)
    {
        try {
            if ($string_state === 'encrypt') {
                $encrypted = base64_encode($this->encrypter->encrypt($string_text));
                log_message('info', 'Encryption successful');
                return $encrypted;
            } elseif ($string_state === 'decrypt') {
                $decrypted = $this->encrypter->decrypt(base64_decode($string_text));
                log_message('info', 'Decryption successful');
                return $decrypted;
            }

            log_message('error', 'Invalid crypt state: ' . $string_state);
            return false;
        } catch (\Exception $e) {
            log_message('error', 'Crypt operation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Encrypts a string with AES-128-CTR.
     *
     * @param string $value
     * @return string|false
     */
    public function Enc_String(string $value)
    {
        try {
            $cipher_algo = "AES-128-CTR";
            $options = 0;
            $crypt_iv = getenv('CRYPT_IV') ?: '1693339625878204'; // Prefer .env
            $crypt_key = getenv('CRYPT_KEY') ?: "�s��0F&�C�!uA�o���)Q{Ԇ\~`�ݲ)���<�M"; // Prefer .env

            $enc_val = openssl_encrypt($value, $cipher_algo, $crypt_key, $options, $crypt_iv);
            if ($enc_val !== false) {
                log_message('info', 'String encryption successful');
                return $enc_val;
            }

            log_message('error', 'String encryption failed');
            return false;
        } catch (\Exception $e) {
            log_message('error', 'Enc_String error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Decrypts a string with AES-128-CTR.
     *
     * @param string $value
     * @return string|false
     */
    public function Dec_String(string $value)
    {
        try {
            $cipher_algo = "AES-128-CTR";
            $options = 0;
            $crypt_iv = getenv('CRYPT_IV') ?: '1693339625878204'; // Prefer .env
            $crypt_key = getenv('CRYPT_KEY') ?: "�s��0F&�C�!uA�o���)Q{Ԇ\~`�ݲ)���<�M"; // Prefer .env

            $dec_val = openssl_decrypt($value, $cipher_algo, $crypt_key, $options, $crypt_iv);
            if ($dec_val !== false) {
                log_message('info', 'String decryption successful');
                return $dec_val;
            }

            log_message('error', 'String decryption failed');
            return false;
        } catch (\Exception $e) {
            log_message('error', 'Dec_String error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Decrypts file content with AES-128-CBC.
     *
     * @param string $value
     * @return string|false
     */
    public function Dec_File(string $value)
    {
        try {
            $cipher_algo = "AES-128-CBC";
            $options = OPENSSL_RAW_DATA;
            $crypt_iv = getenv('FILE_CRYPT_IV') ?: '[M[@_w[F4a>yQsJW'; // Prefer .env
            $crypt_key = getenv('FILE_CRYPT_KEY') ?: "a:r2yt>N3_\\Py,f="; // Prefer .env

            $dec_val = openssl_decrypt($value, $cipher_algo, $crypt_key, $options, $crypt_iv);
            if ($dec_val !== false) {
                log_message('info', 'File decryption successful');
                return base64_decode($dec_val);
            }

            log_message('error', 'File decryption failed');
            return false;
        } catch (\Exception $e) {
            log_message('error', 'Dec_File error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Decodes content with AES-128-CBC.
     *
     * @param string $value
     * @return string|false
     */
    public function decode_content(string $value)
    {
        return $this->Dec_File($value); // Reuses Dec_File
    }

    /**
     * Encodes to Base64URL.
     *
     * @param string $data
     * @return string|false
     */
    public function base64url_encode(string $data)
    {
        $b64 = base64_encode($data);
        if ($b64 === false) {
            log_message('error', 'Base64URL encode failed');
            return false;
        }
        $url = strtr($b64, '+/', '-_');
        return urlencode($url);
    }

    /**
     * Decodes from Base64URL.
     *
     * @param string $data
     * @param bool $strict
     * @return string|false
     */
    public function base64url_decode(string $data, bool $strict = false)
    {
        $b64 = strtr(urldecode($data), '-_', '+/');
        return base64_decode($b64, $strict);
    }
}
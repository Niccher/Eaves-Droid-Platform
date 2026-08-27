<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Services;

class CryptModel extends Model
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
    public function decrypt_file(string $value)
    {
        // Plaintext passthrough: composite dispatchers write unencrypted JSON
        // sub-files (e.g. parse_apps_notifications). If the input is already
        // valid JSON, return it unchanged instead of attempting decryption.
        if ($value !== '' && json_decode($value, true) !== null) {
            return $value;
        }

        try {
            $cipher_algo = "AES-128-CBC";
            $crypt_key = getenv('FILE_CRYPT_KEY') ?: "a:r2yt>N3_\\Py,f=";

            // 1. Try assuming raw data with dynamic IV (first 16 bytes)
            if (strlen($value) > 16) {
                $iv = substr($value, 0, 16);
                $ciphertext = substr($value, 16);
                $dec_val = openssl_decrypt($ciphertext, $cipher_algo, $crypt_key, OPENSSL_RAW_DATA, $iv);
                if ($dec_val !== false) {
                    return $dec_val;
                }
            }

            // 2. Try assuming base64-encoded data with dynamic IV (first 16 bytes of decoded output)
            $base64_decoded_input = base64_decode($value, true);
            if ($base64_decoded_input !== false && strlen($base64_decoded_input) > 16) {
                $iv = substr($base64_decoded_input, 0, 16);
                $ciphertext = substr($base64_decoded_input, 16);
                $dec_val2 = openssl_decrypt($ciphertext, $cipher_algo, $crypt_key, OPENSSL_RAW_DATA, $iv);
                if ($dec_val2 !== false) {
                    return $dec_val2;
                }
            }

            // 3. Fallback: Try static IV (legacy mode)
            $legacy_iv = getenv('FILE_CRYPT_IV') ?: '[M[@_w[F4a>yQsJW';
            $dec_legacy = openssl_decrypt($value, $cipher_algo, $crypt_key, OPENSSL_RAW_DATA, $legacy_iv);
            if ($dec_legacy !== false) {
                return $dec_legacy;
            }

            if ($base64_decoded_input !== false) {
                $dec_legacy2 = openssl_decrypt($base64_decoded_input, $cipher_algo, $crypt_key, OPENSSL_RAW_DATA, $legacy_iv);
                if ($dec_legacy2 !== false) {
                    return $dec_legacy2;
                }
            }

            return false;
        } catch (\Exception $e) {
            log_message('error', 'decrypt_file error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Encodes content with AES-128-CBC.
     *
     * @param string $value
     * @return string|false
     */
    public function encrypt_file(string $value)
    {
        try {
            $cipher_algo = "AES-128-CBC";
            $crypt_key = getenv('FILE_CRYPT_KEY') ?: "a:r2yt>N3_\\Py,f=";

            // Generate a secure 16-byte random IV
            $crypt_iv = openssl_random_pseudo_bytes(16);

            $enc_val = openssl_encrypt($value, $cipher_algo, $crypt_key, OPENSSL_RAW_DATA, $crypt_iv);
            if ($enc_val !== false) {
                // Prepend the raw IV to the encrypted value
                return $crypt_iv . $enc_val;
            }

            log_message('error', 'encrypt_file: encryption failed');
            return false;
        } catch (\Exception $e) {
            log_message('error', 'encrypt_file error: ' . $e->getMessage());
            return false;
        }
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

    /**
     * Short secure encryption for URL IDs (using AES-128-CTR and base64url)
     */
    public function encrypt_id(string $value): string
    {
        $cipher_algo = "AES-128-CTR";
        $crypt_iv = getenv('CRYPT_IV') ?: '1693339625878204';
        $crypt_key = getenv('CRYPT_KEY') ?: "s0F&C!uAo)Q{Ԇ\\~`ݲ)<M";
        
        $raw = openssl_encrypt($value, $cipher_algo, $crypt_key, OPENSSL_RAW_DATA, $crypt_iv);
        return $this->base64url_encode($raw);
    }

    /**
     * Short secure decryption for URL IDs (using AES-128-CTR and base64url)
     */
    public function decrypt_id(string $value): string
    {
        $cipher_algo = "AES-128-CTR";
        $crypt_iv = getenv('CRYPT_IV') ?: '1693339625878204';
        $crypt_key = getenv('CRYPT_KEY') ?: "s0F&C!uAo)Q{Ԇ\\~`ݲ)<M";
        
        $raw = $this->base64url_decode($value);
        return openssl_decrypt($raw, $cipher_algo, $crypt_key, OPENSSL_RAW_DATA, $crypt_iv);
    }
}
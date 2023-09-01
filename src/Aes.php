<?php

namespace Kestrelbright\PhpUtils;

class Aes
{
    private string $secretKey;
    private string $iv;

    /**
     * @return string
     */
    public function getSecretKey()
    : string
    {
        return $this->secretKey;
    }

    /**
     * @param string $secretKey
     */
    public function setSecretKey(string $secretKey)
    : void
    {
        $this->secretKey = $secretKey;
    }

    /**
     * @return string
     */
    public function getIv()
    : string
    {
        return $this->iv;
    }

    /**
     * @param string $iv
     */
    public function setIv(string $iv)
    : void
    {
        $this->iv = $iv;
    }

    public function encrypt($str) {
        if(!is_string($str)) {
            $str = json_encode($str);
        }
        $pad = 16 - (strlen($str) % 16);
        $str .= str_repeat(chr($pad), $pad);
        $base = openssl_encrypt($str, 'AES-128-CBC', $this->getSecretKey(), OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $this->getIv());
        return base64_encode($base);
    }

    public function decrypt($str) {
        $decryptData = openssl_decrypt(base64_decode($str), 'AES-128-CBC', $this->getSecretKey(), OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $this->getIv());
        if(!empty($decryptData)) {
            $pad = ord($decryptData[strlen($decryptData) - 1]);
            if ($pad > strlen($decryptData)) {
                return false;
            }
            return substr($decryptData, 0, -1 * $pad);
        }
        return false;
    }
}
<?php

namespace Kestrelbright\PhpUtils;

use OpenSSLAsymmetricKey;

class Rsa {
    private false|OpenSSLAsymmetricKey $privateKey;
    private false|OpenSSLAsymmetricKey $publicKey;

    public function getPrivateKey()
    : OpenSSLAsymmetricKey|bool
    {
        return $this->privateKey;
    }

    public function setPrivateKey(OpenSSLAsymmetricKey|bool $privateKey)
    : void
    {
        $this->privateKey = $privateKey;
    }

    public function getPublicKey()
    : OpenSSLAsymmetricKey|bool
    {
        return $this->publicKey;
    }

    public function setPublicKey(OpenSSLAsymmetricKey|bool $publicKey)
    : void
    {
        $this->publicKey = $publicKey;
    }

    public function encrypt($data) {
        openssl_public_encrypt($data, $encrypted, $this->publicKey);
        return base64_encode($encrypted);
    }

    public function decrypt($data) {
        openssl_private_decrypt(base64_decode($data), $decrypted, $this->privateKey);
        return $decrypted;
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: yarbs
 * Date: 11/10/2017
 * Time: 02:25 PM
 */

namespace Blockchain;
include('Crypt/RSA.php');
use Crypt_RSA;

class BlockVerify
{
    public static function verify ($data, $publicKey, $signature){
        $rsa = new Crypt_RSA();
        $rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);

        $signature = base64_decode($signature);
        $rsa->loadKey($publicKey);
        return $rsa->verify($data, $signature);
    }
}
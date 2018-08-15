<?php

namespace Heinlein;

class RawPublicKey {
    protected $_pubkey;

    public function __construct(string $modulus, string $exponent) {
        $this->_pubkey = $this->kimssl_pkey_get_public($modulus, $exponent);
    }

    public function getPemKey() {
        return $this->_pubkey;
    }

    public static function toPemFormat(string $modulus, string $exponent) {
        return (new static($modulus, $exponent))->getPemKey();
    }


    // Copied from: https://www.identityblog.com/?p=389
    // this function makes up for the fact that openssl doesn't
    // currently support direct use of modulus and exponent except
    // when PEM encoded in publicKeyInfo or Certificate ASN.1
    // So, believe it or not, I convert it into a publicKeyInfo ASN
    // structure and then turn it into PEM - then it works fine.

    protected function kimssl_pkey_get_public (string $modulus, string $exponent) {
        // decode to binary
        $modulus = base64_decode($modulus);
        $exponent = base64_decode($exponent);

        // make an ASN publicKeyInfo
        $exponentEncoding = $this->makeAsnSegment(0x02, $exponent);
        $modulusEncoding = $this->makeAsnSegment(0x02, $modulus);
        $sequenceEncoding = $this->makeAsnSegment(0x30,
            $modulusEncoding.$exponentEncoding);
        $bitstringEncoding = $this->makeAsnSegment(0x03, $sequenceEncoding);
        $rsaAlgorithmIdentifier = pack("H*", "300D06092A864886F70D0101010500");
        $publicKeyInfo = $this->makeAsnSegment (0x30,
            $rsaAlgorithmIdentifier.$bitstringEncoding);

        // encode the publicKeyInfo in base64 and add PEM brackets
        $publicKeyInfoBase64 = base64_encode($publicKeyInfo);
        $encoding = "-----BEGIN PUBLIC KEY-----\n";
        $offset = 0;
        while ($segment=substr($publicKeyInfoBase64, $offset, 64)){
            $encoding = $encoding.$segment."\n";
            $offset += 64;
        }
        $encoding = $encoding."-----END PUBLIC KEY-----\n";

        // use the PEM version of the key to get a key handle
        $publicKey = openssl_pkey_get_public ($encoding);

        return $publicKey;
    }

    // this helper function is necessary because PHP's openssl
    // currently requires that the public key be in PEM format
    // This does the ASN.1 type and length encoding

    protected function makeAsnSegment($type, $string)
    {
        // fix up integers and bitstrings
        switch ($type){
            case 0x02:
                if (ord($string) > 0x7f)
                    $string = chr(0).$string;
                break;
            case 0x03:
                $string = chr(0).$string;
                break;
        }

        $length = strlen($string);

        if ($length < 128){
            $output = sprintf("%c%c%s", $type, $length, $string);
        }
        else if ($length < 0x0100){
            $output = sprintf("%c%c%c%s", $type, 0x81, $length, $string);
        }
        else if ($length < 0x010000) {
            $output = sprintf("%c%c%c%c%s", $type, 0x82, $length/0x0100, $length%0x0100, $string);
        }
        else {
            $output = NULL;
        }

        return($output);
    }
}
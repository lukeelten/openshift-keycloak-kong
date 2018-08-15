<?php

namespace Heinlein;

use GuzzleHttp\Client;
use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;

class Keycloak {

    protected $_options;

    protected $_pubKeys = [];

    public function __construct(array $options) {
        $this->_options = $options;
        $this->loadPubKeys();
    }

    public function getPublicKeys() : array {
        return $this->_pubKeys;
    }

    protected function loadPubKeys() {
        $client = new Client([
            "base_uri" => $this->_options["url"]
        ]);

        $response = $client->request("GET", $this->getCertsUrl());
        if ($response->getStatusCode() >= 300) {
            throw new \Exception("Unexpected response code from keycloak");
        }

        if ($response->getHeaderLine("Content-Type") != "application/json") {
            throw new \Exception("Unexpected content type");
        }

        $body = $response->getBody();
        $json = json_decode($body, true);
        if (!array_key_exists("keys", $json)) {
            throw new \Exception("Unexpected json response from keycloak");
        }

        foreach ($json["keys"] as $key) {
            $this->parseKey($key);
        }
    }

    protected function parseKey(array $key) {
        $kid = $key["kid"];
        $n = static::base64url_decode($key["n"]);
        $exp = static::base64url_decode($key["e"]);

        $modulus = new BigInteger($n, 256);
        $exponent = new BigInteger($exp, 256);

        $rsa = new RSA();
        $rsa->loadKey(["n" => $modulus, "e" => $exponent]);

        $this->_pubKeys[$kid] = $rsa->getPublicKey();
    }

    protected function getCertsUrl() : string {
        return "realms/" . urlencode($this->_options["realm"]) . "/protocol/openid-connect/certs";
    }

    public static function base64url_decode ($input) {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
}
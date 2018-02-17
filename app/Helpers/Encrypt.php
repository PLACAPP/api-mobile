<?php

/**
 * Created by PhpStorm.
 * User: arang
 * Date: 03/06/2016
 * Time: 16:05
 */

namespace Plac\Helpers;

class Encrypt {

    /**
     * Encrypting password
     * @param password
     * returns salt and encrypted password
     */
    static public function hashSSHA($password) {

        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }

    /**
     * Decrypting password
     * @param salt, password
     * returns hash string
     */
    static public function checkhashSSHA($salt, $password) {

        $hash = base64_encode(sha1($password . $salt, true) . $salt);

        return $hash;
    }

    static public function decodedDataApp($encoded) {
        $decoded = "";
        for ($i = 0; $i < strlen($encoded); $i++) {
            $b = ord($encoded[$i]);
            $a = $b ^ 6;  // <-- must be same number used to encode the character
            $decoded .= chr($a);
        }
        return $decoded;
    }

  

}

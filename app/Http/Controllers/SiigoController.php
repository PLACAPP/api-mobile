<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Http\Requests;

class SiigoController extends Controller {

    public function getCodeAuthorization() {
        
        $granType="authorization_code";
        $clientId="e4104a00-65dc-42bd-a2d8-8f0f07c96d01";
        $clientSecret="P2BmiwSPAZi9Da8nQiFFi2BbqBB9VpqQuHmmGzXvbYo=";
        $code="0978bd9a-f44c-4a3e-b662-a7047db50200";
        $redirectUri="http://api.placapp.com/software/siigo/callback";

        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();
       

        curl_setopt($ch, CURLOPT_URL, "https://login.microsoftonline.com/apisiigo.onmicrosoft.com/oauth2/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "client_id=$clientId&client_secret=$clientSecret");
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        
        return $result;
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
    }

    public function callBack(Request $request) {

        \Illuminate\Support\Facades\Log::info(json_encode($request->all()));
    }

}

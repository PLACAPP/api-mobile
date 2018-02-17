<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

use Plac\Http\Requests;

class GuzzleHttpController extends Controller
{
    public static  function  requestHttpFirebase($notification){
        $client = new Client();
        $headers = ['Content-Type' => 'application/json',
            'Authorization' => 'key=AIzaSyAiX9Yl1gbn-i8rYdqERbCk13eIUvWDKCA'];
        $request = new \GuzzleHttp\Psr7\Request('POST', 'https://fcm.googleapis.com/fcm/send', $headers, json_encode($notification));
        $response = $client->send($request, ['timeout' => 2]);
    }


}

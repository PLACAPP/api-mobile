<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Plac\Helpers;

/**
 * Description of OneSignal
 *
 * @author carlosarango
 */
class OneSignal {

    public static function sendNotification($fields, $type = null) {

        $API_KEY = config("app.ONE_SIGNAL_API_KEY_MOBILE");
        $APP_ID = config("app.ONE_SIGNAL_APP_ID_MOBILE");
        if ($type == 'COMPANY') {
            $API_KEY = config("app.ONE_SIGNAL_API_KEY_COMPANY");
            $APP_ID = config("app.ONE_SIGNAL_APP_ID_COMPANY");
        }
        $fields["app_id"] = $APP_ID;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
            'Authorization: Basic ' . $API_KEY));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_exec($ch);
        curl_close($ch);
        return true;
    }

}

<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Installation;

class NotificationsController  {

    public static function getNotificationData($profileFromId, $profileToId) {
        $profileController = new ProfileController();
        $profileFrom = $profileController->getProfileFrom($profileFromId, $profileToId);
        $imageFrom = "";
        $nameFrom = "";
        $iconTitle = "";
        if ($profileFrom->profile_type == "pet") {
            $nameFrom = $profileFrom->placuser->plac_user_name;
            $imageFrom = $profileFrom->placuser->plac_user_image;
            if ($imageFrom == "") {
                $imageFrom = "http://res.cloudinary.com/plac/image/upload/v1487177605/placusers/image_empty.png";
            }
            switch ($profileFrom->pet_type) {
                case "DOG":
                    $iconTitle = "🐶";
                    break;
                case "CAT":
                    $iconTitle = "🐱";
                    break;
                case "MINIPIG":
                    $iconTitle = "🐱";
                    break;
                case "RABBIT":
                    $iconTitle = "🐷";
                    break;
                case "HAMSTER":
                    $iconTitle = "🐹";
                    break;
            }
        } else {
            $nameFrom = $profileFrom->profile_name;
            $imageFrom = $profileFrom->profile_image;
            if ($imageFrom == "") {
                $imageFrom = "http://res.cloudinary.com/plac/image/upload/v1487177605/placusers/image_empty.png";
            }
        }
        return array("imageFrom" => $imageFrom, "nameFrom" => $nameFrom, "iconTitle" => $iconTitle);
    }

    public static function newLikeOnPost(Request $request) {

        $profileToId = $request->profile_to_id;
        $profileFromId = $request->profile_from_id;
        $postId = $request->post_id;
        $device_token = Installation::join('plac_users', 'installations.plac_user_id', '=', 'plac_users.plac_user_id')
                        ->join("profiles", "plac_users.plac_user_id", "=", "profiles.plac_user_id")
                        ->where("profiles.profile_id", $profileToId)->orderBy('installations.created_at', 'desc')->pluck("device_token")->first();

        $notificationData = self::getNotificationData($profileFromId, $profileToId);
        $message = $notificationData["nameFrom"] . " Le ha gustado tu publicación";
        $fields = array(
            'include_player_ids' => array($device_token),
            "headings" => array("en" => "❤ Nuevo me gusta", "es" => "❤ Nuevo me gusta"),
            "contents" => array("en" => $message, "es" => $message),
            'data' => array("notification_type" => "like", "data" => array("post_id" => $postId)),
            "small_icon" => "@drawable/ic_launcher",
            "large_icon" => $notificationData["imageFrom"],
            'url' => "",
        );
        
       
        
        self::sendNotificationOneSignal($fields);

        return true;
    }

    public static function newCommentOnPost(Request $request) {
        $profileToId = $request->profile_to_id;
        $profileFromId = $request->profile_from_id;
        $postId = $request->post_id;
        $device_token = Installation::join('plac_users', 'installations.plac_user_id', '=', 'plac_users.plac_user_id')
                        ->join("profiles", "plac_users.plac_user_id", "=", "profiles.plac_user_id")
                        ->where("profiles.profile_id", $profileToId)->orderBy('installations.created_at', 'desc')->pluck("device_token")->first();

        $notificationData = self::getNotificationData($profileFromId, $profileToId);
        $message = $notificationData["nameFrom"] . " ha comentado tu publicación";
        $fields = array(
            'include_player_ids' => array($device_token),
            "headings" => array("en" => "📝 Nuevo comentario", "es" => "📝 Nuevo comentario"),
            "contents" => array("en" => $message, "es" => $message),
            'data' => array("notification_type" => "comment", "data" => array("post_id" => $postId)),
            "small_icon" => "@drawable/ic_launcher",
            "large_icon" => $notificationData["imageFrom"],
            'url' => "",
        );
        self::sendNotificationOneSignal($fields);
    }

    public static function newFollower(Request $request) {


        $profileToId = $request->profile_to_id;
        $profileFromId = $request->profile_from_id;
        $postId = $request->post_id;
        $device_token = Installation::join('plac_users', 'installations.plac_user_id', '=', 'plac_users.plac_user_id')
                        ->join("profiles", "plac_users.plac_user_id", "=", "profiles.plac_user_id")
                        ->where("profiles.profile_id", $profileToId)->orderBy('installations.created_at', 'desc')->pluck("device_token")->first();

        $notificationData = self::getNotificationData($profileFromId, $profileToId);
        $message = $notificationData["nameFrom"] . " ahora te sigue";
        $fields = array(
            'include_player_ids' => array($device_token),
            "headings" => array("en" => $notificationData["iconTitle"] . " Nuevo seguidor", "es" => $notificationData["iconTitle"]." Nuevo seguidor"),
            "contents" => array("en" => $message, "es" => $message),
            'data' => array("notification_type" => "follower", "data" => array("profile_from_id" => $profileFromId)),
            "small_icon" => "@drawable/ic_launcher",
            "large_icon" => $notificationData["imageFrom"],
            'url' => "",
        );
        self::sendNotificationOneSignal($fields);
    }

    public static function sendNotificationOneSignal($fields) {
 
       
        $API_KEY = config("app.ONE_SIGNAL_API_KEY");
        $APP_ID=config("app.ONE_SIGNAL_APP_ID"); 
        
        $fields["app_id"] = $APP_ID;      
        $fields = json_encode($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
            'Authorization: Basic '.$API_KEY));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);
        return true;
    }

}

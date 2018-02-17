<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Helpers\OneSignal;
use Plac\Helpers\Notifications;

class NotificationFollowersController extends Controller {

    public function newFollower(Request $request, $canSendNotification = true) {
        if (!$canSendNotification) {
            return "Setting doesn`t permit receive notification";
        }




        $profileToId = $request->profile_to_id;
        $profileFromId = $request->profile_from_id;
        $device_token = InstallationController::getDeviceToken($profileToId);
        $notificationData = Notifications::getNotificationData($profileFromId, $profileToId);
        $message = $notificationData["nameFrom"] . " ahora te sigue";
        $fields = array(
            'include_player_ids' => array($device_token),
            "headings" => array("en" => $notificationData["iconTitle"] . " Nuevo seguidor", "es" => $notificationData["iconTitle"] . " Nuevo seguidor"),
            "contents" => array("en" => $message, "es" => $message),
            'data' => array("notification_type" => "follower", "data" => array("profile_from_id" => $profileFromId)),
            "small_icon" => "@drawable/ic_launcher",
            "large_icon" => $notificationData["imageFrom"],
            'url' => "",
        );
        OneSignal::sendNotification($fields, 'MOBILE');
    }

}

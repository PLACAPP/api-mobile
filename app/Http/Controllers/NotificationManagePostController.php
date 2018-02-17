<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Installation;
use DateTime;
use Plac\NotificationPost;
use Plac\Helpers\HelperIDs;
use Plac\Helpers\OneSignal;
use Plac\Helpers\Notifications;

class NotificationManagePostController extends Controller {

    public function newLikeOnPost(Request $request, $canSendNotification = true) {

        $request->type = 'LIKE';
        $response = $this->store($request);

        if (!$canSendNotification) {
            return "Setting doesn`t permit receive notification";
        }
        $profileToId = $request->profile_to_id;
        $profileFromId = $request->profile_from_id;
        $device_token = InstallationController::getDeviceToken($profileToId);
        $notificationData = Notifications::getNotificationData($profileFromId, $profileToId);
        $message = $notificationData["nameFrom"] . " Le ha gustado tu publicación";


        $fields = array(
            'include_player_ids' => array($device_token),
            "headings" => array("en" => "❤ Nuevo me gusta", "es" => "❤ Nuevo me gusta"),
            "contents" => array("en" => $message, "es" => $message),
            'data' => array("notification_type" => "like", 'notification' => $response),
            "small_icon" => "@drawable/ic_launcher",
            "large_icon" => $notificationData["imageFrom"],
            'url' => "",
        );


        return OneSignal::sendNotification($fields, 'MOBILE');
    }

    public function newCommentOnPost(Request $request, $canSendNotification = true) {

        $request->type = 'COMMENT';
        $response = $this->store($request);
        if (!$canSendNotification) {
            return "Setting doesn`t permit receive notification";
        }

        $profileToId = $request->profile_to_id;
        $profileFromId = $request->profile_from_id;
        $device_token = InstallationController::getDeviceToken($profileToId);
        $notificationData = Notifications::getNotificationData($profileFromId, $profileToId);
        $message = $notificationData["nameFrom"] . " ha comentado tu publicación";

        $fields = array(
            'include_player_ids' => array($device_token),
            "headings" => array("en" => "📝 Nuevo comentario", "es" => "📝 Nuevo comentario"),
            "contents" => array("en" => $message, "es" => $message),
            'data' => array("notification_type" => "comment", 'notification' => $response),
            "small_icon" => "@drawable/ic_launcher",
            "large_icon" => $notificationData["imageFrom"],
            'url' => "",
        );

        return OneSignal::sendNotification($fields, 'MOBILE');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $notificationPost = new \Plac\NotificationPost();
        $notificationType = $request->type;
        $notificationPost->type = $notificationType;
        $postId = $request->post_id;
        $profileFromId = $request->profile_from_id;
        if ($notificationType == 'LIKE') {
            $notifications = \Plac\NotificationPost::where('post_id', $postId)->where('profile_from', $profileFromId)->where('type', 'LIKE')->get();
            if ($notifications != null) {
                foreach ($notifications as $notification) {
                    $notification->delete();
                }
            }
        }
        $notificationPost->notification_post_id = $this->generateUniqueId();
        $notificationPost->post_id = $postId;
        $notificationPost->profile_from = $profileFromId;
        $notificationPost->profile_to = $request->profile_to_id;
        if ($notificationType == 'COMMENT') {
            $notificationPost->comment = $request->comment;
        }
        $notificationPost->save();

        return $notificationPost;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }

    public function getNotifications($profileTo) {
        $notifications = \Plac\NotificationPost::where('profile_to', $profileTo)
                ->where('profile_from', '!=', $profileTo)
                ->with('profileFrom.placUser', 'post.profile')
                ->latest()
                ->paginate(10);
        $this->getNotificationTimeAgoCreatedAt($notifications);
        return $notifications;
    }

    public function getNotificationTimeAgoCreatedAt($notifications) {
        $i = 0;
        foreach ($notifications as $notification) {
            $created_at = $notification->created_at;
            $now = new DateTime();
            $ago = \Plac\Helpers\DateUtils::getTimeAgo($now, $created_at);

            $notifications[$i]["post_time_ago"] = $ago;
            $i++;
        }
        return $notifications;
    }

    public function generateUniqueId() {
        $idGenerated = HelperIDs::generateID();
        $count = NotificationPost::where('notification_post_id', $idGenerated)->count();
        if ($count == 1) {
            $this->generateUniqueId();
        } else {
            return $idGenerated;
        }
    }

}

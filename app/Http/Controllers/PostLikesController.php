<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Helpers\HelperIDs;
use Plac\Helpers\JsonObjects;
use Plac\Http\Requests;
use Plac\PostLikes;
use Illuminate\Support\Facades\Log;

class PostLikesController extends Controller {

    public function managePostLike(Request $request) {
        $profile_id = $request->profile_from_id;
        $post_id = $request->post_id;

        $type = "";
        $postLikes = PostLikes::where("profile_id", $profile_id)
                ->where("post_id", $post_id)
                ->first();

        if ($postLikes != null) {
            $postLikes->delete();
            $type = "like_deleted";
        } else {
            $postLikes = new PostLikes();
            $postLikes->post_like_id = $this->generateUniqueId();
            $postLikes->profile_id = $profile_id;
            $postLikes->post_id = $post_id;

            if ($profile_id != $request->profile_to_id) {
                $this->notificationManage($request, $request->profile_to_id);
            }

            $postLikes->save();
            $type = "like_added";
        }

        $array = array("current_likes" => $this->getNumberLikes($post_id));
        return JsonObjects::createJsonObjectModel($type, $post_id, $array);
    }

    public function notificationManage($request, $profileId) {
        $canSendNotification = true;
        $profile = \Plac\Profile::where('profile_id', $profileId)->with('placUser.notificationsSetting')->first();
        $notificationsSettings = $profile->placUser->notificationsSetting;
        Log::info(json_encode($profile));
        if ($notificationsSettings != null) {
            $canSendNotification = ($notificationsSettings->notification_posts_state == 1) ? true : false;
        }

        $notificationManage = new NotificationManagePostController();
        $notificationManage->newLikeOnPost($request, $canSendNotification);
    }

    public function getPostLikes($post_id) {
        $postLikes = PostLikes::where("post_id", $post_id)->with("profile")->get();
        return $postLikes;
    }

    public function getNumberLikes($post_id) {
        $postLikesCount = PostLikes::where("post_id", $post_id)->count();
        return $postLikesCount;
    }

    public function generateUniqueId() {
        $idGenerated = HelperIDs::generateID();
        $count = PostLikes::where('post_like_id', $idGenerated)->count();
        if ($count == 1) {
            $this->generateUniqueId();
        } else {
            return $idGenerated;
        }
    }

}

<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Helpers\HelperIDs;
use Plac\Http\Requests;
use Plac\PostComments;
use DateTime;
use Illuminate\Support\Facades\Log;

class PostCommentsController extends Controller {

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {

        $profileFromId = $request["profile_from_id"];
        $profileToiId = $request["profile_to_id"];
        $post_id = $request["post_id"];
        $comment = $request["comment"];

        $postComment = new PostComments();
        $postComment->post_comment_id = $this->generateUniqueId();
        $postComment->profile_id = $profileFromId;
        $postComment->post_id = $post_id;
        $postComment->message = $comment;
        $postComment->save();

        if ($profileFromId != $profileToiId) {
            $this->notificationManage($request, $profileToiId);
        }

        $postComment = PostComments::where("post_comment_id", $postComment->post_comment_id)->with("profile.placUser")->first();
        $profileController = new ProfileController();
        $profile = $postComment->profile;
        $postComment["post_time_ago"] = "Hace un momento";
        $postComment["profile"]["pet_type_translate"] = $profileController->getPetType($profile->pet_type);
        return $postComment;
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
        $notificationManage->newCommentOnPost($request, $canSendNotification);
    }

    public function getPostCommentTimeAgoCreatedAt($postComments) {
        $i = 0;
        foreach ($postComments as $postComment) {
            $created_at = $postComment->created_at;
            $now = new DateTime();
            $ago = \Plac\Helpers\DateUtils::getTimeAgo($now, $created_at);

            $postComments[$i]["post_time_ago"] = $ago;
            $i++;
        }


        return $postComments;
    }

    public function getPostComments($post_id, $profileFromId) {

        $postComments = PostComments::where("post_id", $post_id)->with("profile.placUser", "profile.breed")->orderBy('created_at', 'desc')->paginate(20);


        $profiles = new ProfileController();
        for ($i = 0; $i < count($postComments); $i++) {
            $profile = $postComments[$i]->profile;
            if ($profile->profile_type == "pet") {
                $petType = $profiles->getPetType($profile->pet_type);
                $postComments[$i]["profile"]["pet_type_translate"] = $petType;
            }
        }

        $postComments = $this->getPostCommentTimeAgoCreatedAt($postComments);
        $postComments = $this->checkProfileFollowing($postComments, $profileFromId);
        return $postComments;
    }

    public function checkProfileFollowing($postComments, $profileFromId) {
        $i = 0;
        foreach ($postComments as $postComment) {
            $profile = $postComment->profile;
            $isFollowing = FollowersController::checkProfileIsFollowing($profileFromId, $profile->profile_id);
            $postComments[$i]["isFollowing"] = $isFollowing;
            $i++;
        }
        return $postComments;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        PostComments::destroy($id);
        return "post_deleted";
    }

    public function generateUniqueId() {
        $idGenerated = HelperIDs::generateID();
        $count = PostComments::where('post_comment_id', $idGenerated)->count();
        if ($count == 1) {
            $this->generateUniqueId();
        } else {
            return $idGenerated;
        }
    }

}

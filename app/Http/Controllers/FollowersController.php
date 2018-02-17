<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Plac\Follower;
use Plac\Helpers\HelperIDs;
use Plac\Helpers\JsonObjects;
use Plac\Http\Requests;
use DateTime;
use Illuminate\Support\Facades\Log;

class FollowersController extends Controller {

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {

        $profileFromId = $request["profile_from_id"];
        $profileToId = $request["profile_to_id"];


        $follower = new Follower();
        $follower->follower_id = $this->generateUniqueId();
        $follower->profile_from_id = $profileFromId;
        $follower->profile_to_id = $profileToId;
        $follower->save();

        return $follower;
    }

    public function addRelationProfileCreated($profileId) {

        $follower = new Follower();
        $follower->follower_id = $this->generateUniqueId();
        $follower->profile_from_id = $profileId;
        $follower->profile_to_id = $profileId;
        $follower->save();
        return $follower;
    }

    public function generateUniqueId() {
        $idGenerated = HelperIDs::generateID();
        $count = Follower::where('follower_id', $idGenerated)->count();
        if ($count == 1) {
            $this->generateUniqueId();
        } else {
            return $idGenerated;
        }
    }

    public function getFollowers($profileDevice, $profileToId) {
        $followers = Follower::where("profile_to_id", $profileToId)->where("profile_from_id", "<>", $profileToId)->with("profileFrom.placUser")->with("profileFrom.breed")->paginate(20);
        $followers = json_encode($followers);
        $followers = \GuzzleHttp\json_decode($followers, true);
        $i = 0;
        foreach ($followers["data"] as $follower) {
            $isFollowing = 0;
            if (FollowersController::checkProfileIsFollowing($profileDevice, $follower["profile_from_id"])) {
                $isFollowing = 1;
            }
            $followers["data"][$i]["isFollowing"] = $isFollowing;

            $i++;
        }

        return $followers;
    }

    public function getFollowings($profileDevice, $profileFromId) {
        $followings = Follower::where("profile_from_id", $profileFromId)->where("profile_to_id", "<>", $profileFromId)->with("profileTo.placUser")->with("profileTo.breed")->paginate(20);
        $followings = json_encode($followings);
        $followings = \GuzzleHttp\json_decode($followings, true);
        $i = 0;
        foreach ($followings["data"] as $following) {

            $isFollowing = 0;
            if (FollowersController::checkProfileIsFollowing($profileDevice, $following["profile_to_id"])) {
                $isFollowing = 1;
            }
            $followings["data"][$i]["isFollowing"] = $isFollowing;
            $i++;
        }

        return $followings;
    }

    public
            function getFollowing() {
        
    }

    public function getCountFollower($profile_id) {

        $query = "SELECT   count(*) as following,
                   (SELECT count(* )
                    FROM  followers 
                    WHERE profile_to_id='$profile_id' 
                    AND profile_from_id!='$profile_id'    ) as followers

                FROM followers
                WHERE profile_from_id='$profile_id'  
                AND profile_to_id!='$profile_id' ";

        return DB::select(DB::raw($query));
    }

    /**
     * @param Request $request
     */
    public function manageFollower(Request $request) {

        $profile_from_id = $request['profile_from_id'];
        $profile_to_id = $request['profile_to_id'];

        $follower = Follower::where("profile_from_id", $profile_from_id)->where("profile_to_id", $profile_to_id)->first();
        if ($follower != null) {
            $follower->delete();
            $type = "follower_deleted";
        } else {
            $this->manageNotifications($request, $profile_to_id);

            $follower = $this->store($request, $profile_to_id);
            $type = "follower_added";
        }

        return JsonObjects::createJsonObjectModel($type, $follower->follower_id, $follower);
    }

    public function manageNotifications($request, $profileToId) {

        $canSendNotification = true;
        $profile = \Plac\Profile::where('profile_id', $profileToId)->with('placUser.notificationsSetting')->first();
        $notificationsSettings = $profile->placUser->notificationsSetting;
        Log::info(json_encode($profile));
        if ($notificationsSettings != null) {
            $canSendNotification = ($notificationsSettings->notification_followers_state == 1) ? true : false;
        }


        $notificationFollowers = new NotificationFollowersController();
        $notificationFollowers->newFollower($request, $canSendNotification);
    }

    public static function checkProfileIsFollowing($profileFromId, $profileToId) {
        $check = false;
        $follower = Follower::where("profile_from_id", $profileFromId)->where("profile_to_id", $profileToId)->first();
        if ($follower != null) {
            $check = true;
        }
        return $check;
    }

    public function getRecentsFollowers($profileFrom) {
        $now = new DateTime();
        $followers = Follower::where('profile_to_id', $profileFrom)->where('profile_from_id', '!=', $profileFrom)->with('profileFrom.placUser')->latest()->paginate(15);
        $i = 0;

        foreach ($followers as $follower) {
            // Check if profileFrom is following the new follower
            $check = self::checkProfileIsFollowing($profileFrom, $follower->profile_from_id);
            $followers[$i]['isFollowing'] = $check;
            //get Time ago started following
            $ago = \Plac\Helpers\DateUtils::getTimeAgo($now, $follower->created_at);
            $followers[$i]["time_ago"] = $ago;
            $i++;
        }
        return $followers;
    }

}

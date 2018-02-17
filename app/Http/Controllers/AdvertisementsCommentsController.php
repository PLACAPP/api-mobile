<?php

namespace Plac\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use Plac\Follower;
use Plac\Helpers\HelperIDs;
use Plac\Helpers\JsonObjects;
use Plac\Http\Requests;
use Plac\Post;
use Plac\PostComments;
use Plac\PostLikes;

class AdvertisementsCommentsController extends Controller
{

 /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {

        $profileFromId = $request["profile_from_id"];
        $profileToiId = $request["profile_to_id"];
        $advertisement_id = $request["advertisement_id"];
        $comment = $request["comment"];

        $advertisementComment = new \Plac\AdvertisementsComments();
        $advertisementComment->advertisement_comment_id = $this->generateUniqueId();
        $advertisementComment->profile_id = $profileFromId;
        $advertisementComment->advertisement_id = $advertisement_id;
        $advertisementComment->comment= $comment;
        $advertisementComment->save();

        if ($profileFromId != $profileToiId) {
            //NotificationsController::newCommentOnPostFirebase($request);
        }

        $advertisementComment = \Plac\AdvertisementsComments::where("advertisement_comment_id", $advertisementComment->advertisement_comment_id)->with("profile.placUser")->first();
        
        $profileController = new ProfileController();
        $profile = $advertisementComment->profile;
        $advertisementComment["post_time_ago"]="Hace un momento";
        $advertisementComment["profile"]["pet_type_translate"] = $profileController->getPetType($profile->pet_type);
        return $advertisementComment;
    }
    
      public function getPostCommentTimeAgoCreatedAt($advertisementComments) {
        $i = 0;
        foreach ($advertisementComments as $advertisementComment) {
            $created_at = $advertisementComment->created_at;
            $now = new DateTime();
            $ago = \Plac\Helpers\DateUtils::getTimeAgo($now, $created_at);

            $advertisementComments[$i]["post_time_ago"] = $ago;
            $i++;
        }
        return $advertisementComments;
    }
    
    
      public function getAdvertisementComments($advertisement_id) {

        $advertisementComments = \Plac\AdvertisementsComments::where("advertisement_id", $advertisement_id)->with("profile.placUser", "profile.breed")->orderBy('created_at', 'desc')->paginate(30);


        $profiles = new ProfileController();
        for ($i = 0; $i < count($advertisementComments); $i++) {

            $profile = $advertisementComments[$i]->profile;
            if ($profile->profile_type == "pet") {
                $petType = $profiles->getPetType($profile->pet_type);
                $advertisementComments[$i]["profile"]["pet_type_translate"] = $petType;
            }
        }
        $advertisementComments= $this->getPostCommentTimeAgoCreatedAt($advertisementComments);
        return $advertisementComments;
    }
    
     public function generateUniqueId() {
        $idGenerated = HelperIDs::generateID();
        $count = \Plac\AdvertisementsComments::where('advertisement_comment_id', $idGenerated)->count();
        if ($count == 1) {
            $this->generateUniqueId();
        } else {
            return $idGenerated;
        }
    }
    
}

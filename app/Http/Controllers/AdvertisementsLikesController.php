<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;

use Plac\AdvertisementsLikes;
use Plac\Http\Requests;
use Plac\Helpers\HelperIDs;
use Plac\Helpers\JsonObjects;


class AdvertisementsLikesController extends Controller
{
    public function manageAdvertisementLikes(Request $request)
    {
        $profile_id = $request->profile_from_id;
        $advertisement_id= $request->advertisement_id;

        $type = "";
        $advertisemnentsLikes = AdvertisementsLikes::where("profile_id", $profile_id)->where("advertisement_id", $advertisement_id)->first();

        if ($advertisemnentsLikes != null) {
            $advertisemnentsLikes->delete();
            $type = "like_deleted";
        } else {
            $advertisemnentsLikes = new AdvertisementsLikes();
            $advertisemnentsLikes->advertisement_like_id = $this->generateUniqueId();
            $advertisemnentsLikes->profile_id = $profile_id;
            $advertisemnentsLikes->advertisement_id = $advertisement_id;

            /* if ($profile_id != $request->profile_to_id) {
                 NotificationsController::newLikeOnPostFirebase($request);
             }*/

            $advertisemnentsLikes->save();
            $type = "like_added";
        }

        return $type;

    }

    public function getNumberLikes($advertisement_id)
    {
        $postLikesCount = AdvertisementsLikes::where("advertisement_id", $advertisement_id)->count();
        return $postLikesCount;
    }


    public function generateUniqueId()
    {
        $idGenerated = HelperIDs::generateID();
        $count = AdvertisementsLikes::where('advertisement_like_id', $idGenerated)->count();
        if ($count == 1) {
            $this->generateUniqueId();
        } else {
            return $idGenerated;
        }
    }
}

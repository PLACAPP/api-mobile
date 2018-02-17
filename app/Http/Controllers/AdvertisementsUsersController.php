<?php

namespace Plac\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Plac\Advertisements;
use Plac\AdvertisementsLikes;
use Plac\AdvertisementsComments;
use Plac\AdvertisementsUsers;
use Plac\Billing;
use Plac\Helpers\HelperIDs;

class AdvertisementsUsersController extends Controller {

    public static function getAllAdvertisements($skip, $profile) {
        $profileController = new ProfileController();
        $petType = $profileController->getPetType($profile["pet_type"]);
        $petType = $petType . "s";
        $advertisements = Advertisements::join('campaign', 'advertisements.campaign_id', '=', 'campaign.campaign_id')
                ->where("advertisements_state","Activo")
                ->where("campaign.campaign_public", "like", "%$petType%")
                ->with("campaign.place", 'commentsCount', "likesCount")
                ->skip($skip)
                ->orderBy('advertisements.created_at', 'desc')
                ->take(3)
                ->get();
        $profileId = $profile["profile_id"];

        $advertisements = self::getAdvertisementsHasLikesComments($advertisements, $profileId);


        return $advertisements;
    }

    public static function getAdvertisementsHasLikesComments($advertisements, $profileId) {

        $i = 0;
        foreach ($advertisements as $advertisement) {

            $advertisementLikesCount = AdvertisementsLikes::where("advertisement_id", $advertisement->advertisements_id)->where("profile_id", $profileId)->count();
            $advertisementCommentsCount = AdvertisementsComments::where("advertisement_id", $advertisement->advertisements_id)->where("profile_id", $profileId)->count();

            $advertisements[$i]["has_Liked"] = $advertisementLikesCount;
            if ($advertisementCommentsCount > 0) {
                $advertisementCommentsCount = 1;
            }
            $advertisements[$i]["has_commented"] = $advertisementCommentsCount;

            $i++;
        }

        return $advertisements;
    }

    public static function getCampaignPublic($advertisements) {

        $countMain = 0;
        foreach ($advertisements as $advertisement) {


            $campaign_public = $advertisement->campaign->campaign_public;
            $campaign_public = json_encode(json_decode($campaign_public, true));
            $advertisements[$countMain]["campaign"]["campaign_public"] = $campaign_public;
            $countMain++;
        }
    }

    /* Save a new register of user that view the advertising */

    public function saveNewRegisterAdvertising(Request $request) {

        $advertisementsusers = new AdvertisementsUsers();

        if ($request->advertisements_id != "" && $request->plac_user_id != "") {

            /* If exist a register of user and of avertisements */

            $count_exist_id_advertising = AdvertisementsUsers::where('advertisements_id', $request->advertisements_id)->where("plac_user_id", $request->plac_user_id)->count();

            /* find of balance of the company */

            $advertisingbilling = Advertisements::join("campaign", "campaign.campaign_id", "=", "advertisements.campaign_id")
                            ->where("advertisements_id", $request->advertisements_id)->first();

            $billingvalue = Billing::where("place_id", $advertisingbilling->place_id)->where("billing_plan_use", 1)->first();

            /* all the views of the advertisements for user */

            $consultadvertisingusers = AdvertisementsUsers::where('advertisements_id', $request->advertisements_id)->count();

            $totalsumclicksuser = 500 * intval($consultadvertisingusers);

            /* The budget of advertisements */

            $budgetadvertisingusers = Advertisements::where('advertisements_id', $request->advertisements_id)->first();

            if ($totalsumclicksuser < intval($budgetadvertisingusers->advertisements_budget)) {

                if ($count_exist_id_advertising == 0 && $billingvalue->billing_value_balance != 0) {

                    $billingvalue->billing_value_balance = intval($billingvalue->billing_value_balance) - 500;

                    $advertisementsusers->advertisements_users_id = $this->generateUniqueIdAdvertisingUsersViews();
                    $advertisementsusers->advertisements_id = $request->advertisements_id;
                    $advertisementsusers->plac_user_id = $request->plac_user_id;
                    $advertisementsusers->created_at = Carbon::now();

                    $advertisementsusers->save();
                    $billingvalue->save();

                    return "Click guardado.";
                } else if ($billingvalue->billing_value_balance == 0) {
                    $budgetadvertisingusers->advertisements_state = "Desactivado";
                    $budgetadvertisingusers->save();
                    return "La empresa no tiene presupuesto.";
                } else {
                    return "Click ya existe.";
                }
            } else {

                $budgetadvertisingusers->advertisements_state = "Bloqueado";

                $budgetadvertisingusers->save();

                return "El total de click ya supero al presupuesto.";
            }
        }
    }

    /* Generate a unique at the table advertising users views */

    public function generateUniqueIdAdvertisingUsersViews() {
        $idGenerated = HelperIDs::generateID();
        $count_exist_id_advertising = AdvertisementsUsers::where('advertisements_users_id', $idGenerated)->count();

        if ($count_exist_id_advertising == 1) {
            $this->generateUniqueIdProductoUserViews();
        } else {
            return $idGenerated;
        }
    }

}

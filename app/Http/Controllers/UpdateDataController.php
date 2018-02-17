<?php

namespace Plac\Http\Controllers;


use Illuminate\Http\Request;
use Plac\Profile;

class UpdateDataController extends Controller
{


    public function updateInstallation(Request $request)

    {

        $profileId = $request->profile_id;
        $profile = Profile::where("profile_id", $profileId)->with("placuser")->first();

        $plac_user_id = $profile->plac_user_id;
        $installation = new \Plac\Installation();
        $installation->installation_id = InstallationController::generateUniqueId();
        $installation->app_version = $request->app_version;
        $installation->so_version = $request->so_version;
        $installation->so_version_release = $request->so_version_release;
        $installation->device_type = $request->device_type;
        $installation->device_token = $request->device_token;
        $installation->plac_user_id = $plac_user_id;
        $installation->save();


        return $profile;
    }


}

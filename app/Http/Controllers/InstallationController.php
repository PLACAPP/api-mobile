<?php

namespace Plac\Http\Controllers;

use Plac\Helpers\HelperIDs;
use Plac\Http\Requests;
use Plac\Installation;

class InstallationController extends Controller {

    public static function generateUniqueId() {
        $idGenerated = HelperIDs::generateID();
        $count = Installation::where('installation_id', $idGenerated)->count();
        if ($count == 1) {
            InstallationController::generateUniqueId();
        } else {
            return $idGenerated;
        }
    }

     public static function getDeviceToken($profileToId) {
       return  Installation::join('plac_users', 'installations.plac_user_id', '=', 'plac_users.plac_user_id')
                        ->join("profiles", "plac_users.plac_user_id", "=", "profiles.plac_user_id")
                        ->where("profiles.profile_id", $profileToId)->orderBy('installations.created_at', 'desc')->pluck("device_token")->first();
    }

    public function store($deviceInfo, $placUserId) {


        if (isset($deviceInfo['device_token'])) {
            $count = $this->checkInstallationExist($deviceInfo['device_token']);
            if ($count == 1) {
                return true;
            } else {
                 $installation = new \Plac\Installation();
                $installation->installation_id = $this->generateUniqueId();
                $installation->app_version = $deviceInfo['app_version'];
                $installation->so_name = $deviceInfo['so_name'];
                $installation->so_version = $deviceInfo['so_version'];
                $installation->so_version_release = $deviceInfo['so_version'];
                $installation->device_token = $deviceInfo['device_token'];
                $installation->device_locale = $deviceInfo['device_locale'];
                $installation->device_country = $deviceInfo['device_locale'];
                $installation->plac_user_id = $placUserId;
                $installation->save();
                return true;
            }
        } else {
            return true;
        }
    }

    public function checkInstallationExist($deviceToken) {
        $installation = Installation::where("device_token", $deviceToken)->where("plac_user_id",$deviceToken)->count();
        return $installation;
    }

}

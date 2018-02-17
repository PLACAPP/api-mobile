<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Plac\Helpers;
use Plac\Http\Controllers\ProfileController;

/**
 * Description of Notifications
 *
 * @author carlosarango
 */
class Notifications {

    public  static function getNotificationData($profileFromId, $profileToId) {
        $profileController = new ProfileController();
        $profileFrom = $profileController->getProfileFrom($profileFromId, $profileToId);
        $imageFrom = "";
        $nameFrom = "";
        $iconTitle = "";
        if ($profileFrom->profile_type == "pet") {
            $nameFrom = $profileFrom->placuser->plac_user_name;
            $imageFrom = $profileFrom->placuser->plac_user_image;
            if ($imageFrom == "") {
                $imageFrom = "http://res.cloudinary.com/plac/image/upload/v1487177605/placusers/image_empty.png";
            }
            switch ($profileFrom->pet_type) {
                case "DOG":
                    $iconTitle = "🐶";
                    break;
                case "CAT":
                    $iconTitle = "🐱";
                    break;
                case "MINIPIG":
                    $iconTitle = "🐱";
                    break;
                case "RABBIT":
                    $iconTitle = "🐷";
                    break;
                case "HAMSTER":
                    $iconTitle = "🐹";
                    break;
            }
        } else {
            $nameFrom = $profileFrom->profile_name;
            $imageFrom = $profileFrom->profile_image;
            if ($imageFrom == "") {
                $imageFrom = "http://res.cloudinary.com/plac/image/upload/v1487177605/placusers/image_empty.png";
            }
        }
        return array("imageFrom" => $imageFrom, "nameFrom" => $nameFrom, "iconTitle" => $iconTitle);
    }

}

<?php

namespace Plac\Http\Controllers;

use Plac\NotificationsPlace;
use Plac\Helpers\HelperIDs;

class NotificationsOnsignalController extends Controller {
    
    /* Save a new notification */

    public function saveNotification($url, $type, $title, $content, $place_id) {

        $saleNotificationPlace = new NotificationsPlace();

        $saleNotificationPlace->notification_place_id = $this->generateUniqueNotificationId();
        $saleNotificationPlace->notification_type = $type;
        $saleNotificationPlace->notification_url = $url;
        $saleNotificationPlace->notification_title = $title;
        $saleNotificationPlace->notification_content = $content;
        $saleNotificationPlace->place_id = $place_id;

        $saleNotificationPlace->save();
    }

    /* Generate a unique of notifications */

    public function generateUniqueNotificationId() {
        $idGenerated = HelperIDs::generateID();
        $count_exist_id_product = NotificationsPlace::where('notification_place_id', $idGenerated)->count();

        if ($count_exist_id_product == 1) {
            $this->generateUniqueNotificationId();
        } else {
            return $idGenerated;
        }
    }

}

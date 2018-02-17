<?php

namespace Plac\Http\Controllers;

use Plac\Helpers\HelperIDs;
use Plac\DeviceSession;
use Plac\NotificationsPlace;
use Mail;
use Plac\Helpers\OneSignal;

class NotificationStoreController extends Controller {
    /* Save a new notification */

    public static function saveNotification($url, $type, $title, $content, $place_id) {

        $saleNotificationPlace = new NotificationsPlace();
        $saleNotificationPlace->notification_place_id = self::generateUniqueNotificationId();
        $saleNotificationPlace->notification_type = $type;
        $saleNotificationPlace->notification_url = $url;
        $saleNotificationPlace->notification_title = $title;
        $saleNotificationPlace->notification_content = $content;
        $saleNotificationPlace->place_id = $place_id;
        $saleNotificationPlace->save();

        return $saleNotificationPlace;
    }

    public static function sendNotificationToPlace($placeId, $url, $title, $content) {
        $fields = array(
            'include_player_ids' => self::getDeviceSession($placeId),
            'data' => array("foo" => "bar"),
            'headings' => array("en" => $title, "es" => $title),
            'url' => $url,
            'contents' => array("en" => $content, "es" => $content)
        );

        return OneSignal::sendNotification($fields, 'COMPANY');
    }

    public static function sendEmailNewOrder($emailFor,$to, $ti, $mes, $ord) {

        $data = [
            'emailFor'=>$emailFor,
            'order_title' => $ti,
            'order_message' => $mes,
            'order' => $ord,
        ];
        $subject = "Nueva orden #" . $ord->order_id;
        Mail::send('emails.store.order.newOrderUser', $data, function($mensaje) use($subject, $to) {
            $mensaje->from("tienda@placapp.com", 'PLAC TIENDA');
            $mensaje->to($to);
            $mensaje->subject($subject);
        });
    }

    public static function sendEmailNewQuestionToSeller($to, $questionIn) {
        $product = $questionIn['product'];
        $productName = $product['product_name'];

        $data = [
            'question' => $questionIn,
        ];
        $subject = "Nueva pregunta sobre " . $productName;
        Mail::send('emails.store.question.index', $data, function($mensaje) use($subject, $to) {
            $mensaje->from("tienda@placapp.com", 'PLAC TIENDA');
            $mensaje->to($to);
            $mensaje->subject($subject);
        });
    }

    public static function sendEmailToPlaceLackOfStock($productOrdered) {

        $messageViewEmail = "Un cordial saludo empresa " . $productOrdered->place_location_name . ". Le notificamos que el producto " . $productOrdered->product_name . " no cuenta con Stock, lo invitamos a que actualice este producto.";
        $data = [
            "messageViewEmail" => $messageViewEmail,
            "product_id" => $productOrdered->product_id,
            "stock" => $productOrdered->product_stock,
            "productImageMain" => $productOrdered->product_image_main,
            "productName" => $productOrdered->product_name,
        ];

        $subject = "El stock de uno de sus productos ha llegado a cero.";

        Mail::send('emails.buyProduct.index', $data, function($mensaje) use($subject, $productOrdered) {
            $mensaje->from("PLAC TIENDA<tienda@placapp.com>");
            $mensaje->to($productOrdered->place_location->place->place_email);
            $mensaje->subject($subject . " - " . $productOrdered->product_name);
        });
    }

    /* Generate a unique of notifications */

    public static function generateUniqueNotificationId() {
        $idGenerated = HelperIDs::generateID();
        $count = NotificationsPlace::where('notification_place_id', $idGenerated)->count();
        if ($count == 1) {
            self::generateUniqueNotificationId();
        } else {
            return $idGenerated;
        }
    }

    public static function getDeviceSession($placeId) {
        $deviceSession = DeviceSession::where("place_id", $placeId)->get();
        $arrayDeviceSession = [];
        $count = 0;
        foreach ($deviceSession as $deviceSessionObject) {
            $arrayDeviceSession[$count++] = $deviceSessionObject->device_player_id;
        }
        return $arrayDeviceSession;
    }

}

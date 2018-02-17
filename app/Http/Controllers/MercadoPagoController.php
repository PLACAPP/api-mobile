<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Http\Requests;

class MercadoPagoController extends Controller {

    public static function getPreferenceId($productOrdered, $orderCustomer, $order) {

        $orderResumed = $order['order_resumed'];
        //GET  ACCESS_TOKEN_SELLER MERCADO PAGO FROM SELLER(PLACE IN PLAC)
        $place = $productOrdered['place_location']['place'];
        $authMercadoPago = json_decode($place['place_auth_mercadopago'], true);
        $ACCESS_TOKEN_SELLER = $authMercadoPago['response']['access_token'];
        //GET INFO PAYER
        $placUserCustomer = $orderCustomer['plac_user_shipping_address'];
        $payer = array('name' => $placUserCustomer['plac_user_name'], "email" => $placUserCustomer['plac_user_email'],);

        //CREATE MERCADO PAGO PREFERENCE PAY
        $mp = new \Plac\Helpers\MercadoPago\MercadoPago($ACCESS_TOKEN_SELLER); // seller access_token
        $priceUnit = $orderResumed['price_unit'];
        $quantity = $orderResumed['quantity'];
        $priceDelivery = $orderResumed['price_send'];
        $shippingsService = json_decode($productOrdered['shipping_service'], true);
        $messageAdditional = "";
        $shippingPriceInComission = 0;
        if ($shippingsService) {
            if (count($shippingsService) > 0) {
                $shippingService = $shippingsService[0];
                if ($shippingService['service'] == 'mensappjeros') {
                    $messageAdditional = 'Mensappjeros';
                    $shippingPriceInComission = $priceDelivery;
                }
            }
        }
        $commissionForSale = (8 / 100);
        $saleCommission = (($priceUnit * $quantity) * $commissionForSale) + $shippingPriceInComission;

        $preference_data = array(
            "items" => array(
                array(
                    "id" => $productOrdered['product_id'],
                    "title" => $productOrdered['product_name'] . ' #' . $order['order_id'],
                    "description" => $productOrdered['product_description'],
                    "quantity" => $quantity,
                    "unit_price" => $priceUnit,
                    "currency_id" => "COP",
                    "picture_url" => $productOrdered['product_images'][0]['url'],
                )
            ),
            'marketplace_fee' => $saleCommission, // fee to collect,
            'shipments' =>
            array(
                'mode' => 'not_specified',
                'cost' => $priceDelivery,
            ),
            'external_reference' => $order['order_id'],
            'payer' => $payer,
            'notification_url' => 'https://www.placapp.com/mercadopago/notifications'
        );

        $preference = $mp->create_preference($preference_data);


        return $preference;
    }

    public static function getPreference($orderResumed, $order, $place, $placUserShippingAddress) {

        $authMercadoPago = json_decode($place->place_auth_mercadopago, true);
        $ACCESS_TOKEN_SELLER = $authMercadoPago['response']['access_token'];
        //GET INFO PAYER

        $payer = array('name' => $placUserShippingAddress['plac_user_name'], "email" => $placUserShippingAddress['plac_user_email'],);

        //CREATE MERCADO PAGO PREFERENCE PAY
        $mp = new \Plac\Helpers\MercadoPago\MercadoPago($ACCESS_TOKEN_SELLER); // seller access_token
        $total = $orderResumed['subTotal'];
        $shippingPrice = $orderResumed['shippingPrice'];

        $commissionForSale = (2 / 100);
        $saleCommission = $commissionForSale * $total;



        $preference_data = array(
            "items" => array(
                array(
                    "title" => 'Orden de compra  #' . $order->order_id,
                    "description" => 'Orden generada '. $order->created_at,
                    "quantity" => 1,
                    "unit_price" => $total,
                    "currency_id" => "COP",
                    "picture_url" => $place->path_image_logo,
                ),
        
            ),
            'marketplace_fee' => $saleCommission, // fee to collect,
            'shipments' =>
            array(
                'mode' => 'not_specified',
                'cost' => $shippingPrice,
            ),
            'external_reference' => $order->order_id,
            'payer' => $payer,
            'notification_url' => 'https://www.placapp.com/mercadopago/notifications'
        );

        $preference = $mp->create_preference($preference_data);


        return $preference;
    }

}

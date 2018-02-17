<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Product;
use Plac\Helpers\HelperIDs;
use Plac\Sales;
use Plac\SalesDetails;
use Plac\Helpers\JsonObjects;
use Plac\Billing;
use DB;

class StoreController extends Controller {

// Generate order of buy

    public function generateOrder(Request $request) {

        if (config("app.key") == $request->header("access-key")) {
            $order = $request->order;
            $orderArray = json_decode(\Plac\Helpers\Encrypt::decodedDataApp($order), true);
            $productOrderId = $orderArray["product_id"];
            $quantity = $orderArray["quantity"];
            $productOrdered = Product::where('product_id', $productOrderId)->with('placeLocation.place')->first();
            $placeId = $productOrdered->place_id;
            $billingPlace = $this->billingPlaceActive($placeId);


            if ($productOrdered != null && $billingPlace != null) {
                $check = $this->checkProductStock($quantity, $productOrdered, $placeId);
                if (!$check) {
                    return JsonObjects::responseJsonObject("store", "quantity_unvailable", $productOrdered, "El inventario de este producto no tiene la cantidad que solicitaste");
                }
                //SAVE ORDER
                $sale = $this->setOrder($productOrdered, $orderArray);
                /* Save detail of the sale */
                $saleDetails = $this->setOrderDetails($orderArray, $sale);

                // DISCOUNT VALUE TO PLACE FOR THE NEW ORDER
                $billingPlace->billing_value_balance -= $this->getValueForDiscount($billingPlace);
                $billingPlace->save();

                /* Send of email for the company and the user by the buy of a productOrdered */
                NotificationStoreController::sendEmailNewOrder($sale, $saleDetails, $productOrdered);

                /* Calculate -> Subtract stock of productOrdered, price with discount and the price end of the sale */
                $productOrdered->product_stock -= $quantity;
                $productOrdered->save();

                /* ------ Save notification ------ */
                $url = \Plac\Helpers\Environment::getServerNameMainCurrentEnvironment() . "ventas/" . $sale->sale_id;
                $type = "orders";
                $title = "Solicitud de compra";
                $content = "Confirmación de compra del producto " . $productOrdered->product_name;
                NotificationStoreController::saveNotification($url, $type, $title, $content, $placeId);
                //SEND NOTIFICATION TO SELLER(PLACE)
                NotificationStoreController::sendNotificationToPlace($placeId, $url, 'Nueva solicitud de compra');

                return JsonObjects::responseJsonObject("store", "order_made", $sale, "La orden del producto fue hecha");
            } else {
                return JsonObjects::responseJsonObject("store", "product_unvailable", $productOrdered, "El producto ha sido deshabilitado o eliminado por el vendedor");
            }
        } else {
            return JsonObjects::responseJsonObject("store", "access_prohibited", null, "Algo va mal con tu petición, ve fuera ");
        }
    }

    /* Generate a unique of sales of identification */

    public function generateUniqueId() {
        $idGenerated = HelperIDs::generateID();
        $count_exist_id_product = Sales::where('sale_id', $idGenerated)->count();

        if ($count_exist_id_product == 1) {
            $this->generateUniqueId();
        } else {
            return $idGenerated;
        }
    }

    /* CHECK BILLING PLACE  IS ACTIVE */

    public function billingPlaceActive($place_id) {
        return Billing::where("place_id", $place_id)->where("billing_state", "APPROVED")->where("billing_plan_use", 1)->first();
    }

    public function setOrder($productOrdered, $order) {

        $sale = new Sales();
        $sale->sale_id = $this->generateUniqueId();
        $sale->sale_price = $productOrdered->product_price;
        $sale->sale_quantity = $order["quantity"];
        $sale->sale_iva = $productOrdered->product_tax;
        $sale->sale_price_send = $productOrdered->product_value_send;
        $sale->sale_type = $productOrdered->product_type;
        $sale->sale_note = $order["sale_note"];

        $productValueTax = $productOrdered->product_unit_price + ($productOrdered->product_unit_price * doubleval((0) . "." . $productOrdered->product_tax));
        $sale->sale_price_discount = $productValueTax * doubleval((0) . "." . $productOrdered->product_discount_rate);
        $sale->sale_price_end = $productOrdered->product_price * $order["quantity"];

        $sale->plac_user_id = $order["user_id"];
        $sale->place_id = $productOrdered->place_id;
        $sale->sale_product_id = $productOrdered->product_id;
        $sale->plac_user_shipping_address_id = $order["plac_user_shipping_address_id"];
        $sale->save();
        return $sale;
    }

    public function setOrderDetails($order, $sale) {

        $saleDetails = new SalesDetails();
        $saleDetails->sale_detail_id = $this->generateUniqueId();
        $saleDetails->sale_name_user = $order["name_user"];
        $saleDetails->sale_detail_address = $order["address"];
        $saleDetails->sale_city = $order["city"]['city_name'];
        $saleDetails->sale_detail_phone = $order["telephone"];
        $saleDetails->sale_detail_email = $order["email"];
        $saleDetails->sale_id = $sale->sale_id;
        $saleDetails->save();
        return $saleDetails;
    }

    public function checkProductStock($quantityOrder, $productOrdered, $placeId) {
        $productStock = $productOrdered->product_stock;
        $check = true;
        if ($quantityOrder > $productStock) {
            $check = false;
        }
        if ($productStock == 1 || ($productStock - $quantityOrder) == 0) {
            /* ------ Save notification ------ */
            $url = \Plac\Helpers\Environment::getServerNameMainCurrentEnvironment() . "productos/" . $productOrdered->product_id;
            $type = "stockzero";
            $title = "Inventario en cero";
            $content = "El inventario del producto " . $productOrdered->product_name . " ha llegado a cero.";
            NotificationStoreController::saveNotification($url, $type, $title, $content, $placeId);
            NotificationStoreController::sendNotificationToPlace($placeId, $url, 'El stock de su producto ha llegado a cero.');
            /* Send of email for the company by the stock of a productOrdered in zero */
        }
        return $check;
    }

    public function getOrdersUser($placUserId) {
        $orders = Order::where("plac_user_id", $placUserId)
                ->with('assessment', 'product.placeLocation', 'shippingAddress.city')
                ->latest()
                ->paginate(20);
        return $orders;
    }

    public function getValueForDiscount($billingPlace) {
        $namePlan = explode("-", $billingPlace->billing_description);
        $valueDiscount = 0;
        switch ($namePlan[0]) {
            case "Plan Cachorro":
                $valueDiscount = 4000;
                break;
            case "Plan Travieso":
                $valueDiscount = 3000;
                break;
            case "Plan Manada":
                $valueDiscount = 2000;
                break;
        }
        return $valueDiscount;
    }

    public function getStoresByCity(Request $request) {
        $cityId = $request->cityId;

        $placeLocations = \Plac\Place::join('place_locations', 'places.place_id', '=', 'place_locations.place_id')
                ->join('products', 'products.place_id', '=', 'places.place_id')
                ->join('store_configuration', 'places.store_configuration_id', '=', 'store_configuration.store_configuration_id')
                ->select(DB::raw('COUNT(products.product_id) as count'), 'places.place_id', 'place_locations.place_location_name','place_locations.place_location_id','place_locations.path_image1','places.path_image_logo', 'store_configuration.*')
                ->where('place_locations.isMain_location', 1)
                ->where('places.path_image_logo',"!=","")
                ->where('products.product_state', 'ACTIVE')
                ->where('places.store_configuration_id','!=', "")
                ->whereRaw('places.place_auth_mercadopago != ""')
                ->whereRaw("store_configuration.shipping_prices like '%$cityId%' ")
                ->distinct()
                ->groupBy('places.place_id')
                ->orderBy('count', 'desc')
                ->get();
        return $placeLocations;
    }

}

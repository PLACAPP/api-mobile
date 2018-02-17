<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Helpers\JsonObjects;
use Plac\Helpers\Encrypt;
use Plac\Helpers\HelperIDs;
use Plac\Order;
use DB;
use Carbon\Carbon;

class OrdersController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
//
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
//
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        if (config("app.key") == $request->header("access-key")) {

            $orderCustomer = $request->order;

            $orderCustomerArray = json_decode(Encrypt::decodedDataApp($orderCustomer), true);
            if (isset($request->appVersion)) {
                if ($request->appVersion === 2.4) {
                    return $this->storeOrder($orderCustomerArray);
                }
            }


            $productOrdered = $orderCustomerArray['product'];
            $productId = $productOrdered['product_id'];


            $quantityOrder = $orderCustomerArray['quantity'];
            $productController = new ProductsController();
            $check = $productController->checkStock($quantityOrder, $productOrdered);
            if (!$check) {
                return JsonObjects::responseJsonObject("store", "quantity_unvailable", $productOrdered, "El inventario de este producto no tiene la cantidad que solicitaste");
            }

            $order = new Order();
            $order->order_id = $this->generateUniqueId();
            $order->order_price = $productOrdered['product_price'];
            $order->order_quantity = $quantityOrder;
            $order->order_discount_rate = $productOrdered['product_discount_rate'];
            $order->order_state = 'interesting';
            $order->order_note = $orderCustomerArray['sale_note'];
            $order->order_delivery_product = json_encode($productOrdered['product_delivery']);
            $order->order_delivery_customer = json_encode($orderCustomerArray['delivery_customer']);
            $order->product_id = $productOrdered['product_id'];
            $order->place_id = $productOrdered['place_location']['place_location_id'];
            $order->plac_user_id = $orderCustomerArray['plac_user_shipping_address']['plac_user_id'];
            $order->order_notified_pending = 0;
            $placUserShippingAddress = $orderCustomerArray['plac_user_shipping_address'];
            if ($placUserShippingAddress != null) {
                $order->plac_user_shipping_address_id = $placUserShippingAddress['plac_user_shipping_address_id'];
            }
            $order->save();
            $order['order_delivery_customer'] = json_decode($order->order_delivery_customer, true);
            $order = $this->getOrderResumed($order, 'init');
            $preferenceMercadoPago = MercadoPagoController::getPreferenceId($productOrdered, $orderCustomerArray, $order);
            $order['order_preference_mercadopago'] = $preferenceMercadoPago;


            return JsonObjects::responseJsonObject("store", "order_made", $order, "La orden del producto fue hecha");
        } else {
            return JsonObjects::responseJsonObject("store", "access_prohibited", null, "Algo va mal con tu petición, adios");
        }
    }

    public function storeOrder($orderCustomerArray) {
        $orderDetails = $orderCustomerArray['orderDetails'];
        $orderResumed = $orderCustomerArray['orderResumed'];
        $placUserShippingAddress = $orderCustomerArray['placUserShippingAddress'];
        $placeId = $orderDetails[0]['place_id'];
        $order = new \Plac\OrderUser();
        $order->order_id = $this->generateUniqueId();
        $order->place_id = $placeId;
        $order->order_payment_full = $orderResumed['total'];
        $order->order_shipping_price = $orderResumed['shippingPrice'];
        $order->plac_user_shipping_address_id = $placUserShippingAddress['plac_user_shipping_address_id'];
        $order->save();

        $place = \Plac\Place::find($placeId);

        foreach ($orderDetails as $orderDetail) {
            $productId = $orderDetail['product_id'];
            $quantity = $orderDetail['quantity'];
            $orderDetailM = new \Plac\OrderDetail();
            $orderDetailM->order_detail_id = $this->generateUniqueId();
            $orderDetailM->order_price = $orderDetail['price'];
            $orderDetailM->order_quantity = $quantity;
            $orderDetailM->order_discount = $orderDetail['discount'];
            $orderDetailM->order_total = $orderDetail['total'];
            $orderDetailM->product_id = $productId;
            $orderDetailM->order_id = $order->order_id;
            ProductsController::updateProductStock($productId, $quantity);
            $orderDetailM->save();
        }

        $orderMP = MercadoPagoController::getPreference($orderResumed, $order, $place, $placUserShippingAddress);
        return JsonObjects::responseJsonObject("store", "order_made", $orderMP, "La orden del producto fue hecha");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
//
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
//
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
//
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
//
    }

    public function getOrdersUser($placUserId, $state = 'pending') {
        $orders = Order::where("plac_user_id", $placUserId)
                ->where('order_state', $state)
                ->with('assessment', 'product.placeLocation', 'placeLocation.place', 'shippingAddress.city')
                ->latest()
                ->paginate(10);

        $orders = $this->getOrderMercadoPago($orders);
        return $this->getOrdersColumnsDecode($orders);
    }

    public function getOrderMercadoPago($orders) {

        foreach ($orders as $order) {
            $externalReference = $order->order_id;
            $mercadoPago = [];
            $placeAuthMercadoPago = json_decode($order->placeLocation->place->place_auth_mercadopago, true);
            $ACCES_TOKEN = $placeAuthMercadoPago['response']['access_token'];
            $mp = new \Plac\Helpers\MercadoPago\MercadoPago($ACCES_TOKEN);
            $filters = array("external_reference" => $externalReference);
            $search = $mp->search_payment($filters, 0, 1);
            $results = $search['response']['results'];
            if (count($results) > 0) {
                $mercadoPago = $results[0]['collection'];
            }
            $order->mercado_pago = $mercadoPago;
        }
        return $orders;
    }

    public function updateOrderStateLastModified() {

        $filters = array(
            "site_id" => 'MCO',
            "range" => "last_modified",
            "begin_date" => "NOW-6MINUTES",
            "end_date" => "NOW"
        );
        $this->manageOrderUpdate($filters);
        return "update order state last modified";
    }

    public function manageOrderUpdate($filters) {
        $places = \Plac\Place::where('place_auth_mercadopago', '!=', "")->get();
        foreach ($places as $place) {
            $placeAuthMercadoPago = json_decode($place->place_auth_mercadopago, true);
            if ($placeAuthMercadoPago) {
                $ACCES_TOKEN = $placeAuthMercadoPago['response']['access_token'];
                $mp = new \Plac\Helpers\MercadoPago\MercadoPago($ACCES_TOKEN);
                $response = $mp->search_payment($filters, 0, 1000);
                $results = $response['response']['results'];
                if (count($results) > 0) {
                    foreach ($results as $result) {
                        $orderMP = $result['collection'];
                        $this->updateOrder($orderMP);
                    }
                }
            }
        }
    }

    private function updateOrder($orderMP) {
        $reference = $orderMP['external_reference'];
        $orderMain = Order::find($reference);
        $statusMP = $orderMP ['status'];
        $orderPaymentPendingNotification = $orderMain->payment_pending_notification;
        $orderPaymentApprovedNotification = $orderMain->payment_approved_notification;
        if ($orderPaymentPendingNotification == 0) {
            $reference = $orderMP['external_reference'];

            $order = DB::table(DB::raw('orders ord'))
                    ->join(DB::raw('places pl'), 'ord.place_id', '=', 'pl.place_id')
                    ->join(DB::raw('place_locations plo'), 'pl.place_id', '=', 'plo.place_id')
                    ->join(DB::raw('plac_user_shipping_address pusa'), 'ord.plac_user_shipping_address_id', '=', 'pusa.plac_user_shipping_address_id')
                    ->join(DB::raw('cities ci'), 'pusa.city_id', '=', 'ci.city_id')
                    ->join(DB::raw('store_configuration sc'), 'pl.store_configuration_id', '=', 'sc.store_configuration_id')
                    ->select('ord.order_id', 'ord.place_id', 'order_shipping_price', 'order_note', 'ord.order_payment_method', 'ord.order_payment_type', 'ord.discount_coupon_id', 'ord.order_payment_full', 'ord.order_payment_status', 'pusa.plac_user_name', 'pusa.plac_user_telephone', 'pusa.plac_user_email', 'pusa.plac_user_neighborhood', 'pusa.plac_user_address', 'pusa.plac_user_additional_info', 'pl.place_email', 'plo.place_location_name', 'sc.shipping_prices', 'sc.minimum_price_free_shipping', 'sc.delivery_schedules', 'sc.delivery_time', 'ci.city_name', 'ord.created_at')
                    ->where('ord.order_id', $reference)
                    ->where('plo.isMain_location', 1)
                    ->first();
            $orderDetails = DB::table(DB::raw('order_details ordet'))
                    ->join(DB::raw('products pro'), 'ordet.product_id', '=', 'pro.product_id')
                    ->select(DB::raw('ordet.*'), 'pro.product_name', 'pro.product_images')
                    ->where('ordet.order_id', $order->order_id)
                    ->get();

            $order->order_details = $orderDetails;
            $mercadoPago = array("order" => $orderMP, "payment_info" => $this->getOrderPaymentInfoMP($orderMP), "status" => $this->getOrderStatusMP($orderMP));
            $order->mercado_pago = $mercadoPago;
            $order->order_resumed = $this->getOrderResumed($order);
            $this->sendNotificationPlaceNewOrder($order);
            $this->sendEmailsNewOrder($order);
            $orderMain->payment_pending_notification = 1;
        }
        if ($orderPaymentApprovedNotification == 0) {
            if ($statusMP == "approved") {
                $orderMain->payment_approved_notification = 1;
                $orderMain->order_payment_date = Carbon::now();
            }
        }

        $orderMain->order_payment_type = $orderMP['payment_type'];
        $orderMain->order_payment_method = $orderMP['payment_method_id'];
        $orderMain->order_payment_status = $statusMP;
        $orderMain->save();
    }

    private function getOrderResumed($order) {

        $minimumPrice = $order->minimum_price_free_shipping;
        $shippingPrices = $order->shipping_prices;
        $orderResumed = ["total" => 0, "subtotal" => 0, "envio" => 0];
        $orderDetails = $order->order_details;
        $subTotal = 0;
        foreach ($orderDetails as $orderDetail) {
            $subTotal = $orderDetail->order_total + $subTotal;
        }

        $orderResumed['subtotal'] = $subTotal;
        $minimumPrice = json_decode($minimumPrice);
        $shippingPrice = 0;
        if ($subTotal <= $minimumPrice->price) {
            $shippingPrices = json_decode($shippingPrices);
            $shippingPrice = $shippingPrices[0]->price;
        }
        $orderResumed['envio'] = $shippingPrice;

        $orderResumed['total'] = $subTotal + $shippingPrice;


        return $orderResumed;
    }

    private function getOrderPaymentInfoMP($orderMP) {
        //payment type (ticket, bank_transfer, account_money, debit_card, prepaid_card, credit_card)
        $paymentType = $orderMP['payment_type'];
        //payment method id (efecty, davivienda,etc)
        $paymentMethodId = $orderMP['payment_method_id'];

        switch ($paymentType) {
            case 'account_money':
                $paymentType = 'Dinero en cuenta de mercado pago';
                break;
            case 'ticket':
                $paymentType = 'Codigo impreso';
                break;
            case 'bank_transfer':
                $paymentType = 'Transferencia bancaria';
                break;
            case 'credit_card':
                $paymentType = 'Tarjeta de credito';
                break;
            case 'debit_card':
                $paymentType = 'Tarjeta de debito';
                break;
            case 'prepaid_card':
                $paymentType = 'Tarjeta prepagada';
                break;
        }
        return array("payment_type" => $paymentType, "payment_method" => $paymentMethodId);
    }

    private function getOrderStatusMP($orderMP) {
        $status = $orderMP['status'];
        switch ($status) {
            case 'pending':
                $status = 'Pendiente de pago';
                break;
            case 'approved':
                $status = 'Aprobado';
                break;
            case 'in_process':
                $status = 'Revisando';
                break;
            case 'cancelled':
                $status = 'Cancelado';
                break;
            case 'refunded':
                $status = 'Reembolsada';
                break;
        }

        return $status;
    }

    public function sendEmailsNewOrder($order) {

        $sellerName = $order->place_location_name;
        $sellerEmail = $order->place_email;

        $buyerName = $order->plac_user_name;
        $buyerEmail = $order->plac_user_email;

//SEND TO BUYER
        $titleBuyer = 'Gracias por tu orden!';
        $messageBuyer = 'Tu orden ha sido recibida, ' . $sellerName . '(vendedor) enviara '
                . 'el producto cuando el pago haya sido realizado.';
        NotificationStoreController::sendEmailNewOrder('buyer', $buyerEmail, $titleBuyer, $messageBuyer, $order);

//SEND TO SELLER
        $titleSeller = "Nueva orden de compra!";
        $messageSeller = $buyerName . ' ha hecho una orden en tu tienda, espera a que se acredite el pago para hacer la entrega.';
        NotificationStoreController::sendEmailNewOrder('seller', $sellerEmail, $titleSeller, $messageSeller, $order);
    }

    public function sendNotificationPlaceNewOrder($order) {

        $orderId = $order->order_id;
        $placeId = $order->place_id;
        $placUserName = $order->plac_user_name;
        $url = \Plac\Helpers\Environment::getServerNameMainCurrentEnvironment() . "ordenes/" . $orderId;
        $title = 'Nueva orden de compra ' . $orderId;
        $content = $placUserName . " ha hecho una orden en tu tienda #";
        NotificationStoreController::sendNotificationToPlace($placeId, $url, $title, $content);
        $type = "orden";
        NotificationStoreController::saveNotification($url, $type, $title, $content, $placeId);
    }

    public function generateUniqueId() {
        $idGenerated = HelperIDs::generateID();
        $count_exist_id = Order::where('order_id', $idGenerated)->count();
        if ($count_exist_id == 1) {
            $this->generateUniqueId();
        } else {
            return $idGenerated;
        }
    }

    public function getTemplate($order) {
        return view('emails.store.order.newOrder')->with(compact('order', $order))->with('emailFor', 'buyers');
    }

}

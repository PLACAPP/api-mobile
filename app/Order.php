<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class Order extends Model {

    public $timestamps = true;
    protected $primaryKey = 'order_id';
    protected $table = "orders";
    public $incrementing = false;

    public function assessment() {
        return $this->hasOne('Plac\Assessment', 'order_id', 'order_id');
    }

    public function shippingAddress() {
        return $this->belongsTo('Plac\PlacUserShippingAddress', 'plac_user_shipping_address_id', 'plac_user_shipping_address_id');
    }

    public function placeLocation() {
        return $this->belongsTo('Plac\PlaceLocation', 'place_id', 'place_location_id');
    }

    public function orderDetail() {
        return $this->hasMany('Plac\OrderDetail', 'order_id', 'order_id');
    }

}

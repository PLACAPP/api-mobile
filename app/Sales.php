<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class Sales extends Model {

    protected $table = 'sales';
    protected $primaryKey = 'sale_id';
    public $incrementing = false;
    public $timestamps = true;

    public function placeLocation() {
        return $this->belongsTo('Plac\PlaceLocation', 'place_id', 'place_location_id');
    }

    public function product() {
        return $this->belongsTo('Plac\Product', 'sale_product_id', 'product_id');
    }

    public function assessment() {
        return $this->hasOne('Plac\Assessment', 'sale_id', 'sale_id');
    }

    public function shippingAddress() {
        return $this->belongsTo('Plac\PlacUserShippingAddress', 'plac_user_shipping_address_id', 'plac_user_shipping_address_id');
    }

}

<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class Place extends Model {

    protected $table = 'places';
    protected $primaryKey = 'place_id';
    public $incrementing = false;
    protected $hidden = array('encrypted_password', 'salt', "confirmation_code", "remember_token");

    public function placeLocations() {
        return $this->hasMany('Plac\PlaceLocation', 'place_id', 'place_location_id');
    }
    
     public function storeConfiguration() {
        return $this->belongsTo('Plac\StoreConfiguration', 'store_configuration_id', 'store_configuration_id');
    }

}

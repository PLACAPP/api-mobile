<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class PlaceLocation extends Model {

    protected $table = 'place_locations';
    protected $primaryKey = 'place_location_id';
    public $incrementing = false;

    public function city() {
        return $this->belongsTo('Plac\City', 'city_id', 'city_id');
    }

    public function place() {
        return $this->belongsTo('Plac\Place', 'place_id', 'place_id');
    }

}

<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $table = 'campaign';
    protected $primaryKey = 'campaign_id';
    public $incrementing = false;

    public function place()
    {
        return $this->belongsTo('Plac\PlaceLocation', 'place_id', 'place_location_id');
    }
    
    
}

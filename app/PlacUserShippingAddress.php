<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class PlacUserShippingAddress extends Model
{
   
    protected $primaryKey = 'plac_user_shipping_address_id';
    protected $table = "plac_user_shipping_address";
    public $incrementing = false;
    public $timestamps = true;
    
     public function city()
    {
        return $this->belongsTo('Plac\City','city_id','city_id');
    }
    
    
   

}

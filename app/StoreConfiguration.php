<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class StoreConfiguration extends Model
{
     protected $table = 'store_configuration';
    protected $primaryKey = 'store_configuration_id';
    public $incrementing = false;
    public $timestamps = true;

   

}

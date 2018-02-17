<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class OrderUser extends Model {

    public $timestamps = true;
    protected $primaryKey = 'order_id';
    protected $table = "orders";
    public $incrementing = false;

}

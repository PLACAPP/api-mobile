<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model {

    public $timestamps = true;
    protected $primaryKey = 'order_detail_id';
    protected $table = "order_details";
    public $incrementing = false;

}

<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class SalesDetails extends Model {

    protected $table = 'sales_details';
    protected $primaryKey = 'sale_detail_id';
    public $incrementing = false;
    public $timestamps = false;

}

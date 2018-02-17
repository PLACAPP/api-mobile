<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class ProductUserView extends Model {

    protected $table = 'product_user_views';
    protected $primaryKey = 'product_user_views_id';
    public $incrementing = false;

}

<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class Product_Categories extends Model {

    protected $table = 'product_categories';
    protected $primaryKey = 'category_id';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'category_name',
        'product_type',
        'category_description'
    ];

}

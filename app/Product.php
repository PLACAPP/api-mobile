<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class Product extends Model {

    protected $table = 'products';
    protected $primaryKey = 'product_id';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'product_name',
        'product_description_long',
        'product_description_short',
        'product_categories',
        'product_type',
        'product_target',
        'product_price',
        'product_stock',
        'product_discount_rate',
        'product_tax',
        'product_image_main',
        'product_image2',
        'product_image3',
        'product_state',
        'place_id',
        'updated_at',
        'created_at'
    ];

    public function placeLocation() {
        return $this->belongsTo('Plac\PlaceLocation', 'place_id', 'place_id');
    }

    public function questions() {
        return $this->hasMany('Plac\ProductQuestion', 'product_id', 'product_id');
    }

}

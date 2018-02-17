<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class PlaceSubCategories extends Model
{
    
     protected $table='place_subcategories';
    protected $primaryKey='subcategory_id';
     public $incrementing = false;
     
     
      public function category() {
        return $this->belongsTo('Plac\PlaceCategories', 'category_id', 'category_id');
    }
}

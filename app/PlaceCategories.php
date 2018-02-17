<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class PlaceCategories extends Model
{
  protected $table='place_categories';
    protected $primaryKey='categories_id';
     public $incrementing = false;
}

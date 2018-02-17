<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class ProductQuestion extends Model
{
    public $table = "products_questions";
    protected $primaryKey = 'question_id';
    public $incrementing = false;
    public $timestamps = true;
    
    
    public function placUser()
    {
        return $this->belongsTo('Plac\PlacUser', 'plac_user_id', 'plac_user_id');
    }
    
    public function product(){
        return $this->belongsTo('Plac\Product', 'product_id', 'product_id');
    }
    

}

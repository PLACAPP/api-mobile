<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class PlacUserBlackList extends Model
{

    protected $primaryKey = 'plac_user_black_list_id';
    protected $table = "plac_users_black_list";
    public $incrementing = false;
    public $timestamps = false;
    
    
     public function placUserTo()
    {
        return $this->belongsTo('Plac\PlacUser', 'plac_user_to_id', 'plac_user_id');
    }

}

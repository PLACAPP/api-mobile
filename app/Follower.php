<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    public $timestamps = true;
    protected $primaryKey = 'follower_id';
    protected $table = "followers";
    public $incrementing = false;


    public function profileFrom()
    {
        return $this->belongsTo('Plac\Profile', "profile_from_id", 'profile_id');
    }


    public function profileTo()
    {
        return $this->belongsTo('Plac\Profile', "profile_to_id", 'profile_id');
    }

}

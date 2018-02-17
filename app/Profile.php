<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    public $timestamps = true;
    protected $primaryKey = "profile_id";
    public $incrementing = false;

    public function breed()
    {
        return $this->belongsTo('Plac\Breed', "breed_id", 'breed_id');
    }


    public function placUser()
    {
        return $this->belongsTo('Plac\PlacUser', 'plac_user_id', 'plac_user_id');
    }

    public function post()
    {
        return $this->hasMany('Plac\Post', 'profile_id', 'profile_id');
    }
    
  
    
     public function followings()
    {
       return $this->hasMany('Plac\Follower', 'profile_from_id', 'profile_id');
        
         
    }
    
    
    
    
    public function followers()
    {
        return $this->hasMany('Plac\Follower', 'profile_to_id', 'profile_id');
    }
    
    
    
    
}

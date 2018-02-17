<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class PlacUser extends Model
{

    public $timestamps = true;
    protected $primaryKey = 'plac_user_id';
    protected $hidden = ['encrypted_password', 'salt','confirmation_code'];
    protected $table = "plac_users";
    public $incrementing = false;

    public function profiles()
    {
        return $this->hasMany('Plac\Profile', 'plac_user_id', 'plac_user_id');
    }
    
    public function notificationsSetting(){
        return $this->hasOne('Plac\PlacUserNotificationsSettings','plac_user_id','plac_user_id');
    }
    
    public function installation(){
        return $this->hasMany('Plac\Installation','plac_user_id','plac_user_id');
    }

}

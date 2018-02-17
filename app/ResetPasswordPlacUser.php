<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class ResetPasswordPlacUser extends Model
{
    protected $fillable = ['reset_id','email', 'token', 'created_at'];
    public $timestamps = false;
    public $incrementing = true;
    public $table ="reset_password_plac_users";
    protected $primaryKey = 'reset_id';
    
}

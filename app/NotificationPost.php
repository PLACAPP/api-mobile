<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class NotificationPost extends Model
{
    public    $timestamps = true;
    protected $primaryKey = 'notification_post_id';
    protected $table = "notifications_posts";
    
    
    public function profileFrom()
    {
       return $this->belongsTo('Plac\Profile', 'profile_from', 'profile_id');
    }
    
    public function post(){
       return $this->belongsTo('Plac\Post', 'post_id', 'post_id');
    }
    
    
}

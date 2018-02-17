<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class PostLikes extends Model {

    public $table = "post_likes";
    protected $primaryKey = 'post_like_id';
    public $incrementing = false;
    public $timestamps = true;

    public function profile() {
        return $this->belongsTo('Plac\Profile', "profile_id", 'profile_id');
    }

    public function post() {
        
    }

}

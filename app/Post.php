<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{

    public $table = "posts";
    protected $primaryKey = 'post_id';
    public $incrementing = false;
    public $timestamps = true;



    public function profile()
    {
        return $this->belongsTo('Plac\Profile', 'profile_id', 'profile_id');
    }
  


    public function comments()
    {
        return $this->hasMany('Plac\PostComments', 'post_id', 'post_id');

    }

    public function commentsCount()
    {
        return $this->comments()
            ->selectRaw('post_id,count(*) as count')
            ->groupBy('post_id');
    }

    public function likes()
    {
        return $this->hasMany('Plac\PostLikes', 'post_id', 'post_id');
    }


    public function likesCount()
    {
        return $this->likes()
            ->selectRaw('post_id,count(*) as count')
            ->groupBy('post_id');
    }
    
    
}

<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class PostComments extends Model
{

    public $table = "post_comments";
    protected $primaryKey = 'post_comment_id';
    public $incrementing = false;
    public $timestamps = true;


    public function profile()
    {
        return $this->belongsTo('Plac\Profile', "profile_id", 'profile_id');
    }

}

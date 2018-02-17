<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class AdvertisementsComments extends Model
{
    protected $table = 'advertisements_comments';
    protected $primaryKey = 'advertisement_comment_id';
    public $incrementing = false;
   
    public function profile()
    {
        return $this->belongsTo('Plac\Profile', 'profile_id', 'profile_id');
    }
}
